<?php

namespace WC_P24\Gateways\Blik;

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;
use WC_P24\Core;
use WC_P24\Gateways\Fee;
use WC_P24\Gateways\Payment_Helpers;
use WC_P24\Gateways\Settings_Helper;
use WC_P24\Models\Transaction;
use WC_P24\OneClick\One_Clicks;
use WC_P24\Utilities\Base_Gateway_Block;
use WC_P24\Utilities\Payment_Methods;
use WC_P24\Utilities\Sanitizer;
use WC_P24\Utilities\Validator;
use WC_P24\Utilities\Webhook;
use WC_Payment_Gateway;

class Gateway extends WC_Payment_Gateway
{
    use Payment_Helpers;
    use Settings_Helper;
    use Blik_Legacy_Support;

    private ?Webhook $webhooks = null;

    public function __construct()
    {
        $this->id = Core::BLIK_IN_SHOP_METHOD;

        $this->icon = apply_filters('woocommerce_gateway_icon', WC_P24_PLUGIN_URL . 'assets/blik.svg');
        $this->method = Payment_Methods::BLIK_PAYMENT;
        $this->description = $this->get_option('description');
        $this->supports = ['products', 'refunds'];

        $this->method_title = __('Przelewy24 - BLIK', 'woocommerce-p24');
        /* translators: %s: URL to the general configuration page */
        $this->method_description = sprintf(__('BLIK payment option on the shop <br /><a href="%s">General configuration</a>', 'woocommerce-p24'), Core::get_settings_url());
        $this->title = $this->get_option('title') ?: __('BLIK', 'woocommerce-p24');

        new Fee($this);
        $this->webhooks = new Blik_Webhooks($this);

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_rest_checkout_process_payment_with_context', [$this, 'process_payment_rest'], 10, 2);

        $this->init_form_fields();
        $this->init_legacy();
    }

    public function one_click_enabled(): bool
    {
        return $this->get_option('one_click_enabled') == 'yes';
    }

    public function init_form_fields(): void
    {
        $this->form_fields = array_merge([
            'enabled' => [
                'type' => 'checkbox',
                'label' => __('Enable Przelewy24 - BLIK', 'woocommerce-p24'),
                'custom_attributes' => ['data-enabled' => true],
                'info' => '<svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> ' . __('<h3>BLIK Level 0 Payment</h3>Enabling this option will allow the payer to enter the BLIK code directly on your website. (Available for a customer after their first payment, with the agreement checked).<br/><strong>Before turning this option on, please contact P24 to enable adequate services on your account for on-site Blik payments.</strong><br/><br/><em>Example view for the buyer:</em><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_blik_lvl0.png" alt="BLIK Level 0 Payment" style="max-width: 400px">', 'woocommerce-p24'),
            ],
            'title' => [
                'type' => 'text',
                'title' => __('Payment title', 'woocommerce-p24'),
                'description' => __('Name of payment visible at checkout', 'woocommerce-p24'),
                'default' => __('BLIK', 'woocommerce-p24')
            ],
            'description' => [
                'type' => 'textarea',
                'title' => __('Description', 'woocommerce-p24'),
                'description' => __('Description of payment which the user sees during checkout', 'woocommerce-p24'),
                'default' => __('You can find the BLIK code in your banking app', 'woocommerce-p24')
            ],
            'one_click_enabled' => [
                'type' => 'checkbox',
                'title' => __('BLIK One Click Payment', 'woocommerce-p24'),
                'label' => __('Enable one click BLIK payment', 'woocommerce-p24'),
                'info' => '<svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> ' . __('<h3>BLIK One Click Payment</h3>Enabling this option allows for BLIK payments, using saved alias. (Available for a customer after their first payment, with the agreement checked).<br/><strong>Before turning this option on, please contact P24 to enable adequate services on your account for on-site Blik payments.</strong><br/><br/><em>Example view for the buyer:</em><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_blik_oneclick.png" alt="BLIK One Click Payment" style="max-width: 400px">', 'woocommerce-p24'),
                'default' => 'no',
            ]],
            $this->fee_settings()
        );
    }

    private function sanitize(array $data): array
    {
        $sanitizer = new Sanitizer($data, [
            'regulation' => FILTER_VALIDATE_BOOLEAN,
            'type' => Sanitizer::sanitize_key_as_filter(),
            'save' => FILTER_VALIDATE_BOOLEAN,
            'oneclick' => FILTER_SANITIZE_NUMBER_INT,
            'code' => [
                'filter' => FILTER_CALLBACK,
                'options' => function ($value) {
                    return preg_replace('/[\D+]/', '', $value);
                }
            ]
        ]);

        return $sanitizer->run();
    }

