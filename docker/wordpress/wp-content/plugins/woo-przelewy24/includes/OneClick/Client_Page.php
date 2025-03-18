<?php

namespace WC_P24\OneClick;

use WC_P24\Assets;
use WC_P24\Models\Database\Reference;
use WC_P24\Models\Database\Subscription;
use WC_P24\Render;
use WC_P24\Utilities\Account_Page;
use WC_P24\Utilities\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

class Client_Page extends Account_Page
{
    public function __construct()
    {
        Ajax::add_action('remove_one_click', [$this, 'remove_one_click']);
        $this->assets();

        parent::__construct();
    }

    public function remove_one_click()
    {
        if (!is_user_logged_in()) exit;

        check_ajax_referer('remove_one_click', 'nonce');

        global $current_user;

        $id = (int)sanitize_key($_POST['id']);

        $references = Reference::findAll([
            'where' => ['user.ID = %d AND t.id = %d', $current_user->ID, $id],
            'limit' => 1
        ]);

        if (empty($references[0])) {
            $message = __('One click cannot be removed', 'woocommerce-p24');
            wc_add_notice($message, 'error');
            wp_send_json_error(['error' => true, 'message' => $message]);
            exit;
        }

        $reference = $references[0];

        if ($reference->delete()) {
            $message = __('One click successfully removed', 'woocommerce-p24');
            wc_add_notice($message);
            wp_send_json_success(['success' => true, 'message' => $message]);
        }

        exit;
    }

    public function tab_name(): string
    {
        return 'p24-one-click';
    }

    public function render_page(): void
    {
        global $current_user;

        $items = Reference::findAll([
            'select' => ['t.*', 'COUNT(subscriptions.card_id) as subs'],
            'where' => ['user.ID = %d', (int)$current_user->ID],
            'join' => 'LEFT OUTER JOIN ' . Subscription::table_name() . ' as subscriptions ON subscriptions.card_id = t.id',
            'group_by' => 't.id'
        ]);

        $nonce = wp_create_nonce('remove_one_click');

        Render::template('account/one-click-tab', ['items' => $items, 'nonce' => $nonce], true);
    }

    public function assets()
    {
        Assets::add_script_asset('przelewy24-account-one-clicks-js', 'account-one-clicks.js', false, function () {
            return is_user_logged_in() && is_account_page() && is_wc_endpoint_url($this->tab_name());
        });

        Assets::add_script_localize('przelewy24-account-one-clicks-js', 'przelewy24OneClicksParams', [
            'url' => add_query_arg(['action' => 'remove_one_click'], admin_url('admin-ajax.php'))
        ]);
    }

    public function get_title(): string
    {
        return _x("P24 One click's", 'customer account page title', 'woocommerce-p24');
    }

    public function get_menu_label(): string
    {
        return _x("P24 One click's", 'customer account menu label', 'woocommerce-p24');
    }
}
