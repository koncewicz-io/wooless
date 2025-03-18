<?php

namespace WC_P24\Installments;

use WC_P24\Assets;
use WC_P24\Config;
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

        if ($widgetEnabled) {
            add_action('woocommerce_' . $widgetPosition . '_add_to_cart_form', [$this, 'add_widget_and_simulator']);
            add_filter('script_loader_tag', [$this, 'script_tag_add_attribute'], 10, 3);

            Assets::add_script_asset('p24-installments', 'https://apm.przelewy24.pl/installments/installment-calculator-app.umd.sdk.js', false, false, true);
        }
    }

    public function script_tag_add_attribute($tag, $handle, $source): string
    {
        if ($handle === 'p24-installments') {
            $tag = '<script type="application/javascript" src="' . $source . '" id="' . $handle . '"></script>';
        }

        return $tag;
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


    public function add_widget_and_simulator()
    {
        $product = wc_get_product(get_the_ID());
        $config = Config::get_instance();

        if (!$this->product_price_in_range($product->get_price()))
            return;

        Assets::add_script_localize('p24-installments', 'p24InstallmentsData', [
            'config' => [
                'sign' => Installments::get_signature(),
                'posid' => (string)$config->get_merchant_id(),
                'method' => (string)Payment_Methods::P24_INSTALLMENTS,
                'amount' => Helper::to_lowest_unit($product->get_price()),
                'currency' => $config->get_currency(),
                'lang' => 'pl'
            ],
            'show' => true,
            'showSimulator' => $this->simulatorEnabled,
            'widgetType' => Installments::get_type_of_widget()
        ]);

        Assets::add_script_asset('p24-installments-widget', 'installment.js');

        $modal = $this->simulatorEnabled ? '<div id="p24_installment_modal"></div>' : '';

        echo '<div id="p24_installment"><div id="p24_installment_widget"></div>' . $modal . ' </div>';
    }

}
