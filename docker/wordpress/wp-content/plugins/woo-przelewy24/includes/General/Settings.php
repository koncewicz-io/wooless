<?php

namespace WC_P24\General;

use WC_P24\API\Resources\Test_Access_Resource;
use WC_P24\Assets;
use WC_P24\Config;
use WC_P24\Models\Configuration;
use WC_P24\Utilities\Ajax;
use WC_P24\Utilities\Module_Settings;
use WC_P24\Utilities\Validator;

class Settings extends Module_Settings
{

    public function __construct()
    {
        parent::__construct();

        Ajax::add_action('test_connection', [$this, 'test_connection']);
    }

    public function get_handle(): string
    {
        return '';
    }

    public function get_label(): string
    {
        return __('General', 'woocommerce-p24');
    }

    protected function settings(): array
    {
        return [
            [
                'title' => __('Przelewy24 settings', 'woocommerce-p24'),
                'type' => 'title',
                'desc' => __('<h4>Przelewy24 payment gateway configuration</h4>Before configuring the P24 payment gateway, please ensure that you have added the server\'s <strong>IP address</strong> in the P24 transaction panel (follow the instructions: <a href="https://developers.przelewy24.pl/modules/index.php?pl#tag/Woocommerce-9.x-nowa-wersja-od-2025/Konfiguracja-adresu-IP-dla-konta-w-panelu-Przelewy24" target="_blank" title="IP address and default Web Services">link</a>.)', 'woocommerce-p24'),
            ],

            [
                'id' => 'p24_mode',
                'title' => __('Module mode', 'woocommerce-p24'),
                'type' => 'select',
                'custom_attributes' => ['required' => true],
                'desc' => __('Mode of transaction', 'woocommerce-p24'),
                'info' => '<h3> <svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('Module mode', 'woocommerce-p24').'</h3>'.__('<p>The module mode determines whether the plugin connects to the live Przelewy24 environment or the test environment. Choose "Normal - production" if you want to process real transactions, or "Test - sandbox" if you want to test with simulated transactions.<br><strong>(If you use test mode, be sure to enter the configuration keys from the Przelewy24 Sandbox environment-production and sandbox keys are different.)</strong>.</p>', 'woocommerce-p24').'<ul style="list-style: disclosure-open !important;"><li><h4 style="color:#cc0000;">'.__('Normal - production', 'woocommerce-p24').'</h4></li>'.__('<p>If you do not have an account with Przelewy24 yet, create your account.<br>Please register your account in the Przelewy24 service - <a href="https://panel.przelewy24.pl/rejestracja.php" target="_blank" title="Registration Form">link</a>.</p>After creating an account, you will receive the necessary data to configure this payment module.<br>', 'woocommerce-p24').'<br><hr><li><h4 style="color:#cc0000;">'.__('Test - sandbox', 'woocommerce-p24').'</h4>'.__('<p>The plug-in also allows orders to be processed in <strong>test mode</strong>. <br>To do this you need to switch the module mode from <strong>"Normal - production"</strong> to <strong>"Test - sandbox"</strong> and complete the configuration with the CRC key, which you will find in the sandbox panel. <br><br>Please remember to change the CRC key to the one from the Przelewy24 panel after disabling the test environment.<br><br>To activate a sandbox account, follow the instructions: <a href="https://developers.przelewy24.pl/index.php?en#tag/Set-up-and-test-your-accounts/Create-your-sandbox-account-(sandbox)" target="_blank" title="Registration Form">link</a>.</p>', 'woocommerce-p24').'</li></ul><hr />'.__('<h3>IP address</h3>To ensure a correct connection with the payment gateway, it is necessary to enter the IP address of the store\'s server in the Przelewy24 transaction panel, from which communication with the Przelewy24 system takes place. If you cannot find the IP address of your store\'s server, contact your hosting provider. You should enter the IP address in the Przelewy24 panel under <strong>"My account" -> "API Configuration"</strong><br/><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_setting-ip.png" alt="Adding a Currency Switcher Button" style="max-width: 400px">', 'woocommerce-p24'),                
                'options' => [
                    'sandbox' => __('Test - sandbox', 'woocommerce-p24'),
                    'production' => __('Normal - production', 'woocommerce-p24'),
                ],
            ],
            [
                'id' => 'p24_merchant_id',
                'title' => __('Merchant ID', 'woocommerce-p24'),
                'type' => 'text',
                'custom_attributes' => ['required' => true, 'pattern' => '^[0-9]+$', 'data-reverse-pattern' => '[^0-9]+'],
                'desc' => __('The merchant ID given in the Przelewy24 system', 'woocommerce-p24'),
				'info' => '<h3><svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> <span style="color:green;">'.__('Merchant ID', 'woocommerce-p24').'</span>/'.__('Shop ID', 'woocommerce-p24').'</h3> '.__('It is your login to the P24 Transaction Panel. It can be found as well in the P24 Transaction Panel in <br/><strong>"My account"</strong> => <strong>"Account Verification"</strong> => <strong>My stores</strong>.<br/><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_setting-general-merchantid1.png" alt="Merchant ID" style="max-width: 400px">', 'woocommerce-p24')
            ],
            [
                'id' => 'p24_crc_key',
                'title' => __('CRC key', 'woocommerce-p24'),
                'type' => 'password_toggle',
                'custom_attributes' => ['required' => true, 'pattern' => '^[a-f0-9]{16}$', 'data-reverse-pattern' => '[^a-f0-9]+', 'maxlength' => 16],
                'placeholder' => __('16 characters', 'woocommerce-p24'),
                'desc' => __('Enter the CRC key', 'woocommerce-p24'),
				'info' => '<h3><svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('CRC key', 'woocommerce-p24').'</span></h3> '.__('The CRC key and the API key are the neccessary configuration data needed for connection with the P24 system. You can find them in the P24 Transaction Panel in <br/><strong>"My account"</strong> => <strong>"API Configuration"</strong>.<br/><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_setting-crc_key.png" alt="CRC key" style="max-width: 400px">', 'woocommerce-p24'),
            ],
            [
                'id' => 'p24_reports_key',
                'title' => __('API key', 'woocommerce-p24'),
                'type' => 'password_toggle',
                'custom_attributes' => ['required' => true, 'pattern' => '^[a-f0-9]+$', 'data-reverse-pattern' => '[^a-f0-9]+'],
                'desc' => __('Enter your key for reports', 'woocommerce-p24'),
                'info' => '<h3><svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('API key', 'woocommerce-p24').'</span></h3> '.__('The CRC key and the API key are the neccessary configuration data needed for connection with the P24 system. You can find them in the P24 Transaction Panel in <br/><strong>"My account"</strong> => <strong>"API Configuration"</strong>.<br/><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_setting-api_key.png" alt="API key" style="max-width: 400px">', 'woocommerce-p24'),
            ],
            [
                'type' => 'button',
                'id' => 'test_connection',
                'label' => __('Test connection', 'woocommerce-p24'),
                'after' => '<span id="test_connection_status"></span>'
            ],
            [
                'type' => 'sectionend',
            ]
        ];
    }

    public function update_settings(): void
    {
        if ($this->is_current_section()) {
            $validator = new Validator();

            $validator->validate_merchant_id($_POST['p24_merchant_id']);
            $validator->validate_crc($_POST['p24_crc_key']);

            $validator->display_errors();
        }

        parent::update_settings();
    }

    public function test_connection(): void
    {
        $config = Config::get_instance();
        $test_configuration = new Configuration();
        $test_configuration->set_config($_POST['merchant_id'], $_POST['crc_key'], $_POST['reports_key'], $config->get_currency());

        $config->set_config($test_configuration);
        $config->set_live($_POST['mode'] === 'production');

        $client = new Test_Access_Resource();

        $response = $client->test_access();

        if (empty($response['data'])) {
            wp_send_json_error();
        }

        wp_send_json_success();
        $config->clear_config();
    }
}
