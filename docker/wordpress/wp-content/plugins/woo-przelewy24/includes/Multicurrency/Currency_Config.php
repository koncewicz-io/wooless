<?php

namespace WC_P24\Multicurrency;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Models\Configuration;

class Currency_Config extends Configuration
{
    private float $multiplier = 1;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_multiplier(float $multiplier)
    {
        $this->multiplier = $multiplier;
    }

    public function get_currency_name(): string
    {
        $currencies = get_woocommerce_currencies();
        $currency = $currencies[$this->get_currency_code()] ?? '';

        return $currency;
    }

    public function get_currency_code(): string
    {
        return strtoupper($this->currency_code);
    }

    public function get_currency_symbol(): string
    {
        return get_woocommerce_currency_symbol($this->get_currency_code());
    }

    public function get_multiplier(): float
    {
        return $this->multiplier;
    }

    public function to_json(): string
    {
        return json_encode([
            'currency_code' => $this->get_currency_code(),
            'merchant_id' => $this->merchant_id,
            'crc_key' => $this->crc_key,
            'reports_key' => $this->reports_key,
            'multiplier' => $this->get_multiplier()
        ]);
    }
}
