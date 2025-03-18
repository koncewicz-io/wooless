<?php

namespace WC_P24;

if (!defined('ABSPATH')) {
    exit;
}

class Assets
{
    static array $scripts = [];
    static array $localize_scripts = [];

    static array $styles = [];

    public function __construct()
    {
        self::add_script_asset('p24-admin-general', 'admin-general.bundle.min.js', true);
        self::add_script_asset('p24-block-checkout', 'block-checkout.js', false, false, false, ['react', 'wp-html-entities']);
        self::add_style_asset('p24-admin-styles', 'admin-styles.css', true);
        self::add_script_asset('p24-online-payments', 'online-payments.bundle.js');
        self::add_style_asset('p24-styles', 'styles.css');

        add_action('admin_enqueue_scripts', function () {
            $this->enqueue(true);
        });

        add_action('wp_enqueue_scripts', function () {
            $this->enqueue();
        });
    }

    public function enqueue(bool $for_admin = false)
    {
        foreach (self::$scripts as $script) {
            [$handle, $file, $admin, $callback, $external, $depedencies] = $script;

            $load = !is_callable($callback) || $callback();

            if ($for_admin === $admin && $load) {
                $url = $external ? $file : WC_P24_PLUGIN_URL . 'assets/js/' . $file;
                wp_enqueue_script($handle, $url, $depedencies, Core::$version, true);
            }
        }

        foreach (self::$localize_scripts as $script) {
            [$handle, $name, $data, $admin] = $script;

            if ($for_admin === $admin) {
                wp_localize_script($handle, $name, $data);
            }
        }

        foreach (self::$styles as $style) {
            [$handle, $file, $admin, $callback] = $style;

            $load = !is_callable($callback) || $callback();

            if ($for_admin === $admin && $load) {
                wp_enqueue_style($handle, WC_P24_PLUGIN_URL . 'assets/css/' . $file, [], Core::$version);
            }
        }
    }

    static function add_script_asset(string $handle, string $src, bool $admin = false, $callback = false, $external = false, $depedencies = [])
    {
        self::$scripts[] = [$handle, $src, $admin, $callback, $external, $depedencies];
    }

    static function add_script_localize(string $handle, string $name, array $data, bool $admin = false)
    {
        self::$localize_scripts[] = [$handle, $name, $data, $admin];
    }

    static function add_style_asset(string $handle, string $src, bool $admin = false, $callback = false)
    {
        self::$styles[] = [$handle, $src, $admin, $callback];
    }
}
