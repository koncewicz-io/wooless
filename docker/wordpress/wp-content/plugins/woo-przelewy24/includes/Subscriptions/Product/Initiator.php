<?php

namespace WC_P24\Subscriptions\Product;

use WC_P24\Render;

class Initiator
{
    public function __construct()
    {
        add_filter('product_type_selector', [$this, 'register_product_type']);
        add_filter('woocommerce_product_class', [$this, 'register_product_class'], 20, 2);
        add_action('woocommerce_process_product_meta', [$this, 'back_compatibility_migrate'], 20, 2);
        add_filter('woocommerce_product_data_tabs', [$this, 'hide_product_blocks']);
        add_action('woocommerce_' . Product::TYPE . '_add_to_cart', [$this, 'add_to_cart_button']);
    }

    // for back compatibility purpose
    public function back_compatibility_migrate($post_id, $post)
    {
        if ($_POST['product-type'] === Product::TYPE) {
            update_post_meta($post_id, '_migrated', 1);
        }
    }

    public function register_product_type(array $types): array
    {
        $types[Product::TYPE] = __('Subscription P24', 'woocommerce-p24');

        return $types;
    }

    public function register_product_class($classname, $product_type)
    {
        if ($product_type == Product::TYPE) {
            $classname = Product::class;
        }

        return $classname;
    }

    public function hide_product_blocks($product_data_tabs)
    {
        $product_data_tabs['inventory']['class'][] = 'hide_if_' . Product::TYPE;
        $product_data_tabs['shipping']['class'][] = 'hide_if_' . Product::TYPE;
        $product_data_tabs['attribute']['class'][] = 'hide_if_' . Product::TYPE;
        $product_data_tabs['variations']['class'][] = 'hide_if_' . Product::TYPE;
        $product_data_tabs['advanced']['class'][] = 'hide_if_' . Product::TYPE;

        return $product_data_tabs;
    }

    public function add_to_cart_button()
    {
        global $product;

        Render::template('subscription/add-to-cart', ['product' => $product], true);
    }
}
