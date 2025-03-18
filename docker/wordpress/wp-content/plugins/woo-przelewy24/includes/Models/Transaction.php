<?php

namespace WC_P24\Models;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\API\Resources\Blik_Resource;
use WC_P24\API\Resources\Card_Resource;
use WC_P24\API\Resources\Paywall_Resource;
use WC_P24\API\Resources\Resource;
use WC_P24\API\Resources\Transaction_Resource;
use WC_P24\Config;
use WC_P24\Gateways\Card\Card_Webhooks;
use WC_P24\Gateways\General_Webhooks;
use WC_P24\Helper;
use WC_P24\Models\Database\Reference;
use WC_P24\Models\Simple\Card_Simple;
use WC_P24\Models\Simple\Notification;
use WC_P24\Multicurrency\Multicurrency;
use WC_P24\Utilities\Encryption;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Payment_Methods;
use WC_P24\Utilities\User_Data;

class Transaction

{
    const TOKEN_KEY = '_p24_token';
    const SESSION_ID_KEY = '_p24_session_id';
    const TRACE_ID_KEY = '_p24_trace_id';
    const ORDER_ID_KEY = '_p24_order_id';
    const CARD_STANDARD = 'standard';
    const CARD_INITIAL = 'initial';
    const CARD_ONE_CLICK = '1click';
    const CARD_RECURRING = 'recurring';
    const ONE_CLICK_CARD = 'one-click-card';
    const ONE_CLICK_BLIK = 'one-click-blik';

    const APPLE_PAY = 'applepay';
    const GOOGLE_PAY = 'googlepay';

    public $order;
    protected int $amount;
    protected string $currency;
    protected ?string $token = null;
    protected ?int $order_id = null;
    protected Config $config;
    protected Resource $client;
    protected ?string $session_id = null;
    protected ?string $trace_id = null;
    protected ?string $return_url = null;
    private int $method = 0;
    private ?Card_Simple $card = null;
    private ?Reference $reference = null;
    private string $card_payment_type = self::CARD_STANDARD;

    private bool $save_blik = false;
    private ?string $one_click = null;
    private ?string $payload = null;
    private ?string $payload_type = null;

    private bool $accept_rules = false;

    public function __construct($order_id, int $method = Payment_Methods::PAYWALL_PAYMENT, bool $accept_rules = false)
    {
        $this->order = wc_get_order($order_id);

        if (!$this->order) {
            throw new \Exception('Order does exist');
        }

        $this->method = $method;
        $this->client = new Transaction_Resource();
        $this->config = Config::get_instance();
        $this->currency = $this->order->get_currency();

        $this->session_id = $this->order->get_meta(self::SESSION_ID_KEY);
        $this->trace_id = $this->order->get_meta(self::TRACE_ID_KEY);
        $this->order_id = (int)$this->order->get_meta(self::ORDER_ID_KEY, true);
        $this->token = $this->order->get_meta(self::TOKEN_KEY, true);
        $this->accept_rules = $accept_rules;

        $this->amount = Helper::to_lowest_unit($this->order->get_total());
    }

    public function set_card(Card_Simple $card, string $payment_type = self::CARD_STANDARD): void
    {
        $this->card = $card;
        $this->card_payment_type = $payment_type;
    }

    public function set_payload(string $payload, string $type): void
    {
        $this->payload = $payload;
        $this->payload_type = $type;
    }

    public function set_reference(Reference $reference): void
    {
        $this->reference = $reference;
    }

    public function set_one_click(string $value): void
    {
        $this->one_click = $value;
    }

    public function set_save_blik(bool $save_blik): void
    {
        $this->save_blik = $save_blik;
    }

    public function set_session_id($value): void
    {
        $this->session_id = $value;
    }

    public function get_one_click(): ?string
    {
        return $this->one_click;
    }

    public function get_session_id(): ?string
    {
        if (!$this->session_id) {
            $this->set_session_id(Encryption::generate_session_id($this->order->get_id()));
        }

        return $this->session_id;
    }

    public function get_trace_id(): ?string
    {
        if (!$this->trace_id) {
            $this->trace_id = $this->order->get_meta(self::TRACE_ID_KEY);
        }

        return $this->trace_id;
    }

    public function get_order_id(): ?int
    {
        if (!$this->order_id) {
            $this->order_id = $this->order->get_meta(self::ORDER_ID_KEY, true);
        }

        return (int)$this->order_id;
    }

