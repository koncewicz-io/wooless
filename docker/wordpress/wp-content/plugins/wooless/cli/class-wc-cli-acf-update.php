<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_CLI_ACF_Update {

    /**
     * Updates multiple ACF fields for a given post using a JSON string.
     *
     * ## EXAMPLES
     *     wp wooless acf-update --id=13 --data='{"custom_field": "123"}'
     *
     * @param array $args Arguments
     * @param array $assoc_args Additional options
     */
    public static function update_acf_fields($args, $assoc_args) {
        if (!isset($assoc_args['id']) || !isset($assoc_args['data'])) {
            WP_CLI::error("Usage: wp wooless acf-update --id=<id> --data='<json_string>'");
        }

        $id = $assoc_args['id'];
        $json_string = $assoc_args['data'];

        $fields = json_decode($json_string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            WP_CLI::error("Invalid JSON format: " . json_last_error_msg());
        }

        foreach ($fields as $field_name => $field_value) {
            update_field($field_name, $field_value, $id);
        }
    }
}
