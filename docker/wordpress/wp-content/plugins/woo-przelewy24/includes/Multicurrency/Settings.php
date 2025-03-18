<?php

namespace WC_P24\Multicurrency;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Assets;
use WC_P24\Render;
use WC_P24\UI\Display;
use WC_P24\Utilities\Ajax;
use WC_P24\Utilities\Module_Settings;
use WC_P24\Utilities\Validator;

class Settings extends Module_Settings
{
    public function __construct()
    {
        parent::__construct();

        Ajax::add_action('save_currency', [$this, 'save_currency']);
        Ajax::add_action('remove_currency', [$this, 'remove_currency']);

        $this->assets();
    }

    public function get_handle(): string
    {
        return 'multicurrency';
    }

    public function get_label(): string
    {
        return __('Multicurrency', 'woocommerce-p24');
    }

    public function settings(): array
    {
        return [
            [
                'type' => 'title',
                'title' => __('Multicurrency configuration', 'woocommerce-p24'),
                'desc' => __('<p>The Przelewy24 payment module allows handling multiple currencies within a single store.</p> <h3>How to configure?</h3> <ol> <li><strong>Activate the module</strong> - after enabling it, add the currency below.</li> <li><strong>Ensure the account supports the selected currency</strong> - before adding the configuration, verify that the account you want to use for handling foreign currency payments has the appropriate payment methods enabled. To activate currency support, contact Przelewy24.</li> <li><strong>Configure the currency</strong> - provide the required details:</li> <ul style="list-style: inside;"> <li><strong>Merchant ID</strong></li> <li><strong>CRC Key</strong></li> <li><strong>API Key</strong></li> </ul> <li><strong>Set the currency multiplier</strong> - define the exchange rate relative to the store\'s base currency.</li></ol>', 'woocommerce-p24'),
            ],
            [
                'id' => Multicurrency::ENABLE_KEY,
                'type' => 'checkbox',
                'desc' => __('Enable multicurrency module', 'woocommerce-p24'),
                'info' => '<h3><svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('Adding a Currency Switcher Button', 'woocommerce-p24').'</h3> '.__('<p>To add a currency switcher button to your website, use the pre-made block called <strong>"Przelewy24 - Multi Currency Switcher"</strong>.</p><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_multicurrency1.png" alt="Adding a Currency Switcher Button" style="max-width: 400px"><br /><p>You can also use the shortcode named <strong><code>[p24_multicurrency_switcher]</code></strong>.</p><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_multicurrency2.png" alt="Adding a Currency Switcher Button" style="max-width: 400px"><br /><br /><em>Example view for the buyer:</em><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_multicurrency3.png" alt="Adding a Currency Switcher Button" style="max-width: 400px">', 'woocommerce-p24'),
                'default' => 'no'
            ],
            [
                'type' => 'sectionend'
            ]
        ];
    }

    public function form_settings(): array
    {
        return [
            [
                'id' => 'p24_currency_code',
                'type' => 'hidden',
                'name' => 'currency_code'
            ],
            [
                'id' => 'p24_currency_merchant_id',
                'name' => 'merchant_id',
                'title' => __('Merchant ID', 'woocommerce-p24'),
                'type' => 'text',
                'custom_attributes' => ['required' => true, 'pattern' => '^[0-9]+$'],
                'desc' => __('The merchant ID given in the Przelewy24 system', 'woocommerce-p24'),
            ],
            [
                'id' => 'p24_currency_crc_key',
                'name' => 'crc_key',
                'title' => __('CRC key', 'woocommerce-p24'),
                'type' => 'password_toggle',
                'custom_attributes' => ['required' => true, 'pattern' => '^[a-f0-9]{16}$'],
                'placeholder' => __('16 characters', 'woocommerce-p24'),
                'desc' => __('Enter the CRC key', 'woocommerce-p24')
            ],
            [
                'id' => 'p24_currency_reports_key',
                'name' => 'reports_key',
                'title' => __('API key', 'woocommerce-p24'),
                'type' => 'password_toggle',
                'custom_attributes' => ['required' => true, 'pattern' => '^[a-f0-9]+$'],
                'desc' => __('Enter your API key', 'woocommerce-p24'),
            ],
            [
                'id' => 'p24_currency_multiplier',
                'name' => 'multiplier',
                'title' => __('Multiplier', 'woocommerce-p24'),
                'type' => 'number',
                'custom_attributes' => ['required' => true, 'step' => '0.01'],
                'desc' => __('Enter a multiple in relation to the base currency', 'woocommerce-p24'),
            ]
        ];
    }

