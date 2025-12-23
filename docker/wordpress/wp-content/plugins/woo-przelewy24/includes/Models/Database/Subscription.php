<?php

namespace WC_P24\Models\Database;

use DateTime;
use WC_Customer;
use WC_Order;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Models\Transaction;
use WC_P24\Multicurrency\Multicurrency;
use WC_P24\Subscriptions\Product\Product;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Payment_Methods;
use WC_P24\Utilities\Record;

if (!defined('ABSPATH')) {
    exit;
}

class Subscription extends Record
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_SUSPENDED = 3;
    const STATUS_CANCELLED = 4;

    private int $id = 0;
    private int $status = 0;
    private int $customer_id = 0;
    private int $order_id = 0;
    private int $product_id = 0;
    private ?int $card_id = null;
    private ?DateTime $valid_to;
    private ?DateTime $checked_at;
    private ?Reference $card;
    private ?WC_Order $order;
    private ?WC_Customer $customer;
    private ?Product $product;

    public function __construct(int $subscription_id = 0)
    {
        if ($subscription_id > 0) {
            $this->id = $subscription_id;
        }

        if ($this->get_id()) {
            $this->find();
        }
    }

    public static function get(int $subscription_id): ?Subscription
    {
        $subscription = new self($subscription_id);

        return $subscription->get_id() ? $subscription : null;
    }

    public static function table_name(): string
    {
        return self::db()->prefix . 'wc_p24_subscriptions';
    }

    protected static function primary_key(): string
    {
        return 'id';
    }

    protected static function get_fields(): array
    {
        return [
            'id' => ['format' => '%d'],
            'user_id' => ['format' => '%d'],
            'product_id' => ['format' => '%d'],
            'card_id' => ['format' => '%d'],
            'order_id' => ['format' => '%d'],
            'valid_to' => ['format' => '%s', 'parse' => function (DateTime $value): string {
                return $value->format('Y-m-d H:i:s');
            }],
            'status' => ['format' => '%d'],
            'checked_at' => ['format' => '%s', 'parse' => function (DateTime $value): string {
                return $value->format('Y-m-d H:i:s');
            }]
        ];
    }

    protected function set_primary_key(int $value): void
    {
        $this->new_record = false;
        $this->id = $value;
    }

    public function set_status(int $status)
    {
        $this->attributes['status'] = $status;
        $this->status = $status;
    }

    public function set_card_id(?int $card_id = null): void
    {
        $this->attributes['card_id'] = $card_id;
        $this->card_id = $card_id;
    }

    public function set_order_id(int $order_id): void
    {
        $this->attributes['order_id'] = $order_id;
        $this->order_id = $order_id;
        $this->order = null;
    }

    public function set_customer_id(?int $customer_id = null): void
    {
        if (!$customer_id) {
            $customer_id = get_current_user_id();
        }

        $this->customer_id = $customer_id;
        $this->attributes['user_id'] = $customer_id;
        $this->customer = null;
    }

    public function set_product_id(int $product_id): void
    {
        $this->attributes['product_id'] = $product_id;
        $this->product_id = $product_id;
        $this->product = null;
    }

    public function set_checked_at(DateTime $last_checked): void
    {
        $this->attributes['checked_at'] = $last_checked;
        $this->checked_at = $last_checked;
    }

    public function set_valid_to(?DateTime $valid_to = null): void
    {
        $this->attributes['valid_to'] = $valid_to;
        $this->valid_to = $valid_to;
    }

    protected function get_primary_key(): int
    {
        return $this->get_id();
    }

    public function get_id(): ?int
    {
        return $this->id;
    }

    public function get_checked_at(): ?DateTime
    {
        return $this->checked_at;
    }

    public function get_order_id(): int
    {
        return $this->order_id;
    }

    public function get_card(): ?Reference
    {
        if (!isset($this->card) && $this->get_card_id()) {
            $this->card = Reference::get($this->get_card_id());
        }

        return $this->card;
    }

    public function get_card_id(): ?int
    {
        return $this->card_id;
    }

    public function get_customer_id(): int
    {
        return $this->customer_id;
    }

    public function get_valid_to(): ?DateTime
    {
        return $this->valid_to;
    }

    public function get_product_id(): int
    {
        return $this->product_id;
    }

    public function get_customer(): ?WC_Customer
    {
        if (isset($this->customer)) {
            $this->customer = new WC_Customer($this->get_customer_id());
        }

        return $this->customer;
    }

    public function get_status(): int
    {
        return $this->status;
    }

    public function get_status_label(): string
    {
        $status = $this->get_status();

        if ($this->get_valid_to() < new DateTime() && !in_array($status, [Subscription::STATUS_PENDING, Subscription::STATUS_CANCELLED, Subscription::STATUS_PROCESSING])) {
            $status = Subscription::STATUS_SUSPENDED;
        }

        switch ($status) {
            case Subscription::STATUS_ACTIVE:
                return __('Active', 'woocommerce-p24');
            case Subscription::STATUS_PROCESSING:
                return __('Pending payment (renewal)', 'woocommerce-p24');
            case Subscription::STATUS_PENDING:
                return __('Pending payment', 'woocommerce-p24');
            case Subscription::STATUS_SUSPENDED:
                return __('Suspended', 'woocommerce-p24');
            case Subscription::STATUS_CANCELLED:
                return __('Cancelled', 'woocommerce-p24');
            default:
                return __('Inactive', 'woocommerce-p24');
        }
    }

    public function get_order(): ?WC_Order
    {
        $this->order = null;

        if (isset($this->order_id)) {
            $order = wc_get_order($this->get_order_id());

            if ($order instanceof WC_Order) {
                $this->order = $order;
            }
        }

        return $this->order;
    }

    public function get_product(): ?Product
    {
        if (isset($this->product_id)) {
            $this->product = wc_get_product($this->get_product_id());
        }

        return $this->product;
    }

    public function get_downloads(): array
    {
        $order = $this->get_order();

        if (!$order) return [];

        $items = $order->get_downloadable_items();

        $items = array_filter($items, function ($item) {
            return $item['product_id'] == $this->product_id;
        });

        return $items;
    }

    public function is_available(): bool
    {
        $is_active_status = in_array($this->get_status(), [Subscription::STATUS_ACTIVE, Subscription::STATUS_PROCESSING, Subscription::STATUS_CANCELLED]);
        $now = new DateTime();

        return $is_active_status && $this->get_valid_to() >= $now;
    }

    public function is_pending(): bool
    {
        return $this->get_status() == Subscription::STATUS_PENDING;
    }

    public function is_cancelable(): bool
    {
        return in_array($this->get_status(), [Subscription::STATUS_ACTIVE, Subscription::STATUS_PROCESSING]);
    }

    public function activate(int $days, int $card_id): bool
    {
        if (!in_array($this->get_status(), [Subscription::STATUS_PENDING])) {
            return false;
        }

        $valid_to = new DateTime();
        $valid_to->modify('+' . $days . 'days');

        $this->set_card_id($card_id);
        $this->set_checked_at(new DateTime());
        $this->set_valid_to($valid_to);
        $this->set_status(Subscription::STATUS_ACTIVE);

        $order = $this->get_order();
        $order && $this->update_files_expires($order, $this->valid_to);

        return $this->update();
    }

    public function renew(): void
    {
        $last_order = $this->get_order();

        if (!in_array($this->get_status(), [Subscription::STATUS_ACTIVE, Subscription::STATUS_CANCELLED])) {
            return;
        }

        if (!$last_order || !$last_order->get_status('edit') === 'completed') {
            throw new \Exception('Order is not fulfilled');
        }

        if (!$this->card_id) {
            $this->status = Subscription::STATUS_CANCELLED;
            $this->save();
            return;
        }

        Multicurrency::setup($last_order->get_currency());

        $order = wc_create_order();
        $parent_id = $last_order->get_parent_id() ?: $last_order->get_id();
        $order->set_parent_id($parent_id);
        $order->set_address($last_order->get_address('billing'), 'billing');
        $order->set_currency($last_order->get_currency());
        $order->set_customer_id($this->get_customer_id());
        $order->add_product($this->get_product(), 1);
        $order->update_meta_data('_wc_order_attribution_source_type', 'utm');
        $order->update_meta_data('_wc_order_attribution_utm_source', __('P24 recurring subscription', 'woocommerce-p24'));

        [$item] = array_values($order->get_items());
        $item->update_meta_data('_p24_subscription_id', $this->get_id());

        $order->calculate_totals();
        $order->set_status('pending');
        $order->add_order_note(__('Subscription renew order', 'woocommerce-p24'));
        $order->update_meta_data('_p24_has_subscription', true);
        $order->update_meta_data('_p24_subscription_renew', $this->get_id());
        $order->update_meta_data(Transaction::TRACE_ID_KEY, $last_order->get_meta(Transaction::TRACE_ID_KEY));
        $order->set_payment_method($last_order->get_payment_method());
        $order->set_payment_method_title(__('Credit card - renew subscription', 'woocommerce-p24'));

        $this->set_status(Subscription::STATUS_PROCESSING);
        $this->set_checked_at(new DateTime());

        $card = $this->get_card();

        if (!$card) {
            return;
        }

        $order->save();
        $this->set_order_id($order->get_id());
        $this->save();

        $method_id = Gateways_Manager::get_method_id_matching_group(Payment_Methods::CARD_PAYMENT_ALT, $order->get_total());

        $transaction = new Transaction($order->get_id(), $method_id, true);
        $transaction->set_card($card->to_card_simple(), Transaction::CARD_RECURRING);
        $transaction->register();

        try {
            $transaction->do_payment();
        } catch (\Exception $e) {
            $order->add_order_note(__('Attempted card charge failed', 'woocommerce-p24'));
            $this->suspense();
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }
    }

    public function extend(): bool
    {
        if (!in_array($this->get_status(), [Subscription::STATUS_PROCESSING])) {
            return false;
        }

        $order = $this->get_order();

        if (!$order) return false;

        $product = $this->get_product();
        $days = $product->get_days();

        $valid_to = new DateTime();
        $valid_to->modify('+' . $days . ' days');

        $this->set_valid_to($valid_to);
        $this->set_checked_at(new DateTime());
        $this->set_status(Subscription::STATUS_ACTIVE);

        $this->update_files_expires($order, $valid_to);

        return $this->update();
    }

    public function update_files_expires(WC_Order $order, DateTime $valid_to): void
    {
        $valid_to = clone $valid_to;
        $valid_to->setTime(0, 0, 0);

        $items = $order->get_downloadable_items();
        $permissions = wc_get_customer_download_permissions($order->get_customer_id());
        $permissions_by_did = [];

        foreach ($permissions as $permission) {
            $permissions_by_did[$permission->download_id] = $permission->permission_id;
        }

        foreach ($items as $download_item) {
            $permission_id = $_permissions[$download_item['download_id']] ?? null;
            if (!$permission_id) continue;
            $download = new \WC_Customer_Download($permission_id);
            if (!$download->get_id()) continue;
            $download->set_access_expires($valid_to->getTimestamp());
            $download->save();
        }
    }

    public function cancel(): bool
    {
        if (!$this->is_cancelable()) return false;

        $this->set_checked_at(new DateTime());
        $this->set_card_id(null);
        $this->set_status(Subscription::STATUS_CANCELLED);

        return $this->update();
    }

    public function suspense(): bool
    {
        if (!in_array($this->get_status(), [Subscription::STATUS_ACTIVE, Subscription::STATUS_PROCESSING])) {
            return false;
        }

        $this->set_checked_at(new DateTime());
        $this->set_status(Subscription::STATUS_SUSPENDED);

        return $this->update();
    }

    protected static function joins(): string
    {
        return 'INNER JOIN ' . self::db()->prefix . 'posts as product ON product.ID = t.product_id
                INNER JOIN ' . self::db()->prefix . 'users as user ON user.ID = t.user_id
                LEFT JOIN ' . Reference::table_name() . ' as card ON card.id = t.card_id';
    }

    public function parse(array $data): void
    {
        foreach ($data as $key => $value) {
            if (isset(self::get_fields()[$key])) {
                switch ($key) {
                    case 'id':
                        $this->set_primary_key((int)$value);
                        break;
                    case 'user_id':
                        $this->set_customer_id((int)$value);
                        break;
                    case 'product_id':
                        $this->set_product_id((int)$value);
                        break;
                    case 'order_id':
                        $this->set_order_id((int)$value);
                        break;
                    case 'card_id':
                        $this->set_card_id($value);
                        break;
                    case 'valid_to':
                        $this->set_valid_to(new DateTime($value));
                        break;
                    case 'status':
                        $this->set_status((int)$value);
                        break;
                    case 'checked_at':
                        $this->set_checked_at(new DateTime($value ?: ''));
                        break;
                }
            } else {
                $this->additionals[$key] = $value;
            }
        }
    }
}
