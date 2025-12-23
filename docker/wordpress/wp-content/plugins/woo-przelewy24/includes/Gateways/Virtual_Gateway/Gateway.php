<?php

namespace WC_P24\Gateways\Virtual_Gateway;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Core;
use WC_P24\Gateways\Fee;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Gateways\Payment_Helpers;
use WC_Payment_Gateway;

class Gateway extends WC_Payment_Gateway
{
    use Payment_Helpers;
    use Virtual_Gateway_Legacy_Support;

    public bool $is_featured = true;

    public function __construct(int $method, string $name, string $icon = null)
    {
        $this->method = $method;
        $this->id = 'p24-online-payments-' . $method;
        $this->description = ' ';

        $this->title = $name;
        $this->supports = ['products', 'refunds'];
        $this->enabled = 'yes';

        new Fee(Gateways_Manager::$gateways[Core::MAIN_METHOD], $this);

        if ($icon) {
            $this->icon = apply_filters('woocommerce_gateway_icon', $icon);
        }

        add_action('woocommerce_after_checkout_validation', [$this, 'legacy_checkout_validation'], 10, 2);
    }
}