    public function get_token(): ?string
    {
        if (!$this->token) {
            $this->token = $this->order->get_meta(self::TOKEN_KEY, true);
        }

        return $this->token;
    }

    public function get_signature(): string
    {
        $session_id = $this->get_session_id();
        $amount = $this->amount;
        $currency = $this->currency;
        $merchant_id = $this->config->get_merchant_id();

        $signature = Encryption::generate_signature([
            'sessionId' => $session_id,
            'merchantId' => $merchant_id,
            'amount' => $amount,
            'currency' => $currency,
            'crc' => $this->config->get_crc_key()
        ]);

        return $signature;
    }

    public function set_additional_data(&$data)
    {
        $data['additional'] = [
            'PSU' => [
                'IP' => User_Data::get_user_ip(),
                'userAgent' => User_Data::get_user_agent()
            ]
        ];
    }

    public function get_transaction_data(): array
    {
        $signature = $this->get_signature();

        $data = [
            'merchantId' => $this->config->get_merchant_id(),
            'posId' => $this->config->get_merchant_id(),
            'sessionId' => $this->get_session_id(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => sprintf(__('Order no.: %s', 'woocommerce-p24'), $this->order->get_order_number()),
            'email' => $this->order->get_billing_email(),
            'country' => $this->order->get_billing_country(),
            'language' => Helper::get_language(),
            'urlReturn' => $this->return_url,
            'urlStatus' => $this->get_url_status(),
            'client' => Helper::get_customer_name($this->order),
            'address' => $this->order->get_billing_address_1(),
            'zip' => $this->order->get_billing_postcode(),
            'city' => $this->order->get_billing_city(),
            'regulationAccept' => $this->accept_rules,
            'encoding' => 'UTF-8',
            'sign' => $signature
        ];

        if (!empty($this->method)) {
            $data['method'] = $this->method;
        }

        if (!empty($this->card)) {
            $data['cardData'] = [
                'means' => [
                    'referenceNumber' => [
                        'id' => $this->card->get_ref_id()
                    ]
                ],
                'transactionType' => $this->card_payment_type,
            ];

            // one click payment
            if (in_array($this->card_payment_type, [self::CARD_ONE_CLICK])) {
                $data['methodRefId'] = $this->card->get_ref_id();
            }

            // save one click
            if (in_array($this->card_payment_type, [self::CARD_INITIAL])) {
                $save_card_url = Card_Webhooks::get_notification_card_url($this->order->get_id());
                $data['urlNotify'] = $save_card_url;
                $data['urlCardPaymentNotification'] = $save_card_url;
            }
        }

        // Google Pay and Apple Pay
        if ($this->payload && $this->payload_type) {
            $data['cardData'] = [
                'means' => [
                    'xPayPayload' => [
                        'payload' => $this->payload,
                        'type' => $this->payload_type
                    ]
                ],
                'transactionType' => 'standard',
            ];
        }

        // BLIK add IP and user agent to transaction
        if ($this->method == Payment_Methods::BLIK_PAYMENT) {
            $this->set_additional_data($data);
        }

        if ($this->method == Payment_Methods::BLIK_PAYMENT && $this->save_blik) {
            $data['referenceRegister'] = true;
        }

        // BLIK one click
        if ($this->one_click === self::ONE_CLICK_BLIK && !empty($this->reference)) {
            $data['methodRefId'] = $this->reference->get_ref_id();
        }

        return $data;
    }

    public function get_verification_data(): array
    {
        $signature = Encryption::generate_signature([
            'sessionId' => $this->get_session_id(),
            'orderId' => $this->get_order_id(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'crc' => $this->config->get_crc_key()
        ]);

        return [
            'merchantId' => $this->config->get_merchant_id(),
            'posId' => $this->config->get_merchant_id(),
            'sessionId' => $this->get_session_id(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'orderId' => $this->get_order_id(),
            'sign' => $signature
        ];
    }

    public function get_url_status(): string
    {
        return General_Webhooks::get_status_url($this->order->get_id());
    }

    public function get_paywall_url(): string
    {
        $client = new Paywall_Resource();

        return $client->get_paywall_url($this->get_token());
    }

    public function reset_session()
    {
        $this->session_id = null;
        $this->trace_id = null;
        $this->token = null;
    }

    public function register(): void
    {
        $url = Encryption::encrypt($this->order->get_checkout_order_received_url());
        $this->return_url = General_Webhooks::get_return_url($url);
        $this->reset_session();
        $data = $this->get_transaction_data();

        $response = $this->client->register_transaction($data);

        if (empty($response['data']['token'])) {
            throw new \Exception($response['error']);
        }

        $this->token = $response['data']['token'];
        $this->order->update_meta_data(self::SESSION_ID_KEY, $this->session_id);
        $this->order->update_meta_data(self::TOKEN_KEY, $this->token);
        $this->order->add_order_note(sprintf(__('Register transaction<br/><strong>Session ID</strong>: %s <br/><strong>Token</strong>: %s', 'woocommerce-p24'), $this->session_id, $this->token));

        if ($this->card && $this->card_payment_type === self::CARD_INITIAL) {
            $this->order->update_meta_data(self::TRACE_ID_KEY, $this->token);
        }

        $this->order->save();
    }

    private function verify_sign(Notification $notification): bool
    {
        $config = Config::get_instance();

        $generated = Encryption::generate_signature([
            'merchantId' => $config->get_merchant_id(),
            'posId' => $config->get_merchant_id(),
            'sessionId' => $this->session_id,
            'amount' => $notification->amount,
            'originAmount' => $notification->origin_amount,
            'currency' => $notification->currency,
            'orderId' => $this->order_id,
            'methodId' => $notification->method_id,
            'statement' => $notification->statement,
            'crc' => $config->get_crc_key()
        ]);

        return $generated === $notification->sign;
    }

    public function verify(Notification $notification): void
    {
        if (in_array($this->order->get_status(), ['completed', 'processing'])) exit;

        if ($notification->session_id !== $this->get_session_id()) {
            throw new \Exception('Session ID mismatch.');
        }

        if (!$notification->order_id) {
            throw new \Exception('Order ID not provided.');
        }

        Multicurrency::setup($notification->currency);

        $this->save_order_id($notification->order_id);

        $response = $this->client->verify_transaction($this->get_verification_data());

        if (!empty($response['data']['status']) && $response['data']['status'] == 'success') {
            $this->order->add_order_note(sprintf(__('Payment verified<br/><strong>P24 order ID:</strong> %s', 'woocommerce-p24'), $notification->order_id));
            $this->order->payment_complete($notification->statement);
        } else {
            throw new \Exception('Payment cannot be verified.');
        }

        $this->order->save();
    }

    protected function save_order_id(int $order_id): void
    {
        $this->order_id = $order_id;
        $this->order->update_meta_data(self::ORDER_ID_KEY, $this->order_id);

        $this->order->save();
    }

    public function charge_blik_by_code(string $code): void
    {
        $client = new Blik_Resource();

        $response = $client->charge_by_code([
            'token' => $this->get_token(),
            'blikCode' => $code
        ]);

        if (empty($response['data']['orderId'])) {
            throw new \Exception('BLIK charge by code failed');
        }

        $this->save_order_id($response['data']['orderId']);
    }

    public function charge_blik_by_alias(): void
    {
        if ($this->one_click === self::ONE_CLICK_BLIK) {
            $client = new Blik_Resource();

            $response = $client->charge_by_alias([
                'token' => $this->get_token(),
                'type' => 'alias'
            ]);

            if (isset($response['code']) && $response['code'] == 51) {
                if(empty($response['error']['alternativeKeys'])) {
                    throw new \Exception('BLIK charge by alias failed');
                }

                [$alt] = $response['error']['alternativeKeys'];

                $response = $client->charge_by_alias([
                    'token' => $this->get_token(),
                    'type' => 'alternativeKey',
                    'alternativeKey' => $alt['alias']
                ]);
            }

            if (empty($response['data']['orderId'])) {
                throw new \Exception('BLIK charge by alias failed');
            }

            $this->save_order_id($response['data']['orderId']);
        }
    }

    public function charge_card($with_3ds = false, $is_one_click = false): void
    {
        if ($is_one_click && $this->one_click !== self::ONE_CLICK_CARD) {
            return;
        }

        $token = $this->get_token();
        $client = new Card_Resource();
        $response = $with_3ds ? $client->charge_with_3ds($token) : $client->charge($token);

        if (empty($response['data']['orderId'])) {
            throw new \Exception('Card charge failed');
        }

        $this->save_order_id($response['data']['orderId']);
    }

    public function do_payment()
    {
        $response = $this->client->do_payment([
            'requestId' => wp_generate_uuid4(),
            'token' => $this->get_token()
        ]);

        if (empty($response['token'])) {
            throw new \Exception('Card charge failed');
        }
    }
}
