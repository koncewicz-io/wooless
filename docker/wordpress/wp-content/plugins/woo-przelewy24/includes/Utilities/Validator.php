<?php

namespace WC_P24\Utilities;

if (!defined('ABSPATH')) {
    exit;
}

use WC_Admin_Settings;

class Validator
{
    private array $errors = [];

    public function validate_merchant_id(string $value): bool
    {
        $valid = false;

        if (preg_match('/^[0-9]+$/i', $value)) {
            $valid = true;
        } else {
            $this->add_error(__('Merchant ID is not valid', 'woocommerce-p24'));
        }

        return $valid;
    }

    public function validate_crc(string $value): bool
    {
        $valid = false;

        if (strlen($value) == 16 && preg_match('/^[a-f0-9]+$/i', $value)) {
            $valid = true;
        } else {
            $this->add_error(__('CRC key is not valid', 'woocommerce-p24'));
        }

        return $valid;
    }

    public function validate_reports_key(string $value): bool
    {
        $valid = false;

        if (preg_match('/^[a-f0-9]+$/i', $value)) {
            $valid = true;
        } else {
            $this->add_error(__('API key is not valid', 'woocommerce-p24'));
        }

        return $valid;
    }

    public function validate_currency(string $value): bool
    {
        $valid = false;

        if (preg_match('/^[a-z]{3}$/i', strtolower($value))) {
            $valid = true;
        } else {
            $this->add_error(__('Currency is not valid', 'woocommerce-p24'));
        }

        return $valid;
    }

    public function validate_base64(string $value): bool
    {
        $valid = false;

        if (preg_match('/^[A-Za-z0-9+\/]+={0,3}$/i',  $value)) {
            $valid = true;
        } else {
            $this->add_error(__('Token is not valid base64 string', 'woocommerce-p24'));
        }

        return $valid;
    }

    public function custom_validate($value, callable $callback, string $error_message): bool
    {
        $valid = false;

        if ($callback($value)) {
            $valid = true;
        } else {
            $this->add_error($error_message);
        }

        return $valid;
    }

    public function add_error(string $error): void
    {
        $this->errors[] = $error;
    }

    public function has_errors(): bool
    {
        return !!sizeof($this->errors);
    }

    public function get_errors(): array
    {
        return $this->errors;
    }

    public function get_first_error(): ?string
    {
        if ($this->has_errors()) {
            return $this->errors[0];
        }

        return null;
    }

    public function display_errors(): void
    {
        if (!$this->has_errors()) {
            return;
        }

        foreach ($this->get_errors() as $error) {
            WC_Admin_Settings::add_error(esc_html($error));
        }
    }
}
