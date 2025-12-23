<?php

namespace WC_P24;

if (!defined('ABSPATH')) {
    exit;
}

class Core extends \WC_Settings_API
{
    const WEBHOOK_NAMESPACE = 'przelewy24';
    const INSTALLED_VERSION = 'p24-installed-version';
    const MAIN_METHOD = 'p24-online-payments';
    const BLIK_IN_SHOP_METHOD = 'p24-blik';
    const CARD_IN_SHOP_METHOD = 'p24-card';
    const GOOGLE_PAY_IN_SHOP_METHOD = 'p24-google-pay';
    const APPLE_PAY_IN_SHOP_METHOD = 'p24-apple-pay';

    public static string $version = '1.0.0';


    /**
     * Add link to settings on plugin listing
     * Add new tab to woocommerce options
     * Add submenu to settings
     */
    public function __construct()
    {
        add_filter('plugin_action_links_' . WC_P24_PLUGIN_BASENAME, [$this, 'add_settings_link']);
        add_filter('woocommerce_settings_tabs_array', [$this, 'add_settings_tab'], 50);

        add_filter('plugin_row_meta', [$this, 'add_link_to_docs'], 10, 2);
        $plugin_data = get_file_data(WC_P24_PLUGIN_BASEFILE, ['version' => 'Version']);

        if (!empty($plugin_data['version'])) {
            self::$version = $plugin_data['version'];
        }
    }

    public function add_link_to_docs($links, $file)
    {
        if (!empty($links[1])) {
            $links[1] = str_replace("\">", "\" target=\"_blank\">", $links[1]);
        }

        if ($file === WC_P24_PLUGIN_BASENAME && WC_P24_DOCS_URL) {
            $links[] = '<a href="' . WC_P24_DOCS_URL . '" target="_blank"> ' . __('Documentation', 'woocommerce-p24') . ' </a>';
        }

        return $links;
    }

    public static function get_rules_url(): string
    {
        return Helper::get_language() === 'pl' ? 'https://www.przelewy24.pl/obowiazek-informacyjny-rodo-platnicy' : 'https://www.przelewy24.pl/en/information-obligation-gdpr-payer';
    }

    public static function get_tos_url(): string
    {
        return Helper::get_language() === 'pl' ? 'https://www.przelewy24.pl/regulamin' : 'https://www.przelewy24.pl/en/regulations';
    }

    public static function get_plugin_name(): string
    {
        return get_plugin_data(WC_P24_PLUGIN_BASEFILE)['Name'] ?? '';
    }

    public static function check_version(): string
    {
        $saved_version = (string)get_option(self::INSTALLED_VERSION, '');

        return self::$version !== $saved_version ? self::$version : '';
    }

    public static function get_settings_url(): string
    {
        return add_query_arg([
            'page' => 'wc-settings',
            'tab' => WC_P24_SETTINGS_NAME
        ], admin_url('admin.php'));
    }

    public function add_settings_link($links)
    {
        $settings_link = '<a href="' . self::get_settings_url() . '">' . __('Settings', 'woocommerce-p24') . '</a>';

        array_unshift($links, $settings_link);

        return $links;
    }

    public function add_settings_tab($settings_tabs)
    {
        $settings_tabs[WC_P24_SETTINGS_NAME] = __('Przelewy24', 'woocommerce-p24');

        return $settings_tabs;
    }

    public function is_legacy_checkout(): bool
    {
        return \WC_Blocks_Utils::has_block_in_page(wc_get_page_id('checkout'), 'woocommerce/checkout');
    }
}
