<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Store_API_Settings {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_store_api_endpoint']);
    }

    public function register_store_api_endpoint() {
        register_rest_route('wc/store/v1', '/settings', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_settings'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function get_settings() {
        return rest_ensure_response([
            'woocommerce_allowed_countries' => get_option('woocommerce_allowed_countries'),
            'woocommerce_specific_allowed_countries' => get_option('woocommerce_specific_allowed_countries'),
            'furgonetka_deliveryToType' => get_option('furgonetka_deliveryToType') ?: [],
        ]);
    }
}
