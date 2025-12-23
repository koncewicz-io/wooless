<?php

namespace WC_P24\UI;

use WC_P24\Core;
use WC_P24\Gateways\Gateways_Manager;

class Menu
{
    public array $items = [];
    private string $current_section;

    public function __construct()
    {
        global $current_section;
        $this->current_section = $current_section ?: '';
        $this->create_menu();
    }

    private function payment_labels($gateway)
    {
        $labels =  [
            Core::APPLE_PAY_IN_SHOP_METHOD => _x('Apple Pay', 'menu', 'woocommerce-p24'),
            Core::BLIK_IN_SHOP_METHOD => _x('BLIK', 'menu', 'woocommerce-p24'),
            Core::CARD_IN_SHOP_METHOD => _x('Card payments', 'menu', 'woocommerce-p24'),
            Core::GOOGLE_PAY_IN_SHOP_METHOD => _x('Google Pay', 'menu', 'woocommerce-p24'),
            Core::MAIN_METHOD => _x('Online payments', 'menu', 'woocommerce-p24')
        ];

        return $labels[$gateway] ?? '';
    }

    private function exclude_from_menu():array {
        return ['encryption'];
    }

    private function create_menu()
    {
        $sections = apply_filters('woocommerce_get_sections_' . WC_P24_SETTINGS_NAME, []);

        $settings = array_map(function ($key, $value) {
            return [
                'id' => $key ?: 'general',
                'label' => $value,
                'url' => add_query_arg([
                    'page' => 'wc-settings',
                    'tab' => WC_P24_SETTINGS_NAME,
                    'section' => sanitize_key($key)
                ], admin_url('admin.php')),
                'class' => $this->current_section == $key ? 'current' : ''
            ];
        }, array_keys($sections), $sections);

        $settings = array_filter($settings, function($item){
            return !in_array($item['id'], $this->exclude_from_menu());
        });

        $gateways = array_map(function ($key) {
            return [
                'id' => $key,
                'label' => $this->payment_labels($key),
                'url' => add_query_arg([
                    'page' => 'wc-settings',
                    'tab' => 'checkout',
                    'section' => sanitize_key($key)
                ], admin_url('admin.php')),
                'class' => $this->current_section == $key ? 'current' : ''
            ];
        }, array_keys(Gateways_Manager::$gateways), Gateways_Manager::$gateways);

        $this->items = array_merge($settings, [null], $gateways);
    }
}
