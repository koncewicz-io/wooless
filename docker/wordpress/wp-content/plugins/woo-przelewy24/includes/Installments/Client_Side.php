<?php

namespace WC_P24\Installments;

use WC_P24\Assets;
use WC_P24\Config;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Helper;
use WC_P24\Utilities\Payment_Methods;

class Client_Side
{
    private bool $simulatorEnabled = false;

    public function __construct()
    {
        $widgetEnabled = Installments::show_widget_on_product();
        $this->simulatorEnabled = Installments::show_simulator();

        $widgetPosition = Installments::get_product_widget_position();
        if (empty($widgetPosition) || !is_string($widgetPosition)) {
            $widgetPosition = 'before';
        }

        if ($widgetEnabled) {
            $positionPriorities = [
                'after_summary_15' => 15,
                'after_summary_25' => 25,
            ];

            if (strpos($widgetPosition, 'after_summary') === 0) {
                $priority = isset($positionPriorities[$widgetPosition]) ? $positionPriorities[$widgetPosition] : 25;
                add_action('woocommerce_single_product_summary', [$this, 'add_widget_and_simulator_on_product'], $priority);
            } else {
                add_action('woocommerce_' . $widgetPosition . '_add_to_cart_form', [$this, 'add_widget_and_simulator_on_product']);
            }

            if (Installments::show_widget_on_checkout() && WC()->cart && WC()->cart->total >= Installments::get_min_product_price()) {
                add_action('woocommerce_review_order_before_payment', [$this, 'add_widget_and_simulator_on_checkout']);
            }

            Assets::add_script_localize('p24-installments', 'p24InstallmentsData', function () {
                $result = [];
                if (is_product()) {
                    $result = self::get_product_data();
                } else if (is_checkout()) {
                    $result = self::get_checkout_data();
                }
                return $result;
            });

            Assets::add_script_asset('p24-installments', 'installment.js', false);
        }
    }

    private function product_price_in_range($price)
    {
        $min = Installments::get_min_product_price();
        $max = Installments::get_max_product_price();

        if ($min === 0 && $max === 0) {
            return true;
        }

        if ($min !== 0 && $price < $min) {
            return false;
        }

        if ($max !== 0 && $price > $max) {
            return false;
        }

        return true;
    }

    public function is_product_and_in_range(): bool
    {
        if (!is_product()) {
            return false;
        }

        $product = wc_get_product(get_the_ID());
        if (!$product) {
            return false;
        }

        $price = (float) $product->get_price();
        return $this->product_price_in_range($price);
    }

    public function add_widget_and_simulator_on_checkout()
    {
        if (!Installments::show_widget_on_checkout()) {
            return;
        }

        if (!function_exists('WC') || !WC()->cart || !WC()->cart->total) {
            return;
        }

        $total = (float) WC()->cart->total;
        $min = (int) Installments::get_min_product_price();

        if ($total < $min) {
            return;
        }

        $this->render_markup();
    }

    public function add_widget_and_simulator_on_product()
    {
        if (!$this->is_product_and_in_range()) {
            return;
        }

        $this->render_markup();
    }

    private function render_markup()
    {
        $show = Installments::show_simulator() ? 'true' : 'false';
        echo '<p24-installment id="p24_installments" show-modal="' . $show . '" type="' . Installments::get_type_of_widget() . '"></p24-installment>';
    }

    static function get_checkout_data()
    {
        $config = Config::get_instance();
        $total = isset(WC()->cart) ? WC()->cart->total : 0;
        $min = Installments::get_min_product_price();

        $should_show = $total >= $min;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(
                'CHECKOUT TOTAL: ' . $total .
                ' | MIN: ' . $min .
                ' | SHOW: ' . ($should_show ? 'TAK' : 'NIE')
            );
        }

        return [
            'config' => [
                'sign' => Installments::get_signature(),
                'posid' => (string) $config->get_merchant_id(),
                'method' => (string) Payment_Methods::P24_INSTALLMENTS,
                'amount' => Helper::to_lowest_unit((float) $total),
                'currency' => $config->get_currency(),
                'lang' => 'pl',
                'test' => !$config->is_live(),
                'cms' => 'woo',
                'position' => Installments::get_product_widget_position() ?? 'before',
                'where' => 'checkout'
            ],
            'show' => $should_show,
            'showSimulator' => $should_show,
            'widgetType' => Installments::get_type_of_widget()
        ];
    }


    static function get_product_data()
    {
        $product = wc_get_product(get_the_ID());
        $config = Config::get_instance();

        if (!is_product() || !$product) {
            return [];
        }

        $price = (float) $product->get_price();

        return [
            'config' => [
                'sign' => Installments::get_signature(),
                'posid' => (string) $config->get_merchant_id(),
                'method' => (string) Payment_Methods::P24_INSTALLMENTS,
                'amount' => Helper::to_lowest_unit($price),
                'currency' => $config->get_currency(),
                'lang' => 'pl',
                'test' => !$config->is_live(),
                'cms' => 'woo',
                'position' => Installments::get_product_widget_position() ?? 'before',
                'where' => 'product'
            ],
            'show' => true,
            'showSimulator' => Installments::show_simulator(),
            'widgetType' => Installments::get_type_of_widget() ?? 'mini'
        ];
    }
}
