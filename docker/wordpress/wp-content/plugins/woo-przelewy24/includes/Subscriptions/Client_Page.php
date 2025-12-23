<?php

namespace WC_P24\Subscriptions;

use WC_P24\Assets;
use WC_P24\Models\Database\Subscription;
use WC_P24\Render;
use WC_P24\Subscriptions\Product\Product;
use WC_P24\Utilities\Account_Page;
use WC_P24\Utilities\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

class Client_Page extends Account_Page
{
    public function __construct()
    {
        add_filter('woocommerce_customer_get_downloadable_products', [$this, 'filter_downloads'], 9999);
        Ajax::add_action('cancel_subscription', [$this, 'cancel_subscription']);
        $this->assets();

        parent::__construct();
    }

    public function cancel_subscription()
    {
        if (!is_user_logged_in()) exit;

        check_ajax_referer('cancel_subscription', 'nonce');

        global $current_user;

        $id = (int)sanitize_key($_POST['id']);

        $subscriptions = Subscription::findAll([
            'where' => ['user.ID = %d AND t.id = %d', $current_user->ID, $id],
            'limit' => 1
        ]);

        if (empty($subscriptions[0])) {
            $message = __('Subscription cannot be cancelled', 'woocommerce-p24');
            wc_add_notice($message, 'error');
            wp_send_json_error(['error' => true, 'message' => $message]);
            exit;
        }

        $subscription = $subscriptions[0];

        if ($subscription->cancel()) {
            $message = __('Subscription was successfully cancelled', 'woocommerce-p24');
            wc_add_notice($message);
            wp_send_json_success(['success' => true, 'message' => $message]);
        }

        exit;
    }


    public function tab_name(): string
    {
        return 'p24-subscriptions';
    }

    public function filter_downloads(array $downloads): array
    {
        foreach ($downloads as $key => $download) {
            $product = wc_get_product($download['product_id']);

            if (in_array($product->get_type(), [Product::TYPE])) {
                unset($downloads[$key]);
            }
        }

        return $downloads;
    }

    public function render_page(): void
    {
        global $current_user;

        $subscriptions = Subscription::findAll([
            'select' => ['t.*', 'product.post_title AS product_title'],
            'where' => ['user.ID = %d', $current_user->ID]
        ]);

        $nonce = wp_create_nonce('cancel_subscription');

        Render::template('subscription/account-tab', ['subscriptions' => $subscriptions, 'nonce' => $nonce], true);
    }

    public function assets()
    {
        Assets::add_script_asset('przelewy24-account-subscriptions-js', 'account-subscriptions.js', false, function () {
            return is_user_logged_in() && is_account_page() && is_wc_endpoint_url($this->tab_name());
        });

        Assets::add_script_localize('przelewy24-account-subscriptions-js', 'przelewy24SubscriptionsParams', [
            'url' => add_query_arg(['action' => 'cancel_subscription'], admin_url('admin-ajax.php'))
        ]);
    }

    public function get_title(): string
    {
        return _x('P24 Subscriptions', 'customer account page title', 'woocommerce-p24');
    }

    public function get_menu_label(): string
    {
        return _x('P24 Subscriptions', 'customer account menu label', 'woocommerce-p24');
    }

}
