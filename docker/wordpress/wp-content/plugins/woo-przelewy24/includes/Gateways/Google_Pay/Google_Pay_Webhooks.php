<?php

namespace WC_P24\Gateways\Google_Pay;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Webhook;
use WC_Payment_Gateway;

class Google_Pay_Webhooks extends Webhook
{
    const PROCESS_GOOGLE_PAY = 'process-google-pay';
    const ACTION_REGISTER_TRANSACTION_LEGACY = 'register-transaction';
    private WC_Payment_Gateway $gateway;

    public function __construct($gateway)
    {
        parent::__construct();
        $this->gateway = $gateway;
    }

    public function callback(): void
    {
        switch ($this->get_action()) {
            case self::PROCESS_GOOGLE_PAY;
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
                    $result = $this->register_google_pay_transaction();
                    break;
            }

            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(['error' => true, 'message' => $e->getMessage()], 422);
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        exit;
    }

    private function register_google_pay_transaction(): array
    {
        $payment_details = $this->get_payment_details();
        $order = $this->get_order();

        return $this->gateway->payment($order, $payment_details);
    }


    public static function get_process_google_pay_url(): string
    {
        return self::setup_url(self::PROCESS_GOOGLE_PAY);
    }
}
