<?php

namespace WC_P24\Wizard;

use WC_P24\Assets;
use WC_P24\Compatibility\Back_Compatibility_Manager;
use WC_P24\Compatibility\Compatibility_Checker;
use WC_P24\Core;
use WC_P24\Render;
use WC_P24\Utilities\Logger;

class Wizard
{
    const WIZARD_PAGE_ID = 'przelewy24-wizard';

    public function __construct()
    {
        add_filter('plugin_action_links_' . WC_P24_PLUGIN_BASENAME, [$this, 'add_wizard_link']);
        add_action('admin_menu', [$this, 'add_wizard_page']);

        Assets::add_style_asset('p24-wizard', 'bootstrap.min.css', true, function () {
            $page = $_GET['page'] ?? null;
            return $page === self::WIZARD_PAGE_ID;
        });
    }

    public function add_wizard_link($links): array
    {
        if (Back_Compatibility_Manager::old_version_installed()) {
            $links[] = '<a href="' . Wizard::get_url() . '" target="_blank"> ' . __('Wizard', 'woocommerce-p24') . ' </a>';
        }

        return $links;
    }

    public static function get_url($submit = null)
    {
        return add_query_arg([
            'page' => self::WIZARD_PAGE_ID,
            'action' => $submit
        ], admin_url('admin.php'));
    }

    public function add_wizard_page()
    {
        add_submenu_page(null, __('Przelewy24 wizard', 'woocommerce-p24'), '', 'manage_options', self::WIZARD_PAGE_ID, [$this, 'render']);
    }

    public function render()
    {
        $manager = new Back_Compatibility_Manager();

        if (Back_Compatibility_Manager::already_migrated()) {
            wp_safe_redirect(admin_url());
            exit;
        }

        $data = [
            'skip' => admin_url(),
            'go_to_settings' => Core::get_settings_url(),
            'go_to_plugins' => Compatibility_Checker::get_filtered_plugins_url(),
            'old_plugin' => Compatibility_Checker::get_old_plugin_info(),
            'logo_url' => WC_P24_PLUGIN_URL . '/assets/logo-full.svg',
            'old_exist' => Back_Compatibility_Manager::old_version_installed(),
            'action' => self::get_url('submit'),
            'settings' => $manager->settings->get_settings(),
            'multicurrency' => $manager->multicurrency->get_settings(),
            'references' => $manager->references->get_settings(false),
            'subscriptions' => $manager->subscriptions->get_settings(false),
            'import' => false,
            'import_message' => null
        ];

        if ($_GET['action'] === 'submit') {
            try {
                $migrate = $_POST['migrate'] ?? 'no';

                if ($migrate == 'no') {
                    wp_safe_redirect(admin_url());
                    exit;
                }

                $subscriptions = (int)($_POST['subscriptions'] ?? 0);
                $multicurrency = (int)($_POST['multicurrency'] ?? 0);
                $references = (int)($_POST['references'] ?? 0);

                $settings = (object)[
                    'merchant_id' => $_POST['p24_merchant_id'] ?? null,
                    'crc_key' => $_POST['p24_crc_key'] ?? null,
                    'reports_key' => $_POST['p24_reports_key'] ?? null,
                    'mode' => $_POST['p24_mode'] ?? null,
                ];

                if (!empty($settings)) $manager->settings->save_settings($settings);
                if ($multicurrency) $manager->multicurrency->import();
                if ($references) $manager->references->import();
                if ($subscriptions) $manager->subscriptions->import();

                Back_Compatibility_Manager::complete_import();
                $data['import'] = 'success';
            } catch (\Exception $exception) {
                Logger::log($exception->getMessage(), Logger::DEBUG);
                $data['import'] = 'warning';
                $data['import_message'] = $exception->getMessage();
            }
        }

        Render::template('wizard/wizard', $data);
    }
}
