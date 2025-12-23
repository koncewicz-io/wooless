<?php

namespace WC_P24\Gateways;

if (!defined('ABSPATH')) {
    exit;
}

trait Settings_Helper
{
    public function enable_and_set_defaults(): void
    {
        $id = $this->get_option_key();

        if (get_option($id) === false) {
            do_action('woocommerce_update_option', ['id' => $id]);
            $this->update_option('enabled', 'yes');

            foreach ($this->form_fields as $key => $field) {
                if (isset($field['default'])) {
                    $this->update_option($key, $field['default']);
                }
            }

            do_action('woocommerce_update_options');
        }
    }

    public function fee_settings(): array
    {
        return [
            'separator' => [
                'type' => 'title',
                'title' => '<hr />'
            ],
            'fee_enabled' => [
                'type' => 'checkbox',
                'title' => __('Fee', 'woocommerce-p24'),
                'label' => __('Enable gateway fee', 'woocommerce-p24'),
                'default' => 'no',
            ],
            'fee_name' => [
                'type' => 'text',
                'title' => __('Name of fee', 'woocommerce-p24'),
                'default' => __('Przelewy24 service', 'woocommerce-p24'),
            ],
            'fee_value' => [
                'type' => 'number',
                'title' => __('Value of fee', 'woocommerce-p24'),
                'description' => __('Additional cost added to the order at checkout', 'woocommerce-p24'),
            ]
        ];
    }
}
