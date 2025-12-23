<?php

namespace WC_P24\Subscriptions;

use WC_P24\Models\Database\Subscription;
use WC_P24\Utilities\Ajax;

class Export
{
    const ACTION = 'subscription-export';

    public function __construct()
    {
        Ajax::add_action(self::ACTION, [$this, 'export'], true);
    }

    public static function get_url(): string
    {
        return add_query_arg([
            'action' => self::ACTION
        ], admin_url('admin-ajax.php'));
    }

    public function export()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'woocommerce-p24')]);
        }

        $now = new \DateTime();

        $subscriptions = Subscription::findAll([
            'select' => ['t.*', 'user.user_nicename AS customer_name', 'user.user_email AS customer_email', 'product.post_title AS product_title'],
            'where' => ['t.valid_to > %s', $now->format('Y-m-d H:i:s')],
            'order' => 't.valid_to, product_title, customer_email, t.id'
        ]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=p24_subscriptions.csv');

        $stdout = fopen('php://output', 'w');
        $header = array(
            __('ID', 'woocommerce-p24'),
            __('User', 'woocommerce-p24'),
            __('Email', 'woocommerce-p24'),
            __('Valid to ', 'woocommerce-p24'),
            __('Product', 'woocommerce-p24')
        );

        fputcsv($stdout, $header);

        foreach ($subscriptions as $row) {
            fputcsv($stdout, [
                $row->get_id(),
                $row->customer_name,
                $row->customer_email,
                $row->get_valid_to()->format('d-m-Y H:i'),
                $row->product_title
            ]);
        }

        exit;
    }
}
