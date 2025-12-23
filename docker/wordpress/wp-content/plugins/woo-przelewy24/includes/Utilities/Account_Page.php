<?php

namespace WC_P24\Utilities;

abstract class Account_Page
{
    public function __construct()
    {
        add_action('init', [$this, 'add_rewrite']);
        add_filter('query_vars', [$this, 'add_query_var']);
        add_filter('woocommerce_get_query_vars', [$this, 'add_query_var']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_tab_name']);
        add_filter('woocommerce_account_' . $this->tab_name() . '_endpoint', [$this, 'render_page']);
        add_filter('the_title', [$this, 'change_title'], 10, 2);
    }

    public function get_url(): string
    {
        return wc_get_account_endpoint_url($this->get_url());
    }
    abstract public function get_title(): string;
    abstract public function get_menu_label(): string;

    public function add_rewrite()
    {
        add_rewrite_endpoint($this->tab_name(), EP_ROOT | EP_PAGES);
    }

    public function add_query_var(array $vars): array
    {
        $vars[$this->tab_name()] = $this->tab_name();

        return $vars;
    }

    public function change_title($title)
    {
        if ($this->get_title()) {
            global $wp_query;

            if (in_the_loop() && array_key_exists($this->tab_name(), $wp_query->query)) {
                $title = $this->get_title();
            }
        }

        return $title;
    }

    public function add_tab_name($items)
    {
        $new_items = [];

        if ($this->get_menu_label()) {
            foreach ($items as $key => $item) {
                if ($key === 'customer-logout') {
                    $new_items[$this->tab_name()] = $this->get_menu_label();
                }

                $new_items[$key] = $item;
            }
        }

        return empty($new_items) ? $items : $new_items;
    }


    abstract public function tab_name(): string;

    abstract public function render_page(): void;

}
