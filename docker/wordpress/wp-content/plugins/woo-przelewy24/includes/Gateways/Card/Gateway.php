<?php

namespace WC_P24\Gateways\Card;

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;
use WC_P24\API\Resources\Card_Resource;
use WC_P24\Config;
use WC_P24\Core;
use WC_P24\Gateways\Fee;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Gateways\Payment_Helpers;
use WC_P24\Gateways\Settings_Helper;
use WC_P24\Helper;
use WC_P24\Models\Database\Reference;
use WC_P24\Models\Simple\Card_Simple;
use WC_P24\Models\Transaction;
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
    use Card_Legacy_Support;

    public ?Webhook $webhooks = null;

    public function __construct()
    {
        $this->id = Core::CARD_IN_SHOP_METHOD;

        $this->icon = apply_filters('woocommerce_gateway_icon', WC_P24_PLUGIN_URL . '/assets/card.svg');
        $this->method = Payment_Methods::CARD_PAYMENT;
        $this->method_alt = Payment_Methods::CARD_PAYMENT_ALT;

        $this->method_title = __('Przelewy24 - Card payment', 'woocommerce-p24');
        $this->method_description = sprintf(__('Card payment option on the shop <br /><a href="%s">General configuration</a>', 'woocommerce-p24'), Core::get_settings_url());

        $this->title = $this->get_option('title') ?: __('Card payment', 'woocommerce-p24');
        $this->description = $this->get_option('description');
        $this->supports = ['products', 'refunds', 'p24-subscription'];

        $this->init_form_fields();

        new Fee($this);
        $this->webhooks = new Card_Webhooks($this);

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_rest_checkout_process_payment_with_context', [$this, 'process_payment_rest'], 10, 2);
        add_action('przelewy24_after_verify_transaction', [$this, 'save_card_reference'], 10, 1);

        $this->init_legacy();
    }

    public function one_click_enabled(): bool
    {
        return $this->get_option('one_click_enabled') == 'yes';
    }

    public function click_to_pay_enabled(): bool
    {
        $enabled = $this->get_option('c2p_enabled') == 'yes';
        $for = $this->get_option('c2p_for');
        $result = $enabled;

        if ($for == 'logged_in') {
            $result = is_user_logged_in() ? $enabled : false;
        } elseif ($for == 'logged_out') {
            $result = is_user_logged_in() ? false : $enabled;
        }

        $result = is_user_logged_in() && $this->one_click_enabled() ? false : $result;

        return $result;
    }

    public function block_support(): Base_Gateway_Block
    {
        return new Card_Block($this);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = array_merge([
            'enabled' => [
                'type' => 'checkbox',
                'label' => __('Enable Przelewy24 - card payment', 'woocommerce-p24'),
                'custom_attributes' => ['data-enabled' => true],
                'info' => '<svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('<h3>Card payment</h3>Enabling this option allows for card payments, using saved alias. (Available for a customer after their first payment, with the agreement checked). <br/><strong>Before turning this option on, please contact P24 to enable adequate services on your account for on-site card payments.</strong><br/><br/><em>Example view for the buyer:</em><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_karta_lvl0.png" alt="Card payment" style="max-width: 400px">', 'woocommerce-p24'),

            ],
            'title' => [
                'type' => 'text',
                'title' => __('Payment title', 'woocommerce-p24'),
                'description' => __('Name of payment visible at checkout', 'woocommerce-p24'),
                'default' => __('Card payment', 'woocommerce-p24'),
            ],
            'description' => [
                'type' => 'textarea',
                'title' => __('Description', 'woocommerce-p24'),
                'description' => __('Description of payment which the user sees during checkout', 'woocommerce-p24')
            ],
            'c2p_enabled' => [
                'type' => 'checkbox',
                'title' => __('Click to Pay enabled', 'woocommerce-p24'),
                'label' => __('Enable Click to Pay functionality', 'woocommerce-p24'),
                'default' => 'no',
            ],
            'c2p_for' => [
                'type' => 'select',
                'title' => __('Click to Pay enabled for', 'woocommerce-p24'),
                'default' => 'both',
                'options' => [
                    'both' => __('both, logged and non-logged users', 'woocommerce-p24'),
                    'logged_in' => __('logged users', 'woocommerce-p24'),
                    'logged_out' => __('non-logged users', 'woocommerce-p24')
                ],
            ],
            'one_click_enabled' => [
                'type' => 'checkbox',
                'title' => __('One Click Card Payment', 'woocommerce-p24'),
                'label' => __('Enable one click card payment', 'woocommerce-p24'),
                'description' => __('If enabled, prevents use of Click to Pay for logged users', 'woocommerce-p24'),
                'info' => '<svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('<h3>One Click Card Payment</h3>Enabling this option allows for card payments using a saved alias. This functionality is available only for logged-in buyers who have an account in the store. It becomes accessible after the customer\'s first payment, provided they have agreed to save their card details. <br/><strong>Before turning this option on, please contact P24 to enable the necessary services on your account for on-site card payments.</strong><br/><br/><em>Example view for the buyer:</em><br/><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_karta_oneclick.png" alt="One Click Card Payment" style="max-width: 400px">', 'woocommerce-p24'),
                'default' => 'no',
            ],
        ],
            $this->fee_settings(),
            [
                'styles' => [
                    'type' => 'code',
                    'title' => __('Styling options', 'woocommerce-p24'),
                    'default' => '{
                        "lang": "pl", 
                        "agreement": { 
                            "contentEnabled": { "enabled": false, "checkboxEnabled": false },
                            "TOSLanguage": "pl"
                        }
                    }',
                    'description' => sprintf(__('Widget styling settings in JSON format, detailed documentation available <a href="%s" target="_blank">here</a>', 'woocommerce-p24'), 'https://developers.przelewy24.pl/extended/index.php?pl#tag/Inicjalizacja-formularza/Stylowanie-oraz-opcje-formularza'),
                ]
            ]);
    }

    public function save_card_reference(Transaction $transaction): void
    {
        $order = $transaction->order;
        $save_reference = $order->get_meta('_p24_save_card', true);

        if ($save_reference) {
            $resource = new Card_Resource();
            $response = $resource->get_info($transaction->get_order_id());

            if (!empty($response['data'])) {
                $card = new Card_Simple($response['data']);
                Reference::save_reference($card, $order);
                $order->delete_meta_data('_p24_save_card');
            }
        }
    }

    private function sanitize(array $data): array
    {
        $sanitizer = new Sanitizer($data, [
            'regulation' => FILTER_VALIDATE_BOOLEAN,
            'type' => Sanitizer::sanitize_key_as_filter(),
            'save' => FILTER_VALIDATE_BOOLEAN,
            'oneclick' => FILTER_SANITIZE_NUMBER_INT,
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

        if (!in_array($data['type'], ['card-data', 'one-click'])) {
            $validator->add_error('Wrong transaction type');
        }

        if ($data['type'] === 'one-click' && empty($data['oneclick'])) {
            $validator->add_error('One click id not provided');
        }

        if ($validator->has_errors()) {
            throw new \Exception($validator->get_first_error());
        }
    }

    public function payment(\WC_Order $order, array $payment_data = []): array
    {
        $card_data = $payment_data;
        $payment_data = $this->sanitize($payment_data);
        $this->validate($payment_data);

        $result = [
            'redirect' => $order->get_checkout_order_received_url(),
            'status' => 'pending'
        ];

        $type = $payment_data['type'];
        $save = $payment_data['save'];
        $accept_rules = $payment_data['regulation'];

        $method_id = Gateways_Manager::get_method_id_matching_group(Payment_Methods::CARD_PAYMENT_ALT, $order->get_total());

        $transaction = new Transaction($order->get_id(), $method_id, $accept_rules);
        $card_payment_type = Transaction::CARD_STANDARD;

        if (Helper::order_has_subscription_product($transaction->order)) {
            $card_payment_type = Transaction::CARD_INITIAL;
        }

        if ($save && $type != 'one-click') {
            $order->update_meta_data('_p24_save_card', true);
            $order->save_meta_data();
            $card_payment_type = Transaction::CARD_INITIAL;
        }

        switch ($type) {
            case 'card-data':
                $transaction->set_card(new Card_Simple($card_data), $card_payment_type);

                break;
            case 'one-click':
                if ($this->one_click_enabled()) {
                    $one_click_id = (int)$payment_data['oneclick'];

                    $reference = Reference::get_and_check($one_click_id, $order->get_customer_id());

                    if ($reference) {
                        $transaction->set_one_click(Transaction::ONE_CLICK_CARD);
                        $transaction->set_card($reference->to_card_simple(), Transaction::CARD_ONE_CLICK);
                    }
                }
                break;
        }

        $transaction->register();
        $transaction->charge_card(true, true);

        $result['token'] = $transaction->get_token();
        $result['success'] = true;

        return $result;
    }

    public function process_payment_rest(PaymentContext $context, PaymentResult &$payment_result): void
    {
        if ($context->payment_method === Core::CARD_IN_SHOP_METHOD) {
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

        $config = Config::get_instance();
        $settings = Card_Helper::generate_keys_for_tokenization();
        $options = json_decode($this->get_option('styles'));
        $settings['options'] = $options ?: [];

        $one_click_enabled = $this->one_click_enabled() && is_user_logged_in();
        $one_click_items = [];

        if ($get_one_clicks && $one_click_enabled) {
            $current_user = wp_get_current_user();

            $one_click_items = Reference::findAll(['where' =>
                ["user.ID = %d AND t.type != 'blik'", (int)$current_user->ID]
            ]);

            $one_click_items = array_map(function ($card) {
                return [
                    'id' => $card->get_id(),
                    'type' => $card->get_type(),
                    'last_digits' => $card->get_info(),
                    'valid_to' => $card->get_valid_to()->format('m/Y'),
                    'logo' => $card->get_icon()
                ];
            }, $one_click_items);
        }

        $recurring = false;
        $order_id = isset(WC()->session) ? WC()->session->get('store_api_draft_order') : null;
        $order = $order_id ? wc_get_order($order_id) : false;

        if ($order instanceof \WC_Order) {
            $recurring = Helper::order_has_subscription_product($order);
        }

        return [
            'mode' => $config->get_mode_prefix(),
            'lang' => Helper::get_language(),
            'config' => $settings,
            'i18n' => [
                'label' => [
                    'save' => __('Save card reference for future payments', 'woocommerce-p24'),
                    'submit' => __('Pay by card', 'woocommerce-p24'),
                    'regulation' => sprintf(__('I hereby state that I have read the <a href="%s" target="_blank">regulations</a> and <a href="%s" target="_blank">information obligation</a> of "Przelewy24"', 'woocommerce-p24'), Core::get_rules_url(), Core::get_tos_url()),
                ],
                'error' => [
                    'general' => _x('Unknown error has occurred', 'Card in the shop', 'woocommerce-p24'),
                    'rules' => __('You have to accept the terms and conditions for using the chosen method', 'woocommerce-p24'),
                ],
                'use_saved' => __('Use saved payment methods - on-click', 'woocommerce-p24'),
                'or' => __('or use new card details', 'woocommerce-p24'),
            ],
            'recurring' => $recurring,
            'oneClick' => [
                'enabled' => $one_click_enabled,
                'items' => $one_click_items
            ],
            'clickToPay' => [
                'enabled' => $this->click_to_pay_enabled(),
                'email' => ''
            ]
        ];
    }
}
