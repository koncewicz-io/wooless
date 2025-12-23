<?php

namespace WC_P24\Compatibility\Back_Compatibility;

use ArrayObject;
use WC_P24\Core;
use WC_P24\Installments\Installments;

class Settings
{
    private ?ArrayObject $data = null;

    public function __construct()
    {
        $this->get_settings();
    }

    public function get_settings(): ?ArrayObject
    {
        if (empty($this->data)) {
            $current_currency = strtolower(get_option('woocommerce_currency'));
            $common = get_option('woocommerce_przelewy24_' . $current_currency . '_settings');

            if (!empty($common)) {
                $data = new ArrayObject();
                $data->merchant_id = $common['merchant_id'];
                $data->crc_key = $common['CRC_key'];
                $data->reports_key = $common['p24_api'];
                $data->mode = $common['p24_testmod'] == 'secure' ? 'production' : 'sandbox';

                $data->show_featured = $common['p24_show_methods_checkout'] === 'yes';
                $data->featured = $common['p24_paymethods_super_first'];

                $data->show_methods = $common['p24_graphics'] === 'yes';
                $data->order = $common['p24_paymethods_first'] . ':' . $common['p24_paymethods_second'];

                $data->pay_in_shop = $common['p24_payinshop'] === 'yes';
                $data->one_click = $common['p24_oneclick'] === 'yes';
                $data->installments = $common['p24_custom_promote_p24'] === 'yes';

                $this->data = $data;
            }
        }

        return $this->data;
    }

    public function save_settings($custom_data)
    {
        $data = $this->get_settings();

        $custom_data->merchant_id && update_option('p24_merchant_id', $custom_data->merchant_id);
        $custom_data->crc_key && update_option('p24_crc_key', $custom_data->crc_key);
        $custom_data->reports_key && update_option('p24_reports_key', $custom_data->reports_key);
        $custom_data->mode && update_option('p24_mode', $custom_data->mode);

        $settings_main = get_option('woocommerce_' . Core::MAIN_METHOD . '_settings');
        $settings_card = get_option('woocommerce_' . Core::CARD_IN_SHOP_METHOD . '_settings');
        $settings_blik = get_option('woocommerce_' . Core::BLIK_IN_SHOP_METHOD . '_settings');

        update_option('woocommerce_' . Core::MAIN_METHOD . '_settings', array_merge($settings_main ?: [], [
            'enabled' => 'yes',

            'show_available_methods' => $data->show_methods ? 'yes' : 'no',
            'methods' => $data->order,

            'feature_as_gateway' => $data->show_featured ? 'yes' : 'no',
            'gateway_methods' => $data->featured . ':',
        ]));

        if ($data->pay_in_shop) {
            update_option('woocommerce_' . Core::CARD_IN_SHOP_METHOD . '_settings', array_merge($settings_card ?: [], [
                'enabled' => 'yes',
                'one_click_enabled' => $data->one_click ? 'yes' : 'no',
            ]));

            update_option('woocommerce_' . Core::BLIK_IN_SHOP_METHOD . '_settings', array_merge($settings_blik ?: [], [
                'enabled' => 'yes',
                'one_click_enabled' => $data->one_click ? 'yes' : 'no',
            ]));
        }

        if ($data->installments) {
            update_option(Installments::ENABLE_KEY, 'yes');
            update_option(Installments::PREFIX . 'show_widget_on_checkout', 'yes');
            update_option(Installments::PREFIX . 'show_widget_on_product', 'yes');
            update_option(Installments::PREFIX . 'show_simulator', 'yes');
        }
    }
}
