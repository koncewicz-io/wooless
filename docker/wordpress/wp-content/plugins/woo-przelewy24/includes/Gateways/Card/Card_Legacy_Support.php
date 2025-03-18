<?php

namespace WC_P24\Gateways\Card;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Helper;
use WC_P24\Render;

trait Card_Legacy_Support
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

        if ($order instanceof \WC_Order) {
            $result['recurring'] = Helper::order_has_subscription_product($order);
        }

        $result['url'] = Card_Webhooks::get_process_card_url();
        $result['orderId'] = $order_id;
        $result['orderKey'] = $order->get_order_key();
        $result['clickToPay']['email'] = $order->get_billing_email();

        Render::template('receipt_page_card', ['config' => $result]);
    }
}
