<?php

namespace WC_P24\Encryption;

use WC_P24\Core;
use WC_P24\Utilities\Ajax;
use WC_P24\Utilities\Encryption;
use WC_P24\Utilities\Module_Settings;

class Settings extends Module_Settings
{

    public function __construct()
    {
        parent::__construct();

        Ajax::add_action('generate_keys', [$this, 'generate_keys']);
    }

    public function get_handle(): string
    {
        return 'encryption';
    }

    public function get_label(): string
    {
        return __('Encryption', 'woocommerce-p24');
    }

    public static function generate_keys_on_active(): void
    {
        $key = get_option('p24_encryption_key');
        $iv = get_option('p24_iv');

        if (empty($iv) || empty($key)) {
            update_option('p24_iv', Encryption::generate_iv());
            update_option('p24_encryption_key', Encryption::generate_key());
        }
    }

    protected function settings(): array
    {
        return [
            [
                'type' => 'title',
                'title' => __('Encryption settings', 'woocommerce-p24'),
                'desc' => __('Settings for encrypt order IDs', 'woocommerce-p24'),
            ],
            [
                'id' => 'generate_keys_button',
                'type' => 'button',
                'label' => __('Generate Keys', 'woocommerce-p24'),
                'after' => '<span id="key_generation_status"></span>'
            ],
            [
                'id' => 'p24_encryption_key',
                'title' => __('Encryption Key', 'woocommerce-p24'),
                'type' => 'password_toggle',
                'custom_attributes' => ['required' => true, 'pattern' => '^[a-f0-9]{16}$', 'readonly' => true],
                'placeholder' => __('16 characters', 'woocommerce-p24'),
                'desc' => __('The key used for encrypting.', 'woocommerce-p24'),
            ],
            [
                'id' => 'p24_iv',
                'title' => __('Initialization Vector (IV)', 'woocommerce-p24'),
                'type' => 'password_toggle',
                'custom_attributes' => ['required' => true, 'pattern' => '^[a-f0-9]{32}$', 'readonly' => true],
                'placeholder' => __('32 characters', 'woocommerce-p24'),
                'desc' => __('The initialization vector used with the encryption key.', 'woocommerce-p24'),
            ],
            [
                'type' => 'sectionend',
            ]
        ];
    }

    public function generate_keys(): void
    {
        wp_send_json_success([
            'encryption_key' => Encryption::generate_key(),
            'iv' => Encryption::generate_iv()
        ]);
    }
}
