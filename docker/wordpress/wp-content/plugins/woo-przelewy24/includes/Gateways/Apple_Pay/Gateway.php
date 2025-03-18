<?php

namespace WC_P24\Gateways\Apple_Pay;

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;
use WC_P24\Config;
use WC_P24\Core;
use WC_P24\Gateways\Fee;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Gateways\Payment_Helpers;
use WC_P24\Gateways\Settings_Helper;
use WC_P24\Models\Transaction;
use WC_P24\Utilities\Base_Gateway_Block;
use WC_P24\Utilities\Notice;
use WC_P24\Utilities\Payment_Methods;
use WC_P24\Utilities\Sanitizer;
use WC_P24\Utilities\Validator;
use WC_P24\Utilities\Webhook;
use WC_Payment_Gateway;

class Gateway extends WC_Payment_Gateway
{
    use Payment_Helpers;
    use Settings_Helper;
    use Apple_Pay_Legacy_Support;

    private ?Webhook $webhooks = null;

    public function __construct()
    {
        $this->id = Core::APPLE_PAY_IN_SHOP_METHOD;

        $this->icon = apply_filters('woocommerce_gateway_icon', WC_P24_PLUGIN_URL . '/assets/svg/apple-pay.svg');
        $this->method = Payment_Methods::APPLE_PAY;
        $this->method_alt = Payment_Methods::APPLE_PAY_ALT;

        $this->subgroup = 'external-payments';

        $this->method_title = __('Przelewy24 - Apple Pay', 'woocommerce-p24');
        $this->method_description = sprintf(__('Apple Pay option on the shop <br /><a href="%s">General configuration</a>', 'woocommerce-p24'), Core::get_settings_url());

        $this->title = $this->get_option('title') ?: __('Apple Pay', 'woocommerce-p24');
        $this->description = $this->get_option('description');
        $this->supports = ['products', 'refunds'];

        $this->init_form_fields();

        new Fee($this);
        $this->webhooks = new Apple_Pay_Webhooks($this);
        $this->notice();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_rest_checkout_process_payment_with_context', [$this, 'process_payment_rest'], 10, 2);

        $this->init_legacy();
    }

    public function is_available(): bool
    {
        return parent::is_available() && $this->check_availability();
    }

    private function check_availability(): bool
    {
        $id = $this->get_option('merchant_id');
        $domain = $this->get_option('merchant_domain');
        $key = $this->get_option('cert_key');
        $pem = $this->get_option('cert_pem');

        return count(array_filter([$id, $domain, $key, $pem])) == 4;
    }

    private function notice()
    {
        if ($this->is_enabled()) {
            $requires = [];

            $id = $this->get_option('merchant_id');
            $domain = $this->get_option('merchant_domain');

            $key = $this->get_option('cert_key');
            $pem = $this->get_option('cert_pem');
            $keyExist = $key && @file_exists($key);
            $pemExist = $pem && @file_exists($pem);

            if (!$id) {
                $requires[] = _x('merchant id', 'Apple pay require field', 'woocommerce-p24');
            }

            if (!$domain) {
                $requires[] = _x('domain', 'Apple pay require field', 'woocommerce-p24');
            }

            if (!($key || $keyExist)) {
                $requires[] = _x('certificate key', 'Apple pay require field', 'woocommerce-p24');
            }

            if (!($pem || $pemExist)) {
                $requires[] = _x('certificate pem', 'Apple pay require field', 'woocommerce-p24');
            }

            if (!empty($requires)) {
                new Notice(sprintf(__('Przelewy24 - Apple Pay requires <strong>%s</strong> to work properly', 'woocommerce-p24'), implode(', ', $requires)), Notice::WARNING);
            }
        }
    }

