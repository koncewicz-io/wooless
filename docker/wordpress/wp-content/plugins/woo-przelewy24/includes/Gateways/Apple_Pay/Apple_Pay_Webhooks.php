<?php

namespace WC_P24\Gateways\Apple_Pay;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;
use WC_P24\API\Apple_Client;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Webhook;
use WC_Payment_Gateway;

class Apple_Pay_Webhooks extends Webhook
{
    const PROCESS_GOOGLE_PAY = 'process-apple-pay';
    const VALIDATE_MERCHANT = 'validate-merchant';
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

            case self::VALIDATE_MERCHANT:
                $this->validate();
                break;
        }
    }

    private function process(): void
    {
        try {
            $input = $this->get_input();

            switch ($input['type']) {
                case self::ACTION_REGISTER_TRANSACTION_LEGACY:
                    $result = $this->register_apple_pay_transaction();
                    break;
            }

            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['error' => true, 'message' => $e->getMessage()], 422);
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        exit;
    }

    private function validate(): void
    {
        $client = new Apple_Client();
        $gateway = $this->gateway;

        $response = $client->validate([
            'merchantIdentifier' => $gateway->get_option('merchant_id'),
            'displayName' => $gateway->get_option('merchant_name'),
            'initiative' => 'web',
            'initiativeContext' => $gateway->get_option('merchant_domain')
        ], $gateway->get_option('cert_key'), $gateway->get_option('cert_pem'));

        wp_send_json_success($response);
        exit;
    }

    private function register_apple_pay_transaction(): array
    {
        $payment_details = $this->get_payment_details();
        $order = $this->get_order();

        return $this->gateway->payment($order, $payment_details);
    }

    public static function get_process_apple_pay_url(): string
    {
        return self::setup_url(self::PROCESS_GOOGLE_PAY);
    }

    public static function get_process_validate_url(): string
    {
        return self::setup_url(self::VALIDATE_MERCHANT);
    }
}