    public function update_currencies(string $currency_code, bool $delete = false): void
    {
        $currencies = get_option('p24_currencies', []);

        if ($delete) {
            $index = array_search($currency_code, $currencies);
            if ($index >= 0) {
                unset($currencies[$index]);
            }
        } else {
            $currencies[] = $currency_code;
        }

        $currencies = array_unique($currencies);

        update_option('p24_currencies', $currencies);
    }

    public function save_currency()
    {
        check_ajax_referer('save_currency', 'save_currency_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'woocommerce-p24')]);
        }

        $currency_code = strtolower(sanitize_key($_POST['currency_code']));
        $merchant_id = sanitize_key($_POST['merchant_id']);
        $crc_key = sanitize_key($_POST['crc_key']);
        $reports_key = sanitize_key($_POST['reports_key']);
        $multiplier = (float)$_POST['multiplier'];

        $validator = new Validator();

        $validator->validate_currency($currency_code);
        $validator->validate_merchant_id($merchant_id);
        $validator->validate_crc($crc_key);
        $validator->validate_reports_key($reports_key);

        if ($validator->has_errors()) {
            wp_send_json_error(['message' => __('Validation errors', 'woocommerce-p24')], 422);
            exit;
        }

        update_option('p24_currency_' . $currency_code, [
            'merchant_id' => $merchant_id,
            'crc_key' => $crc_key,
            'reports_key' => $reports_key,
            'multiplier' => $multiplier
        ]);

        $this->update_currencies($currency_code);

        wp_send_json_success(['success' => true]);
    }

    public function remove_currency()
    {
        check_ajax_referer('remove_currency', 'remove_currency_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'woocommerce-p24')]);
        }

        $currency_code = strtolower(sanitize_key($_POST['currency_code']));

        $validator = new Validator();
        $validator->validate_currency($currency_code);

        if ($validator->has_errors()) {
            wp_send_json_error(['message' => __('Validation errors', 'woocommerce-p24')], 422);
            exit;
        }

        $this->update_currencies($currency_code, true);

        wp_send_json_success(['success' => true]);
    }

    public function get_currecies()
    {
        $currencies = get_woocommerce_currencies();

        foreach ($currencies as $key => $currency) {
            $currencies[$key] = [
                'name' => $currency,
                'symbol' => get_woocommerce_currency_symbol($key),
            ];
        }

        return $currencies;
    }

    protected function after_settings(): ?string
    {
        $current_currency = get_woocommerce_currency();
        $currencies = $this->get_currecies();

        $featured_currencies = ['USD', 'GBP', 'EUR'];
        $featured = [];

        foreach ($featured_currencies as $feature) {
            $featured[$feature] = $currencies[$feature];
            unset($currencies[$feature]);
        }

        unset($featured[$current_currency]);
        unset($currencies[$current_currency]);

        $currency_configs = Multicurrency::get_available_currencies();

        return Render::return('multicurrency/content', [
            'currencies' => $currencies,
            'featured_currencies' => $featured,
            'current_currency' => $current_currency,
            'currency_configs' => $currency_configs
        ]);
    }

    public function after_form(): void
    {
        if ($this->is_current_section()) {
            $display = new Display(Display::CUSTOM_FORM);
            $display->set_fields($this->form_settings());

            Render::template('multicurrency/form', [
                'fields' => $display->fields,
                'currencies' => $this->get_currecies()
            ]);
        }
    }

    public function assets(): void
    {
        if (!$this->is_current_section()) {
            return;
        }

        Assets::add_script_asset('p24-admin-multi-currency', 'admin-multi-currency.bundle.min.js', true);

        Assets::add_script_localize('p24-admin-multi-currency', 'przelewy24MulticurrencyData', [
            'saveCurrencyNonce' => wp_create_nonce('save_currency'),
            'removeCurrencyNonce' => wp_create_nonce('remove_currency')
        ], true);
    }
}
