<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_ACF_Importer {

    /**
     * Imports ACF field groups from a JSON file.
     *
     * @param string $file_path Path to the JSON file.
     * @return void
     */
    public static function import_from_json($file_path) {
        if (!file_exists($file_path)) {
            WP_CLI::error("JSON file not found: $file_path");
            return;
        }

        $json_data = file_get_contents($file_path);
        $groups = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            WP_CLI::error("JSON parsing error: " . json_last_error_msg());
            return;
        }

        if (!function_exists('acf_import_field_group')) {
            WP_CLI::error("ACF is not active or the function acf_import_field_group() is not available.");
            return;
        }

        foreach ($groups as $group) {
            if (!is_array($group) || empty($group['key']) || empty($group['title'])) {
                WP_CLI::warning("Invalid ACF group data. Skipping one of the groups.");
                continue;
            }

            acf_import_field_group($group);
        }
    }
}