    private function validate(array $data): void
    {
        $validator = new Validator();

        if (!$data['regulation']) {
            $validator->add_error('You have to accept the terms and conditions for using this method');
        }

        if (empty($data['type'])) {
            $validator->add_error('Transaction type not provided');
        }

        if (!in_array($data['type'], ['code', 'one-click'])) {
            $validator->add_error('Wrong transaction type');
        }

        if ($data['type'] === 'one-click' && empty($data['oneclick'])) {
            $validator->add_error('One click id is not provided');
        }

        if ($data['type'] === 'code') {
            if (empty($data['code'])) {
                $validator->add_error('One click id is not provided');
            } elseif (!preg_match('/^\d{6}$/', $data['code'])) {
                $validator->add_error('BLIK code is incorrect');
            }
        }

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

        $type = $payment_data['type'];
        $save = $payment_data['save'];
        $code = $payment_data['code'];
        $accept_rules = $payment_data['regulation'];

        $transaction = new Transaction($order->get_id(), $this->method, $accept_rules);

        if ($save && $type != 'one-click') {
            $transaction->set_save_blik(true);
        }

        if ($type == 'one-click' && $this->one_click_enabled()) {
            $one_click_id = (int)$payment_data['oneclick'];
            $reference = One_Clicks::get_blik_alias($one_click_id);

            if ($reference) {
                $transaction->set_one_click(Transaction::ONE_CLICK_BLIK);
                $transaction->set_reference($reference);
            }
        }

        $transaction->register();

        switch ($type) {
            case 'code':
                $transaction->charge_blik_by_code($code);
                break;
            case 'one-click':
                $transaction->charge_blik_by_alias();
                break;
        }

        return $result;
    }

    public function process_payment_rest(PaymentContext $context, PaymentResult &$payment_result): void
    {
        if ($context->payment_method === Core::BLIK_IN_SHOP_METHOD) {
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
        $get_one_clicks = is_checkout();

        $one_click_enabled = $this->one_click_enabled() && is_user_logged_in();
        $one_click_items = [];

        if ($get_one_clicks && $one_click_enabled) {
            $one_click_items = array_map(function ($item) {
                return [
                    'id' => $item->get_id(),
                    'type' => $item->get_type(),
                    'valid_to' => $item->get_valid_to()->format('d/m/Y'),
                    'name' => $item->get_info(),
                    'logo' => $item->get_icon()
                ];
            }, One_Clicks::get_blik_aliases());
        }

        return [
            'url' => $this->webhooks->get_process_blik_url(),
            'i18n' => [
                'error' => [
                    'aborted' => _x('You cancelled a payment using BLIK', 'BLIK payment', 'woocommerce-p24'),
                    'unexpected' => _x('An unexpected error occurred', 'BLIK payment', 'woocommerce-p24'),
                    'timeout' => _x('Time to confirm the transaction has passed', 'BLIK payment', 'woocommerce-p24'),
                    'validation' => _x('Enter a valid BLIK code (6 digits)', 'BLIK code validation', 'woocommerce-p24'),
                    'rules' => __('You have to accept the terms and conditions for using the chosen method', 'woocommerce-p24'),
                ],
                'confirm' => [
                    'transaction' => __('Confirm transactions in the banking app', 'woocommerce-p24'),
                    'alias' => __('Confirm the creation of an alias in the banking application', 'woocommerce-p24')
                ],
                'label' => [
                    'submit' => __('Pay with BLIK', 'woocommerce-p24'),
                    'input' => __('BLIK code', 'woocommerce-p24'),
                    'save' => __('Save blik alias for future payments', 'woocommerce-p24'),
                    'cancel' => _x('Continue without saving alias', 'BLIK confirm alias creation', 'woocommerce-p24'),
                    /* translators: %1$s: URL to the regulations page, %2$s: URL to the information obligation page */
                    'regulation' => sprintf(__('I hereby state that I have read the <a href="%1$s" target="_blank">regulations</a> and <a href="%2$s" target="_blank">information obligation</a> of "Przelewy24"', 'woocommerce-p24'), Core::get_rules_url(), Core::get_tos_url()),
                ],
                'use_saved' => _x('Use saved payment method - on-click', 'BLIK one-click', 'woocommerce-p24'),
                'or' => _x('or use BLIK code', 'BLIK one-click', 'woocommerce-p24'),
            ],
            'oneClick' => [
                'enabled' => $one_click_enabled,
                'items' => $one_click_items
            ]
        ];
    }
}
