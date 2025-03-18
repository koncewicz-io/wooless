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
            if (strpos($gateway, 'przelewy24') === 0) {
                return Core::MAIN_METHOD;
            }
        }

        return $gateway;
    }
}
