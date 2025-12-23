<?php

namespace WC_P24\Installments;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use WC_P24\Core;

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
        return [ 'p24-installments-frontend' ];
    }

    public function get_editor_script_handles()
    {
        return [ 'p24-installments-backend' ];
    }

    public function get_script_data()
    {
        $min   = Installments::get_min_product_price();
        $total = WC()->cart ? WC()->cart->total : 0;

        if ( $total < $min ) {
            return [ 'enabled' => false ];
        }

        return Client_Side::get_checkout_data();
    }

    public function register_block_editor_scripts()
    {
        wp_register_script(
            'p24-installments-backend',
            WC_P24_PLUGIN_URL . 'assets/blocks/block-p24-installments/index.js',
            [ 'wc-blocks-checkout', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n' ],
            Core::$version,
            true
        );

        wp_set_script_translations(
            'p24-installments-backend',
            'woocommerce-p24',
            WC_P24_PLUGIN_PATH . 'languages'
        );
    }

    public function register_block_frontend_scripts()
    {
        wp_register_script(
            'p24-installments-frontend',
            WC_P24_PLUGIN_URL . 'assets/blocks/block-p24-installments/frontend.js',
            [ 'wc-blocks-checkout', 'wp-element', 'wp-i18n' ],
            Core::$version,
            true
        );

        wp_set_script_translations(
            'p24-installments-frontend',
            'woocommerce-p24',
            WC_P24_PLUGIN_PATH . 'languages'
        );
    }
}
