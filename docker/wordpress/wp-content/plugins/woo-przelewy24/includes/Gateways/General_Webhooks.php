<?php

namespace WC_P24\Gateways;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Gateways\Blik\GooglePay_Webhooks;
use WC_P24\Models\Refund;
use WC_P24\Models\Simple\Notification;
use WC_P24\Models\Simple\Refund_Notification;
use WC_P24\Models\Transaction;
use WC_P24\Utilities\Encryption;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Webhook;

class General_Webhooks extends Webhook
{
    const NOTIFICATION_TRANSACTION = 'transaction';
    const NOTIFICATION_REFUND = 'refund';
    const HANDLE_RETURN = 'return';
    const URL_QUERY_KEY = 'url';

    public function callback(): void
    {
        $action = $this->get_action();

        switch ($action) {
            case self::NOTIFICATION_TRANSACTION:
                $this->verify_transaction();
                break;
            case self::NOTIFICATION_REFUND:
                $this->verify_refund();
                break;
            case self::HANDLE_RETURN:
                $this->handle_return();
                break;
        }
    }

    private function handle_return(): void
    {
        if (empty($_GET[self::URL_QUERY_KEY])) {
            wp_safe_redirect(site_url());
            exit;
        }

        try {
            $encrypted_url = $_GET[self::URL_QUERY_KEY];
            $url = Encryption::decrypt($encrypted_url);

            wp_safe_redirect($url);
        } catch (\Exception $e) {
            wp_safe_redirect(site_url());
        }

        exit;
    }

    private function verify_transaction(): void
    {
        try {
            $order_id = $this->get_order_id();
            $notification = new Notification($this->get_input());
            $transaction = new Transaction($order_id);

            Logger::log('[P24] Notification received for order ' . $order_id);
            $transaction->verify($notification);
            Logger::log('[P24] Verify executed for order ' . $order_id);

            do_action('przelewy24_after_verify_transaction', $transaction);

            status_header(200);
            echo 'OK';
        } catch (\Exception $e) {
            Logger::log('[P24 ERROR] ' . $e->getMessage(), Logger::EXCEPTION);
            status_header(500);
            echo 'ERROR';
        }

        exit;
    }


    private function verify_refund(): void
    {
        try   {
            $order_id = $this->get_order_id();
            $notification = new Refund_Notification($this->get_input());
            $refund = new Refund($order_id);
            $refund->verify($notification);

            do_action('przelewy24_after_verify_refund', $refund);
        } catch (\Exception $e) {
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        exit;
    }

    public static function get_status_url(string $order_id): string
    {
        return self::setup_url(self::NOTIFICATION_TRANSACTION, [
            self::ORDER_ID_QUERY_KEY => $order_id
        ]);
    }

    public static function get_refund_url(string $order_id): string
    {
        return self::setup_url(self::NOTIFICATION_REFUND, [
            self::ORDER_ID_QUERY_KEY => $order_id
        ]);
    }

    public static function get_return_url(string $url): string
    {
        return self::setup_url(self::HANDLE_RETURN, [
            self::URL_QUERY_KEY => $url
        ]);
    }
}
