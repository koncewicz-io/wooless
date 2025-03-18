<?php

namespace WC_P24\Gateways\Online_Payments;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Core;
use WC_P24\Gateways\Fee;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Gateways\Payment_Helpers;
use WC_P24\Gateways\Settings_Helper;
use WC_P24\Utilities\Base_Gateway_Block;
use WC_P24\Utilities\Payment_Methods;
use WC_Payment_Gateway;

class Gateway extends WC_Payment_Gateway
{
    use Payment_Helpers;
    use Settings_Helper;
    use Online_Payments_Legacy_Support;

    public function __construct()
    {
        $this->id = Core::MAIN_METHOD;
        $this->icon = apply_filters('woocommerce_gateway_icon', WC_P24_PLUGIN_URL . '/assets/logo-small.png');

        $this->method_title = __('Przelewy24 - online payments', 'woocommerce-p24');
        $this->method_description = sprintf(__('Payment option directing the user to a payment gateway <br /><a href="%s">General configuration</a>', 'woocommerce-p24'), Core::get_settings_url());

        $this->has_fields = $this->get_option('show_available_methods') == 'yes';
        $this->title = $this->get_option('title') ?: __('Pay with Przelewy24', 'woocommerce-p24');
        $this->description = $this->get_option('description');
        $this->supports = ['products', 'refunds'];

        $this->init_form_fields();

        new Fee($this);

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_after_checkout_validation', [$this, 'legacy_checkout_validation'], 10, 2);
    }

    public function block_support(): Base_Gateway_Block
    {
        return new Online_Payments_Block($this);
    }

    public function init_form_fields()
    {
        $this->form_fields = array_merge([
            'enabled' => [
                'type' => 'checkbox',
                'label' => __('Enable Przelewy24 - online payments', 'woocommerce-p24'),
                'custom_attributes' => ['data-enabled' => true]
            ],
            'title' => [
                'type' => 'text',
                'title' => __('Payment title', 'woocommerce-p24'),
                'description' => __('Name of payment visible at checkout', 'woocommerce-p24'),
                'default' => __('Pay with Przelewy24', 'woocommerce-p24'),
            ],
            'description' => [
                'type' => 'textarea',
                'title' => __('Description', 'woocommerce-p24'),
                'description' => __('Description of payment which the user sees during checkout', 'woocommerce-p24'),
                'default' => __('After selecting this payment, you will be redirected to the Przelewy24 payments', 'woocommerce-p24')
            ],
            'show_short_info' => [
                'type' => 'checkbox',
                'label' => __('Show two popular payment methods and count of additional', 'woocommerce-p24'),
                'default' => 'no'
            ]],
            $this->fee_settings(),
            [
                'show_available_methods' => [
                    'type' => 'checkbox',
                    'title' => __('Show available methods', 'woocommerce-p24'),
                    'label' => __('Show available methods in checkout under this payment option', 'woocommerce-p24'),
                    'description' => __('The customer will see a list of available methods (like bank transfer providers) to choose from before proceeding to the payment gateway.', 'woocommerce-p24'),
                    'default' => 'no'
                ],
                'methods' => [
                    'type' => 'methods',
                    'description' => __('Sort and select the featured. Featured methods will automatically be shown at the beginning', 'woocommerce-p24'),
                    'default' => 'no'
                ],
                'feature_as_gateway' => [
                    'type' => 'checkbox',
                    'title' => __('Highlighted Payment Methods', 'woocommerce-p24'),
                    'label' => __('Highlight Payment Methods in Checkout', 'woocommerce-p24'),
                    'description' => __('This option allows you to highlight selected payment methods from the list of available options (e.g., BLIK, credit cards). You can also manage the display order of the highlighted methods.<br /> When a payer selects a highlighted payment method, they will be redirected directly to the provider of that payment method.', 'woocommerce-p24'),
                    'default' => 'no'
                ],
                'gateway_methods' => [
                    'type' => 'methods',
                    'label' => __('Featured methods as gateway in checkout', 'woocommerce-p24'),
                    'description' => __('Select the feature methods', 'woocommerce-p24'),
                    'default' => 'no'
                ],
            ]
        );
    }

    public function get_featured_methods(): array
    {
        $featured = [];

        if ($this->is_enabled()) {
            $feature_payments = $this->get_option('feature_as_gateway') === 'yes';
            $featured_methods_order = $this->get_option('gateway_methods');

            if ($feature_payments) {
                $methods = Gateways_Manager::get_available_methods();
                $featured = Payment_Methods::prepare_methods($methods, $featured_methods_order);
            }
        }

        return $featured;
    }

    public function get_receipt_config(): array
    {
        $fetch_methods = is_checkout();

        $options = json_decode($this->get_option('styles'));
        $show_short_info = $this->get_option('show_short_info') === 'yes';
        $show_methods = $this->get_option('show_available_methods') === 'yes';
        $feature_payments = $this->get_option('feature_as_gateway') === 'yes';

        $methods_order = $this->get_option('methods');
        $featured_methods_order = $this->get_option('gateway_methods');

        $settings['options'] = $options ?: [];

        $methods = [];
        $featured = [];
        $additional = 0;
        $methods_icons = [];

        if ($show_methods || $show_short_info || $feature_payments) {
            $methods = $fetch_methods ? Gateways_Manager::get_available_methods() : [];
            $methods = Payment_Methods::prepare_methods($methods, $methods_order, true);
            $featured = Payment_Methods::prepare_methods($methods, $featured_methods_order, true);
        }

        if ($show_short_info) {
            $methods_icons = Payment_Methods::get_popular_methods_icons($methods);
            $additional = apply_filters('przelewy24_available_method_additional', count($methods) - count($methods_icons), $methods_icons, $methods);
            $methods_icons = apply_filters('przelewy24_available_method_icons', $methods_icons, $methods);
        }

        return [
            'config' => $settings,
            'method' => $this->method,
            'methods' => [
                'show' => $show_methods,
                'items' => $methods,
                'featured' => array_filter($methods, function ($method) {
                    return $method['featured'];
                }),
                'rest' => array_filter($methods, function ($method) {
                    return !$method['featured'];
                })
            ],
            'featured' => array_values(array_filter($featured, function ($method) {
                return $method['featured'];
            })),
            'info' => [
                'show' => $show_short_info,
                'additional' => $additional,
                'icons' => $methods_icons,
            ],
            'i18n' => [
                'error' => [
                    'rules' => __('You have to accept the terms and conditions for using the chosen method', 'woocommerce-p24'),
                ],
                'label' => [
                    'submit' => __('Pay with Przelewy24', 'woocommerce-p24'),
                    'regulation' => sprintf(__('I hereby state that I have read the <a href="%s" target="_blank">regulations</a> and <a href="%s" target="_blank">information obligation</a> of "Przelewy24"', 'woocommerce-p24'), Core::get_rules_url(), Core::get_tos_url()),
                ]
            ]
        ];
    }
}
