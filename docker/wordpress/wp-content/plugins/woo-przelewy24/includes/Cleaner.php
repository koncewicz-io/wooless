<?php

namespace WC_P24;

use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Multicurrency\Multicurrency;
use WC_P24\UI\Display;
use WC_P24\Utilities\Notice;

class Cleaner
{
    const CLEANER_PAGE_ID = 'przelewy24-cleaner';
    const CLEAR_NONCE_KEY = 'p24_clear_nonce';
    const CLEAR_NONCE_ID = 'p24_clear';
    const CLEAR_ACCEPT_KEY = 'p24_clear_accept';

    public function __construct()
    {
        add_filter('plugin_action_links_' . WC_P24_PLUGIN_BASENAME, [$this, 'add_cleaner_link']);
        add_action('admin_menu', [$this, 'add_cleaner_page']);
    }

    public static function get_url($submit = null): string
    {
        return add_query_arg([
            'page' => self::CLEANER_PAGE_ID,
            'action' => $submit
        ], admin_url('admin.php'));
    }

    public function add_cleaner_link($links): array
    {
        $link = '<a href="' . self::get_url() . '">' . __('Clear plugin data', 'woocommerce-p24') . '</a>';
        $links[] = $link;
        return $links;
    }

    public function add_cleaner_page()
    {
        add_submenu_page(null, __('Przelewy24 cleaner', 'woocommerce-p24'), '', 'manage_options', self::CLEANER_PAGE_ID, [$this, 'render']);
    }

    protected function settings(): array
    {
        return [
            [
                'id' => self::CLEAR_NONCE_KEY,
                'type' => 'hidden',
                'name' => self::CLEAR_NONCE_KEY,
                'default' => wp_create_nonce(self::CLEAR_NONCE_ID)
            ],
            [
                'id' => self::CLEAR_ACCEPT_KEY,
                'type' => 'checkbox',
                'name' => self::CLEAR_ACCEPT_KEY,
                'title' => __('Clear data', 'woocommerce-p24'),
                'label' => __('Clear all data of the plugin', 'woocommerce-p24'),
                /* translators: %s refers to the plugin name */
                'desc' => sprintf(__('By confirming, all configuration data for the <strong>%s</strong> plugin will be erased', 'woocommerce-p24'), Core::get_plugin_name()),
                'required' => true
            ]
        ];
    }

    public function render()
    {
        if (isset($_POST[self::CLEAR_NONCE_KEY])) {
            $nonce = sanitize_key($_POST[self::CLEAR_NONCE_KEY]);

            if (wp_verify_nonce($nonce, self::CLEAR_NONCE_ID)) {
                $this->clean();
                Render::template('admin/notice', [
                    'message' => __('Data was succesfully deleted', 'woocommerce-p24'),
                    'type' => Notice::SUCCESS
                ]);
            } else {
                Render::template('admin/notice', [
                    'message' => __('Failure, incorrect nonce', 'woocommerce-p24'),
                    'type' => Notice::ERROR
                ]);
            }
        }

        $display = new Display(Display::CUSTOM_FORM);
        $display->set_fields($this->settings());

        Render::template('admin/cleaner', [
            'fields' => $display->fields,
        ]);
    }

    public function clean()
    {
        $this->clean_general();
        $this->clean_encryption();
        $this->clean_installments();
        $this->clean_multicurrency();
        $this->clean_subscriptions();

        $this->clean_gateways();

        $this->remove_settings(['p24_data_imported', Core::INSTALLED_VERSION]);
    }

    public function clean_general(): void
    {
        $settings = new \WC_P24\General\Settings();
        $this->remove_settings($settings->get_settings(true));
    }

    public function clean_encryption(): void
    {
        $settings = new \WC_P24\Encryption\Settings();
        $this->remove_settings($settings->get_settings(true));
    }

    public function clean_installments(): void
    {
        $settings = new \WC_P24\Installments\Settings();
        $this->remove_settings($settings->get_settings(true));
    }

    public function clean_multicurrency(): void
    {
        $settings = new \WC_P24\Multicurrency\Settings();
        $this->remove_settings($settings->get_settings(true));

        $currencies = ['p24_currencies'];

        foreach (Multicurrency::get_available_currencies() as $currency) {
            $currencies[] = 'p24_currency_' . $currency->currency_code;
        };

        $this->remove_settings($currencies);
    }

    public function clean_subscriptions(): void
    {
        $settings = new \WC_P24\Subscriptions\Settings();
        $this->remove_settings($settings->get_settings(true));
    }

    public function clean_gateways(): void
    {
        $gateways = Gateways_Manager::$gateways;

        $gateways_ids = [];
        foreach ($gateways as $gateway) {
            $gateways_ids[] = $gateway->plugin_id . $gateway->id . '_settings';
        }

        $this->remove_settings($gateways_ids);
    }

    public function remove_settings(array $settings): void
    {
        foreach ($settings as $setting) {
            delete_option($setting);
        }
    }
}
