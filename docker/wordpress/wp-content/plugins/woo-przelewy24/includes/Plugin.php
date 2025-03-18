<?php

namespace WC_P24;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Admin\Order_Utilities;
use WC_P24\Compatibility\Back_Compatibility_Manager;
use WC_P24\Compatibility\Compatibility_Checker;
use WC_P24\Encryption\Settings as EncryptionSettings;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\General\Settings;
use WC_P24\Installments\Installments;
use WC_P24\Migrations\Migration_Manager;
use WC_P24\Multicurrency\Multicurrency;
use WC_P24\OneClick\One_Clicks;
use WC_P24\Subscriptions\Subscriptions;
use WC_P24\Wizard\Wizard;

class Plugin
{

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('activate_' . WC_P24_PLUGIN_BASENAME, [$this, 'on_activate']);
        add_action('activated_plugin', [$this, 'after_activate']);
        add_action('init', [$this, 'later_init']);
    }

    public function on_activate(): void
    {
        if (!Compatibility_Checker::check()) return;

        new Migration_Manager();
        EncryptionSettings::generate_keys_on_active();
    }

    public function after_activate($plugin)
    {
        if ($plugin == WC_P24_PLUGIN_BASENAME) {
            if (Back_Compatibility_Manager::old_version_installed() && !Back_Compatibility_Manager::already_migrated()) {
                wp_safe_redirect(Wizard::get_url());
                exit;
            }
        }
    }

    public function later_init(): void
    {
        Compatibility_Checker::old_version_activated();
    }

    public function init(): void
    {
        if (!Compatibility_Checker::check()) return;

        load_plugin_textdomain('woocommerce-p24', false, WC_P24_PLUGIN_BASEDIR . '/languages/');

        new Wizard();

        new Compatibility\Back_Compatibility\Refund();

        new Updater();
        new Order_Utilities();

        new Core();
        new Settings();
        new EncryptionSettings();
        new Gateways_Manager();

        new Multicurrency();
        new Subscriptions();
        new One_Clicks();
        new Installments();

        new Cleaner();
        new Assets();
    }
}