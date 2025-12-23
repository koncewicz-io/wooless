<?php

namespace WC_P24\Gateways\Online_Payments;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Render;

trait Online_Payments_Legacy_Support
{
    public function payment_fields(): void
    {
        parent::payment_fields();

        if ($this->has_fields) {
            $settings = $this->get_receipt_config();
            Render::template('gateway/online-payments', $settings);
        }
    }

    public function process_payment($order_id): array
    {
        $method = (int)$_POST['method'] ?? null;

        if (isset($_POST['regulation'])) {
            $regulation = (bool)$_POST['regulation'];
        } else {
            $regulation = (bool)$_POST['regulation_' . $this->method] ?? false;
        }

        return $this->process_on_paywall($order_id, $method, $regulation);
    }

    public function legacy_checkout_validation($fields, $errors): void
     {
         if (isset($_POST['payment_method']) && $_POST['payment_method'] === $this->id) {
             if (!empty($_POST['method']) && empty($_POST['regulation_0'])) {
                 $errors->add('validation', __('You have to accept the terms and conditions for using the chosen method', 'woocommerce-p24'));
             }
         }
     }
}
