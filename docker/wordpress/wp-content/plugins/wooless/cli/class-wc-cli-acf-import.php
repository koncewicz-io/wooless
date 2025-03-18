<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_CLI_ACF_Import {

    /**
     * Imports ACF fields from a JSON file.
     *
     * ## EXAMPLE
     *     wp wooless acf-import /path/to/file.json
     *
     * @param array $args Arguments passed to WP-CLI.
     * @param array $assoc_args Additional options.
     */
    public static function import_acf_fields($args, $assoc_args) {
        if (empty($args[0])) {
            WP_CLI::error("You must provide the path to the JSON file.");
        }

        $file_path = $args[0];

        require_once plugin_dir_path(__FILE__) . '../includes/class-wc-acf-importer.php';

        WC_ACF_Importer::import_from_json($file_path);
    }
}
