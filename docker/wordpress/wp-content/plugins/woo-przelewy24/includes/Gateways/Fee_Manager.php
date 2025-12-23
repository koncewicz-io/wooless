<?php

namespace WC_P24\Gateways;

class Fee_Manager
{
    /** @var $fees Fee[] * */
    public static array $fees = [];

    public function __construct()
    {
        add_action('woocommerce_checkout_init', [$this, 'legacy_change_payment_method']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'manage_fees']);
    }

    public static function add_fee(Fee $fee): void
    {
        self::$fees[] = $fee;
    }

    public function legacy_change_payment_method(): void
    {
        wc_enqueue_js("jQuery(function($){
                           $('form.checkout').on('change', 'input[name=payment_method]', function(){
                               $(document.body).trigger('update_checkout');
                           });
                       });");
    }

    private function get_chosen_payment_method(): string
    {
        $available_methods = array_values(array_filter(WC()->payment_gateways()->payment_gateways(), function ($gateway) {
            return $gateway->enabled;
        }));

        $default_payment_method = !empty($available_methods) ? $available_methods[0] : null;
        $selected_payment_method_id = WC()->session->get('chosen_payment_method');

        return $selected_payment_method_id ?: ($default_payment_method ? $default_payment_method->id : '');
    }

    public function is_for_chosen_gaetway(Fee $fee, string $chosen): bool
    {
        return $fee->virtual_gateway ? $fee->virtual_gateway->id === $chosen : $fee->gateway->id === $chosen;
    }

    public function manage_fees($cart): void
    {
        if (!count(self::$fees)) return;

        $chosen_payment_method_id = $this->get_chosen_payment_method();

        foreach (self::$fees as $fee) {
            if ($this->is_for_chosen_gaetway($fee, $chosen_payment_method_id)) {
                $cart->add_fee($fee->get_fee_name(), $fee->get_fee_value());
            }
        }
    }
}
