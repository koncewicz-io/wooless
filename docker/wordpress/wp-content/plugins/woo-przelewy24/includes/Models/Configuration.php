<?php

namespace WC_P24\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Configuration
{
    public int $merchant_id = 0;
    public ?string $crc_key = null;
    public ?string $reports_key = null;
    public ?string $currency_code = null;

    public function __construct($default = false)
    {
        if ($default) {
            $this->merchant_id = (int)get_option('p24_merchant_id', 0);
            $this->crc_key = get_option('p24_crc_key', null);
            $this->reports_key = get_option('p24_reports_key', null);
            $this->currency_code = get_option('woocommerce_currency', null);
        }
    }

    public function set_config(int $merchant_id, ?string $crc_key, ?string $reports_key, ?string $currency_code): void
    {
        $this->merchant_id = $merchant_id ?: 0;
        $this->crc_key = $crc_key ?: null;
        $this->reports_key = $reports_key ?: null;
        $this->currency_code = $currency_code ?: null;
    }

    public function clear_config(): void
    {
        $this->merchant_id = 0;
        $this->crc_key = null;
        $this->reports_key = null;
        $this->currency_code = null;
    }
}