    public function block_support(): Base_Gateway_Block
    {
        return new Apple_Pay_Block($this);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = array_merge([
            'information' => [
                'type' => 'title',
                'description' => __('Enabling this option allows customers to pay using Apple Pay directly on the shop website.<br><strong>Before turning this option on, please contact P24 to enable adequate services on your account for on-site card payments.</strong>', 'woocommerce-p24'),
            ],
            'enabled' => [
                'type' => 'checkbox',
                'label' => __('Enable Przelewy24 - Apple Pay', 'woocommerce-p24'),
                'custom_attributes' => ['data-enabled' => true]
            ],
            'title' => [
                'type' => 'text',
                'title' => __('Payment title', 'woocommerce-p24'),
                'description' => __('Name of payment visible at checkout', 'woocommerce-p24'),
                'default' => __('Apple Pay', 'woocommerce-p24')
            ],
            'description' => [
                'type' => 'textarea',
                'title' => __('Description', 'woocommerce-p24'),
                'description' => __('Description of payment which the user sees during checkout', 'woocommerce-p24'),
            ],
            'merchant_name' => [
                'type' => 'text',
                'title' => __('Apple merchant name', 'woocommerce-p24'),
                'description' => __('Your Apple merchant name, visible for customer', 'woocommerce-p24'),
                'required' => true
            ],
            'merchant_id' => [
                'type' => 'text',
                'title' => __('Apple merchant ID', 'woocommerce-p24'),
                'description' => __('Your Apple merchant ID', 'woocommerce-p24'),
                'info' => '<h3><svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('Apple merchant ID', 'woocommerce-p24').'</h3> '.__('In order to obtain configuration data for Apple Pay on-site payments, please follow the instructions from our documentation below: <a href="https://developers.przelewy24.pl/index.php?en#tag/APay-Description/Creating-Apple-Pay-Payment-Processing-Certificate" title="Instruction" target="_blank">Creating Apple Pay Payment Processing Certificate</a><br/>', 'woocommerce-p24'),
                'required' => true
            ],
            'merchant_domain' => [
                'type' => 'text',
                'title' => __('Associated domain with Apple merchant', 'woocommerce-p24'),
                'description' => __('Your shop domain', 'woocommerce-p24'),
                'required' => true
            ],
            'cert_pem' => [
                'type' => 'text',
                'title' => __('Apple certificate key', 'woocommerce-p24'),
                'description' => __('Absolute path of certificate .key.pem file', 'woocommerce-p24'),
                'info' => '<h3><svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('Apple certificate key', 'woocommerce-p24').'</h3> '.__('In order to obtain configuration data for Apple Pay on-site payments, please follow the instructions from our documentation below: <a href="https://developers.przelewy24.pl/index.php?en#tag/APay-Description/Creating-Apple-Pay-Payment-Processing-Certificate" title="Instruction" target="_blank">Creating Apple Pay Payment Processing Certificate</a><br/>', 'woocommerce-p24'),
                'required' => true
            ],
            'cert_key' => [
                'type' => 'text',
                'title' => __('Apple certificate cert', 'woocommerce-p24'),
                'description' => __('Absolute path of certificate .crt.pem file', 'woocommerce-p24'),
                'required' => true
            ]
        ],
            $this->fee_settings(),
        );
    }

    private function sanitize(array $data): array
    {
        $sanitizer = new Sanitizer($data, [
            'regulation' => FILTER_VALIDATE_BOOLEAN,
            'payload' => FILTER_SANITIZE_STRING,
        ]);

        return $sanitizer->run();
    }

    private function validate(array $data): void
    {
        $validator = new Validator();

        if (!$data['regulation']) {
            $validator->add_error('You have to accept the terms and conditions for using this method');
        }

        if (empty($data['payload'])) {
            $validator->add_error('Payload token from Apple Pay not provided');
        }

        $validator->validate_base64($data['payload']);

        if ($validator->has_errors()) {
            throw new \Exception($validator->get_first_error());
        }
    }

    public function payment(\WC_Order $order, array $payment_data = []): array
    {
        $payment_data = $this->sanitize($payment_data);
        $this->validate($payment_data);

        $result = [
            'redirect' => $order->get_checkout_order_received_url(),
            'status' => 'pending'
        ];

        $token = $payment_data['payload'];
        $accept_rules = $payment_data['regulation'];

        $method_id = Gateways_Manager::get_method_id_matching_group(Payment_Methods::APPLE_PAY_ALT, $order->get_total());

        $transaction = new Transaction($order->get_id(), $method_id, $accept_rules);
        $transaction->set_payload($token, Transaction::APPLE_PAY);
        $transaction->register();

        $result['token'] = $transaction->get_token();
        $result['success'] = true;

        return $result;
    }

    public function process_payment_rest(PaymentContext $context, PaymentResult &$payment_result): void
    {
        if ($context->payment_method === Core::APPLE_PAY_IN_SHOP_METHOD) {
            try {
                $details = $this->payment($context->order, $context->payment_data);

                $payment_result->set_payment_details($details);
                $payment_result->set_status($details['status']);
            } catch (\Exception $e) {
                $payment_result->set_payment_details(['message' => $e->getMessage()]);
                $payment_result->set_status('error');
            }
        }
    }

    public function get_receipt_config(): array
    {
        $config = Config::get_instance();

        $settings = [
            'appleMerchantId' => $this->get_option('merchant_id'),
            'merchantName' => $this->get_option('merchant_name')
        ];

        return [
            'mode' => $config->get_mode_prefix(),
            'config' => $settings,
            'url' => $this->webhooks->get_process_apple_pay_url(),
            'validateUrl' => $this->webhooks->get_process_validate_url(),
            'i18n' => [
                'label' => [
                    'submit' => __('Pay by Apple Pay', 'woocommerce-p24'),
                    'regulation' => sprintf(__('I hereby state that I have read the <a href="%s" target="_blank">regulations</a> and <a href="%s" target="_blank">information obligation</a> of "Przelewy24"', 'woocommerce-p24'), Core::get_rules_url(), Core::get_tos_url()),
                ],
                'error' => [
                    'unavailable' => __('This payment method is unavailable, for this browser', 'woocommerce-p24'),
                    'rules' => __('You have to accept the terms and conditions for using the chosen method', 'woocommerce-p24'),
                ]
            ]
        ];
    }
}
