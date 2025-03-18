<?php

namespace WC_P24\Gateways\Virtual_Gateway;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Core;
use WC_P24\Render;

trait Virtual_Gateway_Legacy_Support
{
    public function payment_fields(): void
    {
        parent::payment_fields();

        $settings = $this->get_receipt_config();

        Render::template('gateway/virtual-gateway', $settings);
    }

    public function process_payment($order_id): array
    {
        if (isset($_POST['regulation'])) {
            $regulation = (bool)$_POST['regulation'];
        } else {
            $regulation = (bool)$_POST['regulation_' . $this->method] ?? false;
        }

        return $this->process_on_paywall($order_id, $this->method, $regulation);
    }

    public function get_receipt_config(): array
    {
        return [
            'method' => $this->method,
            'i18n' => [
                'error' => [
                    'rules' => __('You have to accept the terms and conditions for using the chosen method', 'woocommerce-p24'),
                ],
                'label' => [
                    'submit' => __('Pay with Przelewy24', 'woocommerce-p24'),
                    'regulation' => sprintf(__('I hereby state that I have read the <a href="%s" target="_blank">regulations</a> and <a href="%s" target="_blank">information obligation</a> of "Przelewy24"', 'woocommerce-p24'), Core::get_rules_url(), Core::get_tos_url()),
                ]
            ]
        ];
    }

    public function legacy_checkout_validation($fields, $errors): void
    {
        if (isset($_POST['payment_method']) && $_POST['payment_method'] === $this->id) {
            if (empty($_POST['regulation_' . $this->method])) {
                $errors->add('validation', __('You have to accept the terms and conditions for using the chosen method', 'woocommerce-p24'));
            }
        }
    }
}
