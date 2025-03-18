<?php

namespace WC_P24\Compatibility;

use WC_P24\Compatibility\Back_Compatibility\Card_References;
use WC_P24\Compatibility\Back_Compatibility\Multicurrency;
use WC_P24\Compatibility\Back_Compatibility\Settings;
use WC_P24\Compatibility\Back_Compatibility\Subscriptions;

class Back_Compatibility_Manager
{

    public Settings $settings;
    public Card_References $references;
    public Subscriptions $subscriptions;
    public Multicurrency $multicurrency;

    public function __construct()
    {
        $this->settings = new Settings();
        $this->multicurrency = new Multicurrency();
        $this->subscriptions = new Subscriptions();
        $this->references = new Card_References();
    }

    public static function old_version_installed(): bool
    {
        return file_exists(WP_PLUGIN_DIR . '/przelewy24/woocommerce-gateway-przelewy24.php');
    }

    public static function already_migrated(): bool
    {
        return get_option('p24_data_imported', false);
    }

    public static function complete_import(): void
    {
        update_option('p24_data_imported', '1', true);
    }
}
