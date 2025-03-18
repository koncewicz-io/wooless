<?php

namespace WC_P24\Compatibility\Back_Compatibility;

use ArrayObject;
use WC_P24\Models\Configuration;
use WC_P24\Multicurrency\Multicurrency as MC;

class Multicurrency
{

    private ?ArrayObject $data = null;

    public function __construct()
    {
        $this->get_settings();
    }

    public function get_settings(): ?ArrayObject
    {
        if (empty($this->data)) {
            $data = new ArrayObject();
            $data->enabled = false;

            $data->currencies = get_option('przelewy24_multi_currency_multipliers', []);
            $data->common = get_option('przelewy24_common_settings', []);

            if (isset($data->common['p24_multi_currency'])) {
                $data->enabled = $data->common['p24_multi_currency'] === 'yes';
            }

            $this->data = $data;
        }

        return $this->data;
    }

    public function import(): void
    {
        $data = $this->get_settings();

        if ($data->enabled) {
            update_option(MC::ENABLE_KEY, 'yes');
        }

        if (empty($data->currencies)) return;

        $config = new Configuration(true);
        $current = get_option('woocommerce_currency');

        $codes = [];

        foreach ($data->currencies as $currency => $multipler) {
            if (strtolower($currency) === strtolower($current)) {
                continue;
            }

            $currency_code = strtolower($currency);
            $codes[] = $currency_code;

            update_option('p24_currency_' . $currency_code, [
                'merchant_id' => $config->merchant_id,
                'crc_key' => $config->crc_key,
                'reports_key' => $config->reports_key,
                'multiplier' => (float)$multipler
            ]);
        }

        update_option('p24_currencies', $codes);
    }
}
