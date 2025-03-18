<?php

namespace WC_P24\Gateways\Card;

if (!defined('ABSPATH')) {
    exit;
}

use WC_Order;
use WC_P24\Models\Database\Reference;
use WC_P24\Models\Simple\Card_Notification;
use WC_P24\Subscriptions\Helper;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Webhook;
use WC_Payment_Gateway;

class Card_Webhooks extends Webhook
{
    const PROCESS_CARD = 'process-card';
    const NOTIFICATION_CARD = 'notification-card';
    const ACTION_REGISTER_TRANSACTION_LEGACY = 'register-transaction';
    private WC_Payment_Gateway $gateway;

    public function __construct($gateway)
    {
        parent::__construct();
        $this->gateway = $gateway;
    }

    public function callback(): void
    {
        $action = $this->get_action();

        switch ($action) {
            case self::PROCESS_CARD;
                $this->process();
                break;
            case self::NOTIFICATION_CARD;
                $this->notification();
                break;
        }
    }

    private function process(): void
    {
        try {
            $input = $this->get_input();

            switch ($input['type']) {
                case self::ACTION_REGISTER_TRANSACTION_LEGACY:
                    $result = $this->register_card_transaction();
                    break;
            }

            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(['error' => true, 'message' => $e->getMessage()], 422);
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        exit;
    }

    private function notification(): void
    {
        try {
            if (empty($_GET[self::ORDER_ID_QUERY_KEY])) {
                throw new \Exception('Order ID not provided');
            }

            $order_id = (int)$_GET[self::ORDER_ID_QUERY_KEY];
            $order = wc_get_order($order_id);

            if (!$order instanceof WC_Order) {
                return;
            }

            $has_save_card = $order->get_meta('_p24_save_card');
            $has_subscription = $order->get_meta('_p24_has_subscription');

            $notification = new Card_Notification($this->get_input());

            if ($notification->has_error()) {
                $notification->reject_message && $order->add_order_note($notification->reject_message);
            } else if (($has_save_card || $has_subscription) && !empty($notification->card)) {
                $notification->card->set_status(Reference::STATUS_REGISTERED);
                $card_id = Reference::save_reference($notification->card, $order);

                if ($card_id && $has_subscription) {
                    Helper::activate_subscription($order, $card_id);
                }
            }

            $notification->message && $order->add_order_note($notification->message);

            $order->save();
        } catch (\Exception $e) {
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        exit;
    }

    private function register_card_transaction(): array
    {
        $payment_details = $this->get_payment_details();
        $order = $this->get_order();

        return $this->gateway->payment($order, $payment_details);
    }

    public static function get_process_card_url(): string
    {
        return self::setup_url(self::PROCESS_CARD);
    }

    public static function get_notification_card_url(string $order_id): string
    {
        return self::setup_url(self::NOTIFICATION_CARD, [
            self::ORDER_ID_QUERY_KEY => $order_id
        ]);
    }
}
