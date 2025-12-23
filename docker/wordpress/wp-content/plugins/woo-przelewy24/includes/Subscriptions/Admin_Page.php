<?php

namespace WC_P24\Subscriptions;

use WC_P24\Render;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Page
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_to_menu']);
        add_filter('woocommerce_order_item_display_meta_key', [$this, 'display_order_item_details'], 10, 3);
    }

    public function add_to_menu(): void
    {
        add_submenu_page('woocommerce', __('P24 Subscriptions', 'woocommerce-p24'), __('P24 Subscriptions', 'woocommerce-p24'), 'manage_options', 'p24-subscriptions', [$this, 'render_page']);
    }

    public function render_page()
    {
        $table = new Listing();
        $table->prepare_items();

        Render::template('admin/subscriptions', ['table' => $table, 'url' => Export::get_url()]);
    }

    public function display_order_item_details($display_key)
    {
        if ($display_key == '_p24_subscription_id') {
            $display_key = __('P24 Subscription ID', 'woocommerce-p24');
        }

        return $display_key;
    }
}
