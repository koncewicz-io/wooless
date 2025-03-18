<?php

namespace WC_P24\UI;

use WC_P24\API\Resources\Payment_Methods_Resource;
use WC_P24\Back_Office;
use WC_P24\Render;
use WC_P24\Utilities\Payment_Methods;

class Display
{
    const SETTINGS_API = 0;
    const ADMIN_API = 1;
    const CUSTOM_FORM = 2;
    private int $scenario = self::SETTINGS_API;
    public array $fields = [];
    private Menu $menu;

    public function __construct($scenario = self::SETTINGS_API)
    {
        $this->scenario = $scenario;
        $GLOBALS['hide_save_button'] = true;

        $this->menu = new Menu();
    }

    public function set_fields($fields, $class = null)
    {
        if ($this->scenario == self::SETTINGS_API) {
            array_unshift($fields, [
                'type' => 'title',
                'title' => $class->title
            ]);
        }

        foreach ($fields as $key => $field) {
            $defaults = [
                'title' => '',
                'name' => '',
                'label' => '',
                'disabled' => false,
                'required' => false,
                'placeholder' => '',
                'type' => 'text',
                'desc_tip' => false,
                'description' => '',
                'custom_attributes' => [],
                'attributes_html' => '',
                'options' => [],
                'before' => '',
                'after' => '',
                'info' => ''
            ];

            $value = null;

            if ($this->scenario == self::SETTINGS_API) {
                $field['name'] = $class->plugin_id . $class->id . '_' . $key;
                $value = $class->get_option($key);
            } else if ($this->scenario == self::ADMIN_API) {
                $field['name'] = $field['id'] ?? '';
                $field['description'] = $field['desc'] ?? '';
                $value = \WC_Admin_Settings::get_option($field['name']);
            } else if ($this->scenario == self::CUSTOM_FORM) {
                $field['description'] = $field['desc'] ?? '';
            }


            $value = empty($value) ? ($field['default'] ?? '') : $value;

            $field['id'] = $field['id'] ?? $field['name'];

            $attributes = [];

            if (!empty($field['custom_attributes']) && is_array($field['custom_attributes'])) {
                foreach ($field['custom_attributes'] as $attribute => $attribute_value) {
                    if ($attribute == 'required') {
                        $field[$attribute] = $attribute_value;
                    }

                    $attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
                }
            }

            foreach (['placeholder', 'disabled', 'required', 'name', 'id'] as $attribute) {
                if (!empty($field[$attribute])) {
                    $attributes[] = esc_attr($attribute) . '="' . esc_attr($field[$attribute]) . '"';
                }
            }

            if ($field['type'] == 'checkbox') {
                $attributes['checked'] = in_array($value, [true, 1, 'yes'], true) ? 'checked' : '';

                if ($this->scenario == self::ADMIN_API) {
                    if (empty($field['label'])) {
                        $field['label'] = $field['description'];
                        $field['description'] = '';
                    }
                }
            }

            if ($field['type'] == 'methods') {
                $methods = Payment_Methods::get_payment_methods(1000, 'PLN');
                $field['methods'] = Payment_Methods::prepare_methods($methods, $value ?? [], true);
            }

            $field['attributes_html'] = implode(' ', $attributes);

            $data = wp_parse_args($field, $defaults);

            $data['value'] = $value;
            $this->fields[] = $data;
        }
    }

    public function render($before_form = '', $after_form = '')
    {
        $back_office = new Back_Office();

        Render::template('admin/template', [
            'logo_url' => WC_P24_PLUGIN_URL . '/assets/logo-full.svg',
            'icons' => array_map(function ($image) {
                return WC_P24_PLUGIN_URL . '/assets/svg/' . $image;
            }, ['blik.svg', 'mastercard.svg', 'paypo.svg', 'visa.svg', 'apple-pay.svg', 'google-pay.svg']),
            'banner' => $back_office->get_banner(),
            'fields' => $this->fields,
            'menu' => $this->menu->items,
            'before_form' => $before_form,
            'after_form' => $after_form
        ]);
    }
}
