<?php

namespace WC_P24\Installments;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use WC_P24\Config;
use WC_P24\Core;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Helper;
use WC_P24\Utilities\Payment_Methods;


class Block_Installments implements IntegrationInterface
{

    public function get_name()
    {
        return 'p24-installments';
    }

    public function initialize()
    {
        $this->register_block_frontend_scripts();
        $this->register_block_editor_scripts();
    }

    public function get_script_handles()
    {
        return ['p24-installments-frontend'];
    }

    public function get_editor_script_handles()
    {
        return ['p24-installments-backend'];
    }

    public function get_script_data()
    {
        $config = Config::get_instance();

        $total = isset(WC()->cart) ? WC()->cart->total : 0;

        $payment_methods = Gateways_Manager::get_available_methods();
        $keys = array_column($payment_methods, 'id');
        $index = in_array(Payment_Methods::P24_INSTALLMENTS, $keys);

        return [
            'config' => [
                'sign' => Installments::get_signature(),
                'posid' => (string)$config->get_merchant_id(),
                'method' => (string)Payment_Methods::P24_INSTALLMENTS,
                'amount' => Helper::to_lowest_unit($total),
                'currency' => $config->get_currency(),
                'lang' => 'pl'
            ],
            'show' => $index,
            'showSimulator' => Installments::show_simulator(),
            'widgetType' => Installments::get_type_of_widget()
        ];
    }

    public function register_block_editor_scripts()
    {
        wp_register_script(
            'p24-installments-backend',
            WC_P24_PLUGIN_URL . '/assets/blocks/block-p24-installments/index.js',
            ['wc-blocks-checkout', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n'],
            Core::$version,
            true
        );
    }

    public function register_block_frontend_scripts()
    {
        wp_register_script(
            'p24-installments-frontend',
            WC_P24_PLUGIN_URL . '/assets/blocks/block-p24-installments/frontend.js',
            ['wc-blocks-checkout', 'wp-element', 'wp-i18n'],
            Core::$version,
            true
        );
    }
}
