<?php

namespace WC_P24\Multicurrency;

if (!defined('ABSPATH')) {
    exit;
}

use WC_Order;
use WC_Product;
use WP_Error;

class Admin
{
    public function __construct()
    {
        add_filter('woocommerce_new_order_item', [$this, 'change_price_depends_on_currency'], 10, 3);
    }

    public function change_price_depends_on_currency($item_id, $item, $order_id)
    {
        $order = wc_get_order($order_id);

        if (!Multicurrency::compare_currency($order->get_currency())) {
            $config = Multicurrency::get_config($order->get_currency());
            $multiplier = $config->get_multiplier();

            if ($item instanceof \WC_Order_Item_Product) {
                $subtotal = $item->get_subtotal();
                $total = $item->get_total();

                $item->set_subtotal($subtotal * $multiplier);
                $item->set_total($total * $multiplier);
                $item->save();
            }
        }

        return $item_id;
    }
}
