<?php

namespace WC_P24;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Admin\Order_Utilities;
use WC_P24\Compatibility\Back_Compatibility_Manager;
use WC_P24\Compatibility\Compatibility_Checker;
use WC_P24\Encryption\Settings as Encryption_Settings;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\General\Settings;
use WC_P24\Installments\Installments;
use WC_P24\Installments\Settings as Installments_Settings;
use WC_P24\Migrations\Migration_Manager;
use WC_P24\Multicurrency\Multicurrency;
use WC_P24\OneClick\One_Clicks;
use WC_P24\Subscriptions\Subscriptions;
use WC_P24\Wizard\Wizard;
use WC_P24\Hooks\Thankyou_Status_Check;
use WC_P24\AJAX\Check_Payment_Status;
use WC_P24\Emails\SendMailToAdminOnNewOrder;
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

        (new Installments_Settings(true))->set_defaults();

        new Migration_Manager();
        Encryption_Settings::generate_keys_on_active();
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

    public function after_update(): void
    {
        $version = Core::check_version();

        if ($version) {
            (new Installments_Settings(true))->set_defaults();

            update_option(Core::INSTALLED_VERSION, $version, true);
        }

    }

    public function init(): void
    {
        if (!is_textdomain_loaded('woocommerce-p24')) {

            load_plugin_textdomain('woocommerce-p24', false, dirname(WC_P24_PLUGIN_BASENAME) . '/languages');
        }
        $this->after_update();

        if (!Compatibility_Checker::check()) return;


        new Wizard();

        new Compatibility\Back_Compatibility\Refund();

        new Updater();
        new Order_Utilities();

        new Core();
        new Settings();
        new Encryption_Settings();
        new Gateways_Manager();

        new Multicurrency();
        new Subscriptions();
        new One_Clicks();
        new Installments();

        new Cleaner();
        new Assets();
        new Back_Office();

        new Thankyou_Status_Check();
        new Check_Payment_Status;

        new SendMailToAdminOnNewOrder();

    }
}
