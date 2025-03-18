<?php

namespace WC_P24\Utilities;

use WC_P24\Core;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Webhook
{
    const ACTION_QUERY_KEY = 'action';
    const ORDER_ID_QUERY_KEY = 'order-id';

    private ?array $input = null;

    public function __construct()
    {
        add_action('woocommerce_api_' . Core::WEBHOOK_NAMESPACE, [$this, 'callback']);
    }

    public abstract function callback(): void;

    protected function get_action(): ?string
    {
        $result = null;

        if (!empty($_GET[self::ACTION_QUERY_KEY])) {
            $result = sanitize_key($_GET[self::ACTION_QUERY_KEY]);
        }

        return $result;
    }

    protected function get_order_id(): int
    {
        if (empty($_GET[self::ORDER_ID_QUERY_KEY])) {
            throw new \Exception('Order ID not provided');
        }

        return (int)$_GET[self::ORDER_ID_QUERY_KEY];
    }

    protected function get_order(): \WC_Order
    {
        $input = $this->get_input();
        $order_id = (int)$input['orderId'] ?? null;
        $order_key = $input['orderKey'] ?? null;
        $is_legacy = $input['checkout'] == 'legacy';

        $order_id = $is_legacy ? $order_id : WC()->session->get('store_api_draft_order');

        $order = wc_get_order($order_id);

        if (!$order) {
            throw new \Exception('Order not found');
        }

        if ($is_legacy && $order->get_order_key() !== $order_key) {
            throw new \Exception('Invalid order key');
        }

        return $order;
    }

    protected function get_payment_details(): array
    {
        $input = $this->get_input();
        $payment_details = $input['paymentDetails'];

        if ($payment_details['oneClick']){
            $payment_details['oneclick'] = $payment_details['oneClick'];
        }

        if (empty($payment_details)) {
            throw new \Exception('Payment details not provided');
        }

        return $payment_details;
    }

    protected function get_input(): array
    {
        if (empty($this->input)) {
            $input = file_get_contents('php://input');
            $this->input = json_decode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            Logger::log(['input' => $this->input], Logger::DEBUG, true);
        }

        return $this->input ?: [];
    }

    protected static function setup_url(string $action, array $args = []): string
    {
        $args = array_merge([self::ACTION_QUERY_KEY => $action], $args);

        return add_query_arg($args, WC()->api_request_url(Core::WEBHOOK_NAMESPACE));
    }
}
