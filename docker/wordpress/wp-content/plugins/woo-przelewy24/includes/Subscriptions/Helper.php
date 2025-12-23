<?php

namespace WC_P24\Subscriptions;

use WC_P24\Models\Database\Subscription;
use WC_P24\Subscriptions\Product\Product;
use WC_P24\Utilities\Logger;

class Helper
{
    static function get_order_subscription_items($order): array
    {
        $result = [];

        if ($order) {
            $has_subscription = $order->get_meta('_p24_has_subscription');

            if ($has_subscription) {
                $items = $order->get_items();

                foreach ($items as $item) {
                    $product = $item->get_product();

                    if ($product instanceof Product) {
                        $subscription_id = (int)$item->get_meta('_p24_subscription_id');

                        if (!$subscription_id) continue;

                        $subscription = Subscription::get($subscription_id);

                        if ($subscription->get_id()) {
                            $quantity = (int)$item->get_quantity();
                            $days = (int)($quantity * $product->get_days());
                            $result[] = [$days, $subscription];
                        }
                    }
                }
            }
        }

        return $result;
    }

    static function activate_subscription($order, $card_id)
    {
        $items = Helper::get_order_subscription_items($order);

        foreach ($items as $item) {
            [$days, $subscription] = $item;
            $status = $subscription->get_status();

            switch ($status) {
                case Subscription::STATUS_PENDING:
                    $subscription->activate($days, $card_id);
                    break;
                case Subscription::STATUS_PROCESSING:
                    Logger::log('here', Logger::EXCEPTION);
                    $subscription->extend();
                    break;
            }
        }
    }
}
