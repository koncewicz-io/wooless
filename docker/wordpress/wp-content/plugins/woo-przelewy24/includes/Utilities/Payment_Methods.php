<?php

namespace WC_P24\Utilities;

use WC_P24\API\Resources\Payment_Methods_Resource;

defined('ABSPATH') || exit;

class Payment_Methods
{
    const BLIK = ['Blik'];
    const BANK_TRANSFERS = ['FastTransfers', 'eTransfer'];
    const CREDIT_CARDS = ['Credit Card'];
    const WALLETS = ['Wallet'];

    const PAYWALL_PAYMENT = 0;
    const BLIK_PAYMENT = 181;
    const CARD_PAYMENT = 218;
    const CARD_PAYMENT_ALT = [147, 218, 220, 241, 242];
    const GOOGLE_PAY = 229;
    const GOOGLE_PAY_ALT = [229, 238, 240, 264, 265];
    const APPLE_PAY = 252;
    const APPLE_PAY_ALT = [232, 239, 252, 253];
    const P24_INSTALLMENTS = 303;
    const EXCLUDED_METHODS = [181];
    const CLICK_TO_PAY_METHODS = [241, 242];

    public static $already_requested = [];

    private static function get_groups(array $methods): array
    {
        return array_map(function ($method) {
            return strtolower($method);
        }, $methods);
    }

    public static function get_methods_by_group(array $type, array $payment_methods): array
    {
        $available = array_filter($payment_methods, function ($method) use ($type) {
            $group = strtolower($method['group']);
            return in_array($group, self::get_groups($type));
        });

        return array_values($available);
    }

    public static function get_available_methods(): array
    {
        if (empty($methods = get_transient('_p24_available_methods'))) {
            $methods = self::get_payment_methods();
            // Cached array of available methods for 24 hours only for initial create Virtual Gateways, they are filtered later in Gateways_Manages/filter_gateways.php depends on cart value
            !empty($methods) && set_transient('_p24_available_methods', $methods, 60 * 60 * 24);
        }

        return $methods;
    }

    public static function get_popular_methods_icons(array $available_methods): array
    {
        $methods_icons = [];

        $blik = self::get_methods_by_group(self::BLIK, $available_methods);
        $credit_cards = self::get_methods_by_group(self::CREDIT_CARDS, $available_methods);
        $wallets = self::get_methods_by_group(self::WALLETS, $available_methods);

        if (!empty($blik)) {
            $methods_icons[] = self::convert($blik[0]);
        }

        if (!empty($credit_cards)) {
            $methods_icons[] = self::convert($credit_cards[0]);
        }

        if (count($methods_icons) < 2 && count($wallets)) {
            $methods_icons[] = self::convert($wallets[0]);
        }

        return $methods_icons;
    }

    public static function convert(array $method): array
    {
        return ['src' => $method['imgUrl'], 'name' => $method['name'], 'id' => $method['id'], 'type' => $method['group']];
    }

    public static function get_payment_methods(?int $value = null, ?string $currency = null): array
    {
        $request_key = $value . '_' . $currency;

        if (!empty(self::$already_requested[$request_key])) {
            return self::$already_requested[$request_key];
        }

        $client = new Payment_Methods_Resource();
        $methods = $client->get_payment_methods($value, $currency);
        $methods = $methods['data'] ?? [];

        if (!empty($methods)) {
            self::$already_requested[$request_key] = $methods;
        }

        return $methods;
    }

    public static function prepare_methods(array $methods, ?string $order = '', bool $exclude = false): array
    {
        if ($exclude) {
            $methods = array_filter($methods, function ($method) {
                return !in_array($method['id'] ?? null, self::EXCLUDED_METHODS);
            });
        }

        $match = preg_match('/([\d,]+)?:([\d,]+)?/', $order, $matches);

        if (!$match) {
            return array_map(function ($method) {
                $method['featured'] = false;

                return $method;
            }, $methods) ?: [];
        }

        $featured = array_filter(explode(',', $matches[1]));
        $methods_by_id = array_combine(array_column($methods, 'id'), $methods);
        $merge_all = array_merge(explode(',', $matches[2] ?? ''), array_keys($methods_by_id));
        $order = array_unique(array_filter($merge_all));

        return array_values(array_filter(array_map(function ($id) use ($methods_by_id, $featured) {
            $item = null;

            if (isset($methods_by_id[$id])) {
                $item = $methods_by_id[$id];
                $item['featured'] = in_array($id, $featured);
            }

            return $item;
        }, $order)));
    }
    public static function get_group_name($method_id): string
    {
        $available_methods = self::get_available_methods();

        foreach ($available_methods as $method) {
            if ((int)$method['id'] === (int)$method_id) {
                return $method['group'] ?? 'Other';
            }
        }

        return 'Other';
    }

}
