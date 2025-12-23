<?php

namespace WC_P24\Gateways\Apple_Pay;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Render;

trait Apple_Pay_Legacy_Support
{
    private function init_legacy()
    {
        add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
    }

    public function process_payment($order_id): array
    {
        return $this->process_on_payment_url($order_id);
    }

    public function receipt_page($order_id)
    {
        $order = wc_get_order($order_id);

        $result = $this->get_receipt_config();
        $result['url'] = Apple_Pay_Webhooks::get_process_apple_pay_url();
        $result['validateUrl'] = Apple_Pay_Webhooks::get_process_validate_url();
        $result['orderId'] = $order_id;
        $result['orderKey'] = $order->get_order_key();
        $result['paymentDetails'] = [
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
        ];

        Render::template('receipt_page_apple_pay', ['config' => $result]);
    }
}
