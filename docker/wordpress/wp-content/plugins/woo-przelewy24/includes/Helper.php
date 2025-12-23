<?php

namespace WC_P24;

use WC_P24\Subscriptions\Product\Product;

if (!defined('ABSPATH')) {
    exit;
}

class Helper
{
    public static function get_language(): string
    {
        $locale = explode('_', get_locale());

        return $locale[0];
    }

    /**
     * Formats the full name of the customer based on their order.
     *
     * @param \WC_Order $order The WooCommerce order object.
     * @return string The formatted full name of the customer, or 'No name provided' if both are empty.
     */
    public static function get_customer_name(\WC_Order $order)
    {
        $firstName = $order->get_billing_first_name();
        $lastName = $order->get_billing_last_name();

        if (!empty($firstName) && !empty($lastName)) {
            $clientName = sprintf('%s %s', $firstName, $lastName);
        } elseif (!empty($firstName)) {
            $clientName = $firstName;
        } elseif (!empty($lastName)) {
            $clientName = $lastName;
        } else {
            $clientName = 'No name provided';
        }

        return $clientName;
    }

    public static function to_lowest_unit(float $amount): int
    {
        return (int)round($amount * 100);
    }

    public static function to_higher_unit(float $amount): float
    {
        return round($amount / 100, 2);
    }

    public static function order_has_subscription_product(\WC_Order $order): bool
    {
        $result = false;

        $items = $order->get_items();

        foreach ($items as $item) {
            $product = $item->get_product();

            if ($product instanceof Product) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public static function anonymize(string $value, int $visible_length = 4): string
    {
        $length = strlen($value);
        $hidden = $length - $visible_length;
        $hidden = $hidden <= 0 ? $length / 2 : $hidden;

        return preg_replace('/^.{' . $hidden . '}/', '*****', $value);
    }

    public static function get_transaction_prefix(): string {
        $woo_version = defined('WC_VERSION') ? WC_VERSION : '';
        $plugin_version = Core::$version;

        return "wpp24{{$woo_version}:{$plugin_version}}";
    }
}
