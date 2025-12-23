<?php

namespace WC_P24\Gateways\Blik;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Webhook;
use WC_Payment_Gateway;


class Blik_Webhooks extends Webhook
{
    const PROCESS_BLIK = 'process-blik';
    const ACTION_REGISTER_TRANSACTION_LEGACY = 'register-transaction';
    const ACTION_GET_ORDER_STATUS = 'get-order-status';
    private WC_Payment_Gateway $gateway;

    public function __construct($gateway)
    {
        parent::__construct();
        $this->gateway = $gateway;
    }

    public function callback(): void
    {
        switch ($this->get_action()) {
            case self::PROCESS_BLIK;
                $this->process();
                break;
        }
    }

    private function process(): void
    {
        try {
            $input = $this->get_input();

            switch ($input['type']) {
                case self::ACTION_REGISTER_TRANSACTION_LEGACY:
                    $result = $this->register_blik_transaction();
                    break;
                case self::ACTION_GET_ORDER_STATUS:
                    $result = $this->get_order_status();
                    break;
            }

            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(['error' => true, 'message' => $e->getMessage()], 422);
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        exit;
    }

    private function register_blik_transaction(): array
    {
        $payment_details = $this->get_payment_details();
        $order = $this->get_order();

        return $this->gateway->payment($order, $payment_details);
    }

    private function get_order_status(): array
    {
        $result = [];
        $order = $this->get_order();

        if (in_array($order->get_status(), ['processing', 'completed'])) {
            $result['completed'] = true;
            $result['success'] = true;
            $result['redirect'] = $order->get_checkout_order_received_url();
        } else {
            $result['pending'] = true;
        }

        return $result;
    }

    public static function get_process_blik_url(): string
    {
        return self::setup_url(self::PROCESS_BLIK);
    }
}
