<?php

namespace WC_P24;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Models\Configuration;

class Config
{
    private static Config $instance;
    private Configuration $default;
    private Configuration $current;
    private bool $is_live;

    public function __construct()
    {
        $this->default = new Configuration(true);
        $this->current = new Configuration();

        $this->is_live = get_option('p24_mode', 'sandbox') === 'production';
    }

    public static function get_instance(): Config
    {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    public function set_live(bool $value): void
    {
        $this->is_live = $value;
    }

    public function is_live(): bool
    {
        return $this->is_live;
    }

    public function get_merchant_id(): ?int
    {
        return $this->current->merchant_id ?: $this->default->merchant_id;
    }

    public function get_crc_key(): ?string
    {
        return $this->current->crc_key ?: $this->default->crc_key;
    }

    public function get_reports_key(): ?string
    {
        return $this->current->reports_key ?: $this->default->reports_key;
    }

    public function get_currency(): ?string
    {
        return strtoupper($this->current->currency_code ?: $this->default->currency_code);
    }

    public function get_base_currency(): string
    {
        return strtoupper($this->default->currency_code);
    }

    public function get_mode_prefix(): string
    {
        return $this->is_live() ? 'secure' : 'sandbox';
    }

    public function set_config(Configuration $config): void
    {
        $this->current->set_config($config->merchant_id, $config->crc_key, $config->reports_key, $config->currency_code);
    }

    public function clear_config(): void
    {
        $this->current = new Configuration();
        $this->is_live = get_option('p24_mode', 'sandbox');
    }
}
