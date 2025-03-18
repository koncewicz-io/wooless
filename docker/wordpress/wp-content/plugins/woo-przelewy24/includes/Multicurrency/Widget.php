<?php

namespace WC_P24\Multicurrency;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Config;
use WC_P24\Render;

class Widget
{
    public function __construct()
    {
        add_action('init', [$this, 'register_block']);
        add_shortcode('p24_multicurrency_switcher', [$this, 'render']);
    }

    public function register_block(): void
    {
        register_block_type(WC_P24_WIDGET_NAMESPACE . '/currency-selector', [
            'title' => __('Multi currency switcher', 'woocommerce-p24'),
            'category' => 'Przelewy24',
            'render_callback' => [$this, 'render']
        ]);
    }

    public function render(): string
    {
        $currencies = Multicurrency::get_available_currencies(true);
        $current = Config::get_instance()->get_currency();

        return Render::return('block/currency_selector', [
            'enabled' => Multicurrency::is_enabled(),
            'currencies' => $currencies,
            'current' => $current
        ], true);
    }
}
