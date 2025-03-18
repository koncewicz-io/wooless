<?php

namespace WC_P24\Gateways;

use WC_P24\API\Resources\Payment_Methods_Resource;
use WC_P24\Render;
use WC_P24\Utilities\Payment_Methods;

if (!defined('ABSPATH')) {
    exit;
}

trait Settings_Helper
{
    public function fee_settings(): array
    {
        return [
            'separator' => [
                'type' => 'title',
                'title' => '<hr />'
            ],
            'fee_enabled' => [
                'type' => 'checkbox',
                'title' => __('Fee', 'woocommerce-p24'),
                'label' => __('Enable gateway fee', 'woocommerce-p24'),
                'default' => 'no',
            ],
            'fee_name' => [
                'type' => 'text',
                'title' => __('Name of fee', 'woocommerce-p24'),
                'default' => __('Przelewy24 service', 'woocommerce-p24'),
            ],
            'fee_value' => [
                'type' => 'number',
                'title' => __('Value of fee', 'woocommerce-p24'),
                'description' => __('Additional cost added to the order at checkout', 'woocommerce-p24'),
            ]
        ];
    }
}
