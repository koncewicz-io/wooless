<?php

namespace WC_P24\Compatibility;

use WC_P24\Utilities\Notice;

if (!defined('ABSPATH')) {
    exit;
}

class Compatibility_Checker
{
    public static function check(): bool
    {
        $php_version = self::check_php_version('7.4.0');
        $woocommerce_version = self::check_wc_version('8.0.0');

        return $php_version && $woocommerce_version;
    }

    public static function old_version_activated(): void
    {
        $is_old_enabled = is_plugin_active('przelewy24/woocommerce-gateway-przelewy24.php');

        if ($is_old_enabled) {
            $plugins_url = self::get_filtered_plugins_url();
            $old_plugin = self::get_old_plugin_info();
            /* translators: 1: Old plugin name, 2: URL to the plugins page */
            new Notice(sprintf(__('The old version of the plug-in (<strong>%1$s</strong>) is running; for the new one to work properly, it must be deactivated. <a href="%2$s">Go to plugins</a>', 'woocommerce-p24'), $old_plugin['Name'], $plugins_url), Notice::ERROR, false);
        }
    }

    public static function get_old_plugin_info(): array
    {
        return get_plugin_data(WP_PLUGIN_DIR . '/przelewy24/woocommerce-gateway-przelewy24.php') ?: [];
    }

    public static function get_filtered_plugins_url(): string
    {
        return add_query_arg([
            's' => 'przelewy24',
            'plugin_status' => 'all'
        ], admin_url('plugins.php'));
    }

    private static function check_php_version(string $min_version): bool
    {
        $is_valid = version_compare(PHP_VERSION, $min_version, '>=');

        if (!$is_valid) {
            /* translators: 1: Required PHP version, 2: Current PHP version */
            new Notice(sprintf(__('At least PHP version %1$s is required. Current version: %2$s.', 'woocommerce-p24'), $min_version, PHP_VERSION), Notice::ERROR);
        }

        return $is_valid;
    }

    private static function check_wc_version(string $min_version): bool
    {
        $is_valid = defined('WC_VERSION') && version_compare(WC_VERSION, $min_version, '>=');

        if (!$is_valid) {
            /* translators: 1: Required WooCommerce version, 2: Current WooCommerce version */
            new Notice(sprintf(__('At least Woocommerce version %1$s is required. Current version: %2$s.', 'woocommerce-p24'), $min_version, WC_VERSION), Notice::ERROR);
        }

        return $is_valid;
    }
}


