<?php

namespace WC_P24\Gateways;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Config;
use WC_P24\Models\Refund;
use WC_P24\Models\Transaction;
use WC_P24\UI\Display;
use WC_P24\Utilities\Logger;
use WC_P24\Utilities\Payment_Methods;

trait Payment_Helpers
{
    public int $method = Payment_Methods::PAYWALL_PAYMENT;
    public string $group = 'przelewy24';
    public string $subgroup = '';
    public array $method_alt = [];

    public function is_enabled(): bool
    {
        return $this->enabled === 'yes';
    }

    public function get_title(): string
    {
        if (is_admin()) {
            $screen = get_current_screen();
            if ($screen && $screen->post_type === 'shop_order') {
                return $this->method_title;
            }
        }

        return parent::get_title();
    }


    public function get_transaction_url($order): string
    {
        $prefix = Config::get_instance()->is_live() ? '' : 'sandbox-';
        $token = $order->get_meta(Transaction::TOKEN_KEY, true);

        if ($token) {
            return 'https://' . $prefix . 'go.przelewy24.pl/trnStatus/' . $token;
        }

        return parent::get_transaction_url($order);
    }

    public function process_on_paywall($order_id, ?int $method = null, $accept_rules = false): array
    {
        $transaction = new Transaction($order_id, $method ?? $this->method, $accept_rules);
        $transaction->register();
        $return_url = get_post_meta($order_id, '_p24_return_url', true);

        return [
            'result'    => 'success',
            'redirect'  => $transaction->get_paywall_url(),
            'wl_return' => $return_url
        ];    }

    public function process_on_payment_url($order_id): array
    {
        $order = wc_get_order($order_id);

        return ['result' => 'success', 'redirect' => $order->get_checkout_payment_url(true)];
    }

    // A fake WP_Error must be returned in order to contract that the return is being processed due to the asynchronicity of the operation, on notification step the true refund will be completed
    public function process_refund($order_id, $amount = null, $reason = ''): \WP_Error
    {
        $message = '';
        $amount = (float)$amount;

        try {
            $refund = new Refund($order_id, $amount, $reason);
            $refund->register();
            $status = $refund->get_status();
            $message = $refund->refund_notes()[$refund->status];

            do_action('przelewy24_after_refund_process', $refund->order, $amount, $reason, $status, $this->id);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            Logger::log($e->getMessage(), Logger::EXCEPTION);
        }

        return new \WP_Error('refund', $message);
    }

    public function admin_options()
    {
        $display = new Display();
        $display->set_fields($this->get_form_fields(), $this);

        $display->render();
    }
}
