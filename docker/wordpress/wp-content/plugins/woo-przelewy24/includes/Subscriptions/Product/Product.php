<?php

namespace WC_P24\Subscriptions\Product;

if (!defined('ABSPATH')) {
    exit;
}

class Product extends \WC_Product
{
    const TYPE = 'p24_subscription';

    public function __construct($product = 0)
    {
        parent::__construct($product);

        $this->back_compability();
        $this->set_virtual(true);
        $this->set_downloadable(true);
        $this->set_manage_stock(false);
    }

    private function back_compability()
    {
        $is_migrated = (bool)$this->get_meta('_migrated');

        if ($is_migrated) return;

        $days = (int)$this->get_meta('_days');
        $download_expiry = (int)$this->get_prop('download_expiry');
        if ($days && $days !== $download_expiry) {
            $this->set_prop('download_expiry', $days);
        }

        $subscription_price = (float)$this->get_meta('_subscription_price');
        $price = (float)$this->get_prop('regular_price');

        if ($subscription_price && $subscription_price !== $price) {
            $this->set_prop('regular_price', $subscription_price);
            $this->set_prop('price', $subscription_price);
        }

        $files = $this->get_meta('p24_sub_files');

        if (!empty($files)) {
            $downloads = [];

            foreach ($files as $file) {
                $download = new \WC_Product_Download();
                $download->set_name($file['name']);
                $download->set_file($file['url']);
                $downloads[] = $download;
            }

            $this->set_downloads($downloads);
        }
    }

    public function possible_to_buy()
    {
        return is_user_logged_in();
    }

    public function get_type(): string
    {
        return self::TYPE;
    }

    public function get_days(): int
    {
        $days = (int)$this->get_prop('download_expiry');

        return $days;
    }

    public function add_to_cart_text()
    {
        $possible_to_buy = $this->possible_to_buy();
        $text = $possible_to_buy ? __('Subscribe', 'woocommerce') : __('Read more', 'woocommerce');

        return apply_filters('woocommerce_product_add_to_cart_text', $text, $this);
    }

    public function add_to_cart_url()
    {
        $possible_to_buy = $this->possible_to_buy();

        $url = $possible_to_buy ? remove_query_arg(
            'added-to-cart',
            add_query_arg(
                ['add-to-cart' => $this->get_id()],
                (function_exists('is_feed') && is_feed()) || (function_exists('is_404') && is_404()) ? $this->get_permalink() : ''
            )
        ) : $this->get_permalink();

        return apply_filters('woocommerce_product_add_to_cart_url', $url, $this);
    }
}
