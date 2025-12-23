<?php

namespace WC_P24\Utilities;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WC_P24\API\Resources\Payment_Methods_Resource;
use WC_P24\Core;
use WC_P24\Gateways\Gateways_Manager;

class Base_Gateway_Block extends AbstractPaymentMethodType
{
    protected \WC_Payment_Gateway $gateway;
    protected Payment_Methods_Resource $client;
    protected string $block_name;

    public function __construct()
    {
        $this->client = new Payment_Methods_Resource();
    }

    protected function get_script_path(): string
    {
        return '';
    }

    public function initialize(): void
    {
        $this->settings   = get_option('woocommerce_' . $this->get_name() . '_settings', []);
        $this->block_name = $this->get_name() . '-block';
    }

    public function get_payment_method_script_handles(): array
    {
        wp_register_script(
            $this->block_name,
            $this->get_script_path(),
            [
                'wc-blocks-registry',
                'wc-settings',
                'wc-blocks-data-store',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            Core::$version,
            true
        );

        wp_set_script_translations(
            $this->block_name,
            'woocommerce-p24',
            WC_P24_PLUGIN_PATH . 'languages'
        );

        return [ $this->block_name ];
    }

    public function get_payment_method_data(): array
    {
        $this->gateway = Gateways_Manager::$gateways[ $this->get_name() ] ?? null;

        if ( ! $this->gateway || ! method_exists($this->gateway, 'get_receipt_config') ) {
            return [];
        }

        return array_merge($this->gateway->get_receipt_config(), [
            'debug'       => defined('P24_DEBUG') ? P24_DEBUG : false,
            'name'        => $this->gateway->id,
            'icon'        => $this->gateway->icon,
            'title'       => $this->gateway->title,
            'description' => $this->gateway->description,
        ]);
    }
}
