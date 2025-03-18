<?php

namespace WC_P24\Subscriptions;

use WC_Order;
use WC_P24\API\Resources\Card_Resource;
use WC_P24\Core;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Models\Database\Reference;
use WC_P24\Models\Database\Subscription;
use WC_P24\Models\Simple\Card_Simple;
use WC_P24\Models\Transaction;
use WC_P24\Subscriptions\Product\Product;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Notice;

class Manager
{
    public string $cron_time = '';

    public function __construct()
    {
        $this->cron_time = 'daily';

        add_filter('cron_schedules', [$this, 'cron_schedules']);

        if (!wp_next_scheduled('przelewy24_cron_event_renew')) {
            wp_schedule_event(time(), $this->cron_time, 'przelewy24_cron_event_renew');
        }

        add_action('przelewy24_cron_event_renew', [$this, 'find_subscription_to_extend']);

        add_action('woocommerce_order_status_pending', [$this, 'create_pending_subscriptions_on_pending_order'], 10, 2);
        add_action('woocommerce_before_delete_order_item', [$this, 'on_remove_order_item']);
        add_action('woocommerce_before_delete_order', [$this, 'on_remove_order']);

        add_action('przelewy24_after_verify_transaction', [$this, 'handle_subscriptions'], 20);

        $this->notice();
    }

    public function cron_schedules($schedules): array
    {
        $schedules['every5mins'] = [
            'interval' => 60 * 5,
            'display' => 'Every 5 minutes'
        ];

        $schedules['everymin'] = [
            'interval' => 60,
            'display' => 'Every minute'
        ];

        return $schedules;
    }

    public function notice(): void
    {
        $card_gateway_is_enabled = Gateways_Manager::$gateways[Core::CARD_IN_SHOP_METHOD]->is_enabled();

        if (Subscriptions::is_enabled() && !$card_gateway_is_enabled) {
            new Notice(__('Subscription Przelewy24 requires <strong>Przelewy24 - Card payment</strong> to be enabled', 'woocommerce-p24'), Notice::WARNING);
        }
    }

    public function handle_subscriptions(Transaction $transaction): void
    {
        $order = $transaction->order;
        $has_subscription = $order->get_meta('_p24_has_subscription');

        if (!$has_subscription) return;

        $resource = new Card_Resource();
        $response = $resource->get_info($transaction->get_order_id());

        if (!empty($response['data'])) {
            $card = new Card_Simple($response['data']);
            $card_id = Reference::save_reference($card, $order);

            if ($card_id) {
                Helper::activate_subscription($order, $card_id);
            }
        }
    }

    public function on_remove_order_item($item_id): void
    {
        $order_id = wc_get_order_id_by_order_item_id($item_id);
        $order = wc_get_order($order_id);

        if (!$order instanceof WC_Order) return;

        $item = $order->get_item($item_id);
        $this->cancel_subscription($item);
    }

    public function on_remove_order($order_id): void
    {
        $order = wc_get_order($order_id);

        if (!$order instanceof WC_Order) return;

        foreach ($order->get_items() as $item) {
            $this->cancel_subscription($item);
        }
    }

    public function cancel_subscription($item): void
    {
        $subscription_id = $item->get_meta('_p24_subscription_id');

        if (!$subscription_id) return;

        $subscription = Subscription::get($subscription_id);
        $subscription && $subscription->cancel();
    }

    public function create_pending_subscriptions_on_pending_order($order_id, $order): void
    {
        $items = $order->get_items();
        $has_subscription = false;

        try {
            foreach ($items as $item) {
                $product = $item->get_product();

                if ($product instanceof Product) {
                    if ($item->get_meta('_p24_subscription_id')) continue;

                    $has_subscription = true;
                    $subscription = new Subscription();
                    $subscription->set_customer_id((int)$order->get_customer_id());
                    $subscription->set_product_id($product->get_id());
                    $subscription->set_valid_to(new \DateTime());
                    $subscription->set_status(Subscription::STATUS_PENDING);
                    $subscription->set_order_id((int)$order_id);

                    if ($subscription->save()) {
                        $item->update_meta_data('_p24_subscription_id', $subscription->get_id());
                    }
                }
            }

            if ($has_subscription) {
                $order->update_meta_data('_p24_has_subscription', true);
                $order->save();
            }
        } catch (\Exception $e) {
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }
    }

    public function find_subscription_to_extend(): void
    {
        $now = new \DateTime();
        $now->modify('+' . Subscriptions::days_to_renew() . ' days');
        $now = $now->format('Y-m-d H:i:s');

        $subscriptions = Subscription::findAll([
            'where' => ['t.valid_to < %s AND t.status IN (%d) AND card_id IS NOT NULL', $now, Subscription::STATUS_ACTIVE]
        ]);

        $first = array_shift($subscriptions);

        if ($first && $first->get_order()) {
            $first->renew();
            $first->save();

            if (!empty($subscriptions)) {
                wp_schedule_single_event(time() + 60, 'p24_renew_subscription');
            }
        }
    }
}
