<?php

namespace WC_P24\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Models\Refund;
use WC_P24\Models\Transaction;
use WC_P24\Render;

class Order_Utilities
{
    public function __construct()
    {
        add_filter('woocommerce_admin_order_preview_get_order_details', [$this, 'set_order_preview_additional_info'], 10, 2);
        add_action('woocommerce_admin_order_preview_end', [$this, 'display_order_preview_additional_info'], 10, 2);
        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'display_order_additional_info'], 10, 2);
        add_action('woocommerce_admin_order_items_after_shipping', [Refund::class, 'display_pending_refunds']);
    }

    public function set_order_preview_additional_info($data, $order)
    {
        $payment_method = $order->get_payment_method();
        $payment_method_title = $order->get_payment_method_title();

        if ($session_id = $order->get_meta(Transaction::SESSION_ID_KEY)) {
            $data['p24_session_id'] = $session_id;
        }

        if ($session_id = $order->get_meta(Transaction::ORDER_ID_KEY)) {
            $data['p24_order_id'] = $session_id;
        }

        if (in_array($payment_method, Gateways_Manager::get_ids())) {
            $data['p24_payment'] = $payment_method;
            $data['p24_payment_title'] = $payment_method_title;
        }

        return $data;
    }

    public function display_order_preview_additional_info()
    {
        Render::template('admin/order-preview-details');
    }

    public function display_order_additional_info($order)
    {
        $payment_method = $order->get_payment_method();

        if (in_array($payment_method, Gateways_Manager::get_ids())) {
            $data = [
                'session_id' => $order->get_meta(Transaction::SESSION_ID_KEY),
                'order_id' => $order->get_meta(Transaction::ORDER_ID_KEY),
            ];

            Render::template('admin/order-details', $data);
        }
    }
}
