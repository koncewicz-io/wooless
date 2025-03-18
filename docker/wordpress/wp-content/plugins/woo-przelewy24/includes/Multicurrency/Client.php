<?php

namespace WC_P24\Multicurrency;

if (!defined('ABSPATH')) {
    exit;
}

class Client
{
    private ?Currency_Config $currency_config;
    private ?string $user_currency = null;

    public function __construct()
    {
        $this->user_currency = $this->save_currency();

        if ($this->check_client_currency()) {
            // Change currency on shop
            add_action('woocommerce_currency', [$this, 'change_currency']);

            // Multiply product prices by config multiplier
            add_action('woocommerce_product_get_price', [$this, 'change_price'], 25);
            add_action('woocommerce_product_get_regular_price', [$this, 'change_price'], 25);
            add_action('woocommerce_product_get_sale_price', [$this, 'change_price'], 25);

            add_action('woocommerce_product_variation_get_price', [$this, 'change_price'], 25);
            add_action('woocommerce_product_variation_get_regular_price', [$this, 'change_price'], 25);
            add_action('woocommerce_product_variation_get_sale_price', [$this, 'change_price'], 25);

            add_filter('woocommerce_get_price_html', [$this, 'change_variant_range_price'], 25, 2);

            // Multiply product fixed discounts
            add_filter('woocommerce_coupon_get_discount_amount', [$this, 'change_product_fixed_discount'], 25, 5);
            // Multiply cart fixed discounts
            add_filter('woocommerce_get_shop_coupon_data', [$this, 'change_cart_discount'], 25, 3);
            // Multiply shipping prices
            add_filter('woocommerce_package_rates', [$this, 'change_shipping_prices'], 25, 2);
        }
    }

    public function check_client_currency(): bool
    {
        $has_other = false;
        $currency = false;

        if (!empty($_COOKIE[Multicurrency::CURRENCY_COOKIE_NAME])) {
            $currency = strtoupper(sanitize_key($_COOKIE[Multicurrency::CURRENCY_COOKIE_NAME]));
        }

        if ($this->user_currency) {
            $currency = $this->user_currency;
        }

        if ($currency && $this->currency_config = Multicurrency::setup($currency)) {
            $has_other = true;
        }

        return $has_other;
    }

    public function save_currency(): ?string
    {
        $result = null;

        if (!empty($_POST['currency'])) {
            $currency = strtoupper(sanitize_key($_POST['currency']));
            $symbol = get_woocommerce_currency_symbol($currency);

            if ($symbol) {
                $result = $currency;
                wc_setcookie(Multicurrency::CURRENCY_COOKIE_NAME, $currency);
            }
        }

        return $result;
    }

    public function change_currency(): string
    {
        return $this->currency_config->get_currency_code();
    }

    public function change_price($price)
    {
        if ($price) {
            $price *= $this->currency_config->get_multiplier();
        }

        return $price;
    }

    public function change_variant_range_price($price_html, $product): string
    {
        if ($product->is_type('variable')) {
            $min_price = $product->get_variation_price('min');
            $max_price = $product->get_variation_price('max');

            $price_html = wc_price($min_price * $this->currency_config->get_multiplier()) . ' - ' . wc_price($max_price * $this->currency_config->get_multiplier());
        }

        return $price_html;
    }

    public function change_product_fixed_discount($discount, $price_to_discount, $item, $false, $coupon): float
    {
        switch ($coupon->get_discount_type()) {
            case 'fixed_product':
                $discount *= $this->currency_config->get_multiplier();
                break;
        }

        return $discount;
    }

    public function change_cart_discount($false, $code, $coupon)
    {
        $coupon_id = wc_get_coupon_id_by_code($code);
        $fixed = false;

        $discount_type = get_post_meta($coupon_id, 'discount_type', true);

        switch ($discount_type) {
            case 'fixed_cart':
                $fixed = true;
                $current_coupon_amount = get_post_meta($coupon_id, 'coupon_amount', true);
                $coupon->set_amount($current_coupon_amount * $this->currency_config->get_multiplier());
                break;
        }

        return !$fixed ? $false : $coupon;
    }

    public function change_shipping_prices($rates, $package): array
    {
        foreach ($rates as $rate) {
            $rate->cost *= $this->currency_config->get_multiplier();
        }

        return $rates;
    }
}
