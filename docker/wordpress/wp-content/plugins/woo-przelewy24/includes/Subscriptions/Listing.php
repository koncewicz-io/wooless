<?php

namespace WC_P24\Subscriptions;

use WC_P24\Models\Database\Subscription;

if (!defined('ABSPATH')) {
    exit;
}

class Listing extends \WP_List_Table
{
    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />',
            'user' => __('User', 'woocommerce-p24'),
            'product' => __('Product', 'woocommerce-p24'),
            'valid' => __('Valid until', 'woocommerce-p24'),
            'status' => __('Status', 'woocommerce-p24')
        ];
    }

    public function column_default($item, $column_name)
    {
        $result = '';

        switch ($column_name) {
            case 'user':
                $result = $item->customer_name . ' (' . $item->customer_email . ')';
                break;
            case 'product':
                $result = '<a href="' . get_edit_post_link($item->get_product_id()) . '">' . $item->product_title . '</a>';
                break;
            case 'valid':
                $result = $item->get_valid_to()->format('d-m-Y H:i');
                break;
            case 'status':
                $html = '<mark>%s</mark>';
                $result = sprintf($html, $item->get_status_label());
                break;
        }

        return $result;
    }

    protected function get_views()
    {
        global $pagenow;

        $status = $this->get_param('status');

        $views = [
            '' => __('All', 'woocommerce-p24'),
            'active' => __('Active', 'woocommerce-p24'),
            'inactive' => __('Inactive', 'woocommerce-p24')
        ];

        $current_page = add_query_arg($_GET, admin_url($pagenow));

        $links = [];

        foreach ($views as $key => $view) {
            $class = $status == $key ? 'class="current"' : '';
            $url = add_query_arg(['status' => $key], $current_page);

            $links[] = '<a href="' . $url . '" ' . $class . '>' . $view . '</a>';
        }

        return $links;
    }

    public function get_param(string $name): string
    {
        return $this->get_params()[$name];
    }

    public function get_params(): array
    {
        $values = [
            'order' => ['asc', 'desc'],
            'orderby' => ['user', 'product', 'valid'],
            'status' => ['active', 'inactive'],
        ];

        $result = [];

        foreach ($values as $key => $possible_values) {
            $value = !empty($_GET[$key]) ? sanitize_key($_GET[$key]) : '';

            if (!in_array($value, $possible_values)) {
                $value = '';
            }

            $result[$key] = $value;
        }

        return $result;
    }

    protected function get_sortable_columns()
    {
        return [
            'user' => ['user', true],
            'product' => ['product', true],
            'valid' => ['valid', true],
        ];
    }

    function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="element[]" value="%s" />', $item->get_id());
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
        $now = new \DateTime();
        $params = $this->get_params();

        $where = [];
        $order = strtoupper($params['order']);
        $order_by = 'customer_name, product_id';

        $order_option = [
            'user' => 'customer_name',
            'valid' => 't.valid_to',
            'product' => 'product_title'
        ];

        $where_options = [
            'active' => ['t.valid_to > %s', $now->format('Y-m-d H:i:s')],
            'inactive' => ['t.valid_to < %s', $now->format('Y-m-d H:i:s')]
        ];

        if (in_array($params['orderby'], array_keys($order_option))) {
            $order_by = $order_option[$params['orderby']];
        }

        if (in_array($params['status'], array_keys($where_options))) {
            $where = $where_options[$params['status']];
        }

        $items = Subscription::findAll([
            'select' => ['t.id', 't.valid_to', 't.status', 't.product_id', 'user.user_nicename AS customer_name', 'user.user_email AS customer_email', 'product.post_title AS product_title'],
            'where' => $where,
            'order' => $order_by . ' ' . $order
        ]);

        $this->items = $items;
    }
}
