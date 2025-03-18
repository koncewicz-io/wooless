<?php

namespace WC_P24\Subscriptions;

use WC_P24\Subscriptions\Product\Product;

if (!defined('ABSPATH')) {
    exit;
}

class Filter_Gateways
{
    public function __construct()
    {
        add_filter('woocommerce_available_payment_gateways', [$this, 'filter_gateways'], 257, 1);
    }

    public function filter_gateways(array $gateways): array
    {
        $order_id = get_query_var('order-pay');
        $is_order_pay = !!$order_id;
        $order = wc_get_order( (int) $order_id );

        $line_items = isset(WC()->cart) ? WC()->cart->get_cart() : [];

        if ($is_order_pay){
            $line_items = $order->get_items();
        }

        $has_subscription_product = false;

        foreach ($line_items as $item) {
            $product = wc_get_product($item['product_id']);

            if ($product && in_array($product->get_type(), [Product::TYPE])) {
                $has_subscription_product = true;
                break;
            }
        }

        if ($has_subscription_product) {
            foreach ($gateways as $key => $gateway) {
                if (!in_array('p24-subscription', $gateway->supports)) {
                    unset($gateways[$key]);
                }
            }
        }

        return $gateways;
    }

}
