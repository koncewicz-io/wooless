<?php

namespace WC_P24\Compatibility\Back_Compatibility;

use WC_P24\Core;

class Refund
{
    public function __construct()
    {
        add_filter('woocommerce_order_get_payment_method', [$this, 'refund'], 10);
    }

    public function refund($gateway)
    {
        if (is_admin()) {
            $oldPlugin = strpos($gateway, 'przelewy24') === 0;
            $featuredMethods = strpos($gateway, Core::MAIN_METHOD . '-') === 0;

            if ($oldPlugin || $featuredMethods) {
                return Core::MAIN_METHOD;
            }
        }

        return $gateway;
    }
}
