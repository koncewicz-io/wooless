<?php

namespace WC_P24\Models;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\API\Resources\Refund_Resource;
use WC_P24\API\Resources\Resource;
use WC_P24\API\Resources\Transaction_Resource;
use WC_P24\Gateways\General_Webhooks;
use WC_P24\Helper;
use WC_P24\Models\Simple\Refund_Notification;
use WC_P24\Render;
use WC_P24\Utilities\Encryption;

class Refund
{
    const REFUND_UUID_PREFIX = '_p24_refund_uuid_';
    const REFUND_REASON_PREFIX = '_p24_refund_reason_';
    const REFUND_AMOUNT_PREFIX = '_p24_refund_amount_';
    const REFUND_PENDING = '_p24_refund_pending';
    const OLD_SESSION_ID_KEY = '_p24_order_session_id';
    const STATUS_COMPLETED = 1;
    const STATUS_PENDING = 2;
    const STATUS_TBC = 3;
    const STATUS_REJECTED = 4;

    public $order;
    private int $amount = 0;
    private ?string $session_id = null;
    private ?int $order_id = null;
    private ?string $request_id = null;
    private ?array $pending_refunds = [];
    private ?string $reason = null;
    public ?int $status = null;

    protected Resource $client;

    public function __construct(int $order_id, float $amount = 0, string $reason = '')
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            throw new \Exception('Order does exist');
        }

        $this->order = $order;
        $this->amount = Helper::to_lowest_unit($amount);
        $this->client = new Refund_Resource();
        $this->session_id = $this->order->get_meta(Transaction::SESSION_ID_KEY);

        // For back compability
        $this->session_id = !$this->session_id ? $this->order->get_meta(self::OLD_SESSION_ID_KEY): $this->session_id;

        $this->order_id = (int)$this->order->get_meta(Transaction::ORDER_ID_KEY);
        $this->pending_refunds = (array)$this->order->get_meta(self::REFUND_PENDING);

        $this->request_id = wp_generate_uuid4() . '_' . time();
        $this->reason = $reason;
    }

    public static function display_pending_refunds($order)
    {
        $order = wc_get_order($order);
        $order->get_meta();
        $pending_refunds_request_ids = (array)$order->get_meta(Refund::REFUND_PENDING);
        $pending_refunds_request_ids = array_filter($pending_refunds_request_ids);
        $pending_refunds = [];

        if (empty($pending_refunds_request_ids)) return;

        foreach ($pending_refunds_request_ids as $request_id) {
            $uuid = $order->get_meta(self::REFUND_UUID_PREFIX . $request_id);
            $reason = $order->get_meta(self::REFUND_REASON_PREFIX . $request_id);
            $amount = (float)$order->get_meta(self::REFUND_AMOUNT_PREFIX . $request_id);

            if ($amount && $uuid) {
                $pending_refunds[] = [
                    'amount' => wc_price(Helper::to_higher_unit($amount), ['currency' => $order->get_currency()]),
                    'reason' => $reason,
                    'uuid' => $uuid
                ];
            }
        }

        if (empty($pending_refunds)) return;

        Render::template('admin/order-pending-refunds', ['pending_refunds' => $pending_refunds]);
    }

    private function get_refund_data(): array
    {
        $parts = [Helper::get_transaction_prefix(), Encryption::generate_session_id($this->order_id)];
        $refundsUuid = substr(implode('_', $parts), 0, 35);

        return [
            'requestId' => $this->request_id,
            'refundsUuid' => $refundsUuid,
            'refunds' => [
                [
                    'orderId' => $this->order_id,
                    'sessionId' => $this->session_id,
                    'amount' => $this->amount,
                    /* translators: %s: Order number */
                    'description' => sprintf(__('Return to order no. %s', 'woocommerce-p24'), $this->order->get_order_number()),
                ]
            ],
            'urlStatus' => General_Webhooks::get_refund_url($this->order->get_id())
        ];
    }

    public function refund_notes(): array
    {
        return [
            self::STATUS_COMPLETED => __('The order was successfully refunded', 'woocommerce-p24'),
            self::STATUS_PENDING => __('The refund is being processed', 'woocommerce-p24'),
            self::STATUS_TBC => __('Refund must be accepted by payment provider', 'woocommerce-p24'),
            self::STATUS_REJECTED => __('Refund was rejected', 'woocommerce-p24')
        ];
    }

    public function register(): void
    {
        $client = new Transaction_Resource();
        $response = $client->get_transaction_by_session_id($this->session_id);

        if (empty($response['data']['sessionId'])) {
            throw new \Exception(__('Order does not exist at przelewy24 system', 'woocommerce-p24'));
        }

        $payload = $this->get_refund_data();
        $response = $this->client->refund($payload);

        if (!empty($response['data'][0]['sessionId'])) {
            $this->add_pending_refund($this->request_id, $payload['refundsUuid']);

            $this->order->save();
        } else if (!empty($response['error'][0]['message'])) {
            $this->order->add_order_note($response['error'][0]['message']);
            $this->order->save();

            throw new \Exception($response['error'][0]['message']);
        }
    }

    public function get_status(): bool
    {
        $response = $this->client->get_details($this->order_id);

        if (!empty($response['code']) && $response['code'] == 401) {
            return true;
        }

        if (empty($response['data']['refunds'])) {
            throw new \Exception('Cannot get refund details');
        }

        $refunds = $response['data']['refunds'];
        $refund = null;

        foreach ($refunds as $_refund) {
            if ($_refund['requestId'] == $this->request_id) {
                $refund = $_refund;
                break;
            }
        }

        if (!$refund) {
            throw new \Exception('Cannot get refund details');
        }

        $status = (int)$refund['status'];
        $this->status = $status;

        if ($this->reason) {
            $this->order->update_meta_data(self::REFUND_REASON_PREFIX . $this->request_id, $this->reason);
        }

        $this->order->add_order_note($this->refund_notes()[$status]);
        $this->order->save();

        return $status === self::STATUS_COMPLETED;
    }

    private function add_pending_refund($request_id, $refund_id): void
    {
        $this->pending_refunds[] = $request_id;
        $this->pending_refunds = array_unique($this->pending_refunds);

        $this->order->update_meta_data(self::REFUND_AMOUNT_PREFIX . $request_id, $this->amount);
        $this->order->update_meta_data(self::REFUND_UUID_PREFIX . $request_id, $refund_id);
        $this->order->update_meta_data(self::REFUND_PENDING, $this->pending_refunds);
    }

    private function remove_pending_refund($request_id): void
    {
        $key = array_search($request_id, $this->pending_refunds);

        if ($key !== false) {
            unset($this->pending_refunds[$key]);
        }

        $this->order->delete_meta_data(self::REFUND_REASON_PREFIX . $request_id);
        $this->order->delete_meta_data(self::REFUND_UUID_PREFIX . $request_id);
        $this->order->delete_meta_data(self::REFUND_AMOUNT_PREFIX . $request_id);
        $this->order->update_meta_data(self::REFUND_PENDING, $this->pending_refunds);
    }

    public function verify(Refund_Notification $notification): void
    {
        $status = false;

        $refund_uuid = $this->order->get_meta(self::REFUND_UUID_PREFIX . $notification->request_id);
        $amount = (int)$this->order->get_meta(self::REFUND_AMOUNT_PREFIX . $notification->request_id);

        if ($notification->session_id !== $this->session_id) {
            throw new \Exception('Session ID mismatch.');
        }

        if (!$notification->order_id) {
            throw new \Exception('Order ID not provided.');
        }

        if ($notification->refunds_uuid !== $refund_uuid) {
            throw new \Exception('Refund UUID mismatch.');
        }

        if ($notification->amount !== $amount) {
            throw new \Exception('Refund amount mismatch.');
        }

        // When success
        if ($notification->status === 0) {
            $this->order->add_order_note(__('The order was successfully refunded', 'woocommerce-p24'));
            $reason = $this->order->get_meta(self::REFUND_REASON_PREFIX . $notification->request_id, true);

            $attributes = [
                'amount' => Helper::to_higher_unit($notification->amount),
                'order_id' => $this->order->get_id(),
                'refund_id' => $notification->refunds_uuid,
                'refund_payment' => false
            ];

            $line_items = [];
            foreach ($this->order->get_items() as $item_id => $item) {
                if ((float) $item->get_total() === Helper::to_higher_unit($notification->amount)) {
                    $line_items[$item_id] = [
                        'qty'          => $item->get_quantity(),
                        'refund_total' => $item->get_total(),
                        'refund_tax'   => [], // add tax refunds if necessary
                    ];
                    break;
                }
            }

            if (!empty($line_items)) {
                $attributes['line_items'] = $line_items;
            }

            if ($reason) {
                $attributes['reason'] = $reason;
            }

            $status = wc_create_refund($attributes);
        }
        // When not success
        if ($notification->status > 0 || $status instanceof \WP_Error) {
            $message = $this->refund_notes()[$notification->status] ?: __('Refund was rejected', 'woocommerce-p24');
            $this->order->add_order_note($message);
        }
        // Always remove temporary metadata
        $this->remove_pending_refund($notification->request_id);
        $this->order->save();
    }
}
