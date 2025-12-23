<?php

namespace WC_P24\Multicurrency;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Config;
use WC_P24\Utilities\Module;

class Multicurrency extends Module
{
    const ENABLE_KEY = 'p24_multicurrency_enabled';
    const CURRENCY_COOKIE_NAME = 'p24_currency_code';

    public function __construct()
    {
        parent::__construct();

        $this->settings = new Settings();
        new Widget();
    }

    public static function is_enabled(): bool
    {
        $option = get_option(self::ENABLE_KEY, 'no');

        return $option === 'yes';
    }

    public static function get_available_currencies($with_base = false): array
    {
        $result = [];

        foreach (get_option('p24_currencies', []) as $value) {
            $result[] = self::get_config($value);
        }

        if ($with_base) {
            $current = new Currency_Config();
            $config = Config::get_instance();
            $current->set_config(0,  '', '', $config->get_base_currency());
            $result[] = $current;
        }

        return $result;
    }

    public static function get_config(?string $currency_code = null): Currency_Config
    {
        $config = new Currency_Config();

        if (!$currency_code) {
            $currency_code = Config::get_instance()->get_currency();
        }

        $currency_code = strtolower($currency_code);
        $array = get_option('p24_currency_' . $currency_code, []);

        if (!empty($array)) {
            $config->set_config((int)$array['merchant_id'], $array['crc_key'], $array['reports_key'], $currency_code);
            $config->set_multiplier($array['multiplier']);
        }

        return $config;
    }

    public static function setup(string $currency_code): ?Currency_Config
    {
        $result = null;
        $config = Config::get_instance();

        if (!self::compare_currency($currency_code)) {
            $currency = self::get_config($currency_code);
             $config->set_config($currency);
             $result = $currency;
        }

        return $result;
    }

    public static function compare_currency(string $new, string $current = ''): bool
    {
        if (!$current) {
            $current = Config::get_instance()->get_base_currency();
        }

        $current = strtolower($current);
        $new = strtolower($new);

        return $current === $new;
    }

    protected function on_client(): void
    {
        new Client();
    }

    protected function on_admin(): void
    {
        new Admin();
    }
}
