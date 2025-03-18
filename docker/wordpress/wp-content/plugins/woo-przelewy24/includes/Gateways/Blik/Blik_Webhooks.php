<?php

namespace WC_P24\Gateways\Blik;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Models\Database\Reference;
use WC_P24\Models\Simple\Reference_Notification;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Webhook;
use WC_Payment_Gateway;


class Blik_Webhooks extends Webhook
{
    const PROCESS_BLIK = 'process-blik';
    const NOTIFICATION_BLIK_ALIAS = 'notification-blik-alias';
    const ACTION_REGISTER_TRANSACTION_LEGACY = 'register-transaction';
    const ACTION_GET_ORDER_STATUS = 'get-order-status';
    const ACTION_GET_ALIAS_STATUS = 'get-alias-status';
    private WC_Payment_Gateway $gateway;

    public function __construct($gateway)
    {
        parent::__construct();
        $this->gateway = $gateway;
    }

    public function callback(): void
    {
        switch ($this->get_action()) {
            case self::PROCESS_BLIK;
                $this->process();
                break;
            case self::NOTIFICATION_BLIK_ALIAS;
                $this->notification_alias();
                break;
        }
    }

    private function process(): void
    {
        try {
            $input = $this->get_input();

            switch ($input['type']) {
                case self::ACTION_REGISTER_TRANSACTION_LEGACY:
                    $result = $this->register_blik_transaction();
                    break;
                case self::ACTION_GET_ORDER_STATUS:
                    $result = $this->get_order_status();
                    break;
                case self::ACTION_GET_ALIAS_STATUS:
                    $result = $this->get_alias_status();
                    break;
            }

            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(['error' => true, 'message' => $e->getMessage()], 422);
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        exit;
    }

    private function notification_alias(): void
    {
        $notification = new Reference_Notification($this->get_input());

        if (!$notification->validate()) return;

        [$reference] = Reference::findAll(['where' => [
            't.status != %d AND t.ref_id = %s', Reference::STATUS_REGISTERED, $notification->reference
        ]]);

        if (!$reference) return;

        $order_id = $reference->get_hash();
        $reference->set_valid_to($notification->expiration);
        $reference->set_status($notification->status);
        $reference->set_hash($notification->reference);

        if (is_numeric($order_id)) {
            $order = wc_get_order($order_id);

            if ($order && $order->get_meta('_p24_save_blik') == $reference->get_id()) {
                $order->update_meta_data('_p24_save_blik', 'completed');
                $order->save();
            }
        }

        $reference->save();
    }

    private function register_blik_transaction(): array
    {
        $payment_details = $this->get_payment_details();
        $order = $this->get_order();

        return $this->gateway->payment($order, $payment_details);
    }

    private function get_order_status(): array
    {
        $result = [];
        $order = $this->get_order();

        if (in_array($order->get_status(), ['processing', 'completed'])) {
            $result['completed'] = true;
            $result['success'] = true;
            $result['redirect'] = $order->get_checkout_order_received_url();
        } else {
            $result['pending'] = true;
        }

        return $result;
    }

    private function get_alias_status(): array
    {
        $result = [];
        $order = $this->get_order();

        $completed = $order->get_meta('_p24_save_blik');

        if ($completed == 'completed') {
            $result['completed'] = true;
            $result['success'] = true;
            $result['redirect'] = $order->get_checkout_order_received_url();
        }

        return $result;
    }

    public static function get_process_blik_url(): string
    {
        return self::setup_url(self::PROCESS_BLIK);
    }

    public static function get_notification_alias_url(): string
    {
        return self::setup_url(self::NOTIFICATION_BLIK_ALIAS);
    }
}
