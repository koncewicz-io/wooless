<?php
/**
 * Plugin Name: Wooless
 * Version: 1.0.0
 * Author: koncewicz.io
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JWT_AUTH_SECRET_KEY', getenv('JWT_AUTH_SECRET_KEY'));

add_filter(
    'jwt_auth_expire',
    function ( $expire, $issued_at ) {
        // Modify the "expire" here.
        return time() + (60 * 60 * 24 * 365);
    },
    10,
    2
);

require_once plugin_dir_path(__FILE__) . 'includes/class-wc-store-api-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-wc-product-acf-extension.php';

new WC_Store_API_Settings();

add_action('woocommerce_blocks_loaded', function () {
    $extend = Automattic\WooCommerce\StoreApi\StoreApi::container()->get(Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema::class);
    (new WC_Product_ACF_Extension())->init($extend);
});

if (defined('WP_CLI') && WP_CLI) {
    require_once plugin_dir_path(__FILE__) . 'cli/class-wc-cli-acf-import.php';
    require_once plugin_dir_path(__FILE__) . 'cli/class-wc-cli-acf-update.php';

    WP_CLI::add_command('wooless acf-import', ['WC_CLI_ACF_Import', 'import_acf_fields']);
    WP_CLI::add_command('wooless acf-update', ['WC_CLI_ACF_Update', 'update_acf_fields']);
}
