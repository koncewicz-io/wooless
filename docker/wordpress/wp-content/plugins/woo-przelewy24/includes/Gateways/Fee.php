<?php

namespace WC_P24\Gateways;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Multicurrency\Multicurrency;

class Fee
{
    private \WC_Payment_Gateway $gateway;
    private $virtual_gateway;

    public function __construct($gateway, $virtual_gateway = null)
    {
        $this->gateway = $gateway;
        $this->virtual_gateway = $virtual_gateway;

        if ($this->is_enabled()) {
            add_action('woocommerce_checkout_init', [$this, 'legacy_change_payment_method']);
            add_action('woocommerce_cart_calculate_fees', [$this, 'manage_fee'], 10, 1);
        }
    }


    public function legacy_change_payment_method()
    {
        wc_enqueue_js("jQuery(function($){
                           $('form.checkout').on('change', 'input[name=payment_method]', function(){
                               $(document.body).trigger('update_checkout');
                           });
                       });");
    }

    public function is_enabled(): bool
    {
        return $this->gateway->get_option('fee_enabled') === 'yes';
    }

    public function get_fee_name(): string
    {
        return $this->gateway->get_option('fee_name');
    }

    public function get_fee_value(): float
    {
        $value = (float)$this->gateway->get_option('fee_value');

        if (Multicurrency::is_enabled()) {
            $value *= Multicurrency::get_config()->get_multiplier();
        }

        return $value;
    }

    private function get_chosen_payment_method(): string
    {
        $available_methods = array_values(WC()->payment_gateways()->get_available_payment_gateways());
        $default_payment_method = !empty($available_methods) ? $available_methods[0] : null;
        $selected_payment_method_id = WC()->session->get('chosen_payment_method');

        return $selected_payment_method_id ?: ($default_payment_method ? $default_payment_method->id : '');
    }

    public function manage_fee($cart): void
    {
        $chosen_payment_method_id = $this->get_chosen_payment_method();
        $is_virtual = (bool)$this->virtual_gateway;

        $virtual_gateways = array_map(function ($gateway) {
            return $gateway->id;
        }, Gateways_Manager::$extra_gateways);

        if ($chosen_payment_method_id == $this->gateway->id || ($is_virtual && in_array($chosen_payment_method_id, $virtual_gateways))) {
            $cart->add_fee($this->get_fee_name(), $this->get_fee_value());
        } else {
            $fees = $cart->get_fees();

            foreach ($fees as $key => $fee) {
                if ($fee->name === $this->get_fee_name()) {
                    unset($fees[$key]);
                }
            }

            $cart->fees_api()->set_fees($fees);
        }
    }
}
