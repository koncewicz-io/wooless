<?php
/**
 * Plugin Name: Przelewy24 Payment Gateway
 * Plugin URI: https://www.przelewy24.pl/pobierz
 * Description: Przelewy24 Payment gateway for WooCommerce.
 * Version: 1.0.2
 * Author: PayPro S.A.
 * Author URI: https://www.przelewy24.pl/
 * Text Domain: woocommerce-p24
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WC_P24_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_P24_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WC_P24_PLUGIN_BASEDIR', basename(__DIR__));
define('WC_P24_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WC_P24_PLUGIN_BASEFILE', __FILE__);
define('WC_P24_PLUGIN_TEMPLATES', __DIR__ . '/templates');
define('WC_P24_WIDGET_NAMESPACE', 'p24-blocks');
define('WC_P24_SETTINGS_NAME', 'p24_settings');
define('WC_P24_UPDATE_URL', 'https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/P24_WooCommerce_9.ini');
define('WC_P24_DOCS_URL', 'https://developers.przelewy24.pl/modules/index.php?pl#tag/Woocommerce-nowa-wersja-od-2025');

require_once WC_P24_PLUGIN_PATH . '/includes/autoload.php';

new WC_P24\Plugin();
