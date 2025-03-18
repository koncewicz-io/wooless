<?php

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\ProductSchema;

class WC_Product_ACF_Extension {

    private $extend;

    public function init(ExtendSchema $extend_rest_api) {
        $this->extend = $extend_rest_api;
        $this->extend_store();
    }

    private function extend_store() {
        $this->extend->register_endpoint_data([
            'endpoint' => ProductSchema::IDENTIFIER,
            'namespace' => 'wooless',
            'data_callback' => [$this, 'extend_product_data'],
            'schema_callback' => [$this, 'extend_product_schema'],
        ]);
    }

    public function extend_product_data(\WC_Product $product): array {
        if (!function_exists('get_fields')) {
            return [
                'acf' => []
            ];
        }

        $fields = get_fields($product->get_id()) ?: [];

        return [
            'acf' => $fields
        ];
    }

    public function extend_product_schema(): array {
        return [
            'acf' => [
                'description' => __('Advanced Custom Fields (ACF) for products', 'wooless'),
                'type' => 'object',
                'readonly' => true,
            ],
        ];
    }
}
