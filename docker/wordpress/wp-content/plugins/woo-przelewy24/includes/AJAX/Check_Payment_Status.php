<?php

namespace WC_P24\Ajax;

class Check_Payment_Status
{
    public function __construct()
    {
        add_action('wp_ajax_check_payment_status', [$this, 'handle']);
        add_action('wp_ajax_nopriv_check_payment_status', [$this, 'handle']);
    }

    public function handle()
    {
        $order_id = absint($_GET['order_id'] ?? 0);

        if (!$order_id) {
            wp_send_json_error(['message' => 'Brak ID zamówienia']);
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(['message' => 'Zamówienie nie istnieje']);
        }

        $status = $order->get_status();
        error_log('P24 STATUS CHECK ORDER ' . $order_id . ': ' . $status);

        if (in_array($status, ['completed', 'processing', 'on-hold', 'p24_confirmed', 'p24_success', 'paid'], true)) {
            wp_send_json_success(['status' => 'paid']);
        }
        wp_send_json_success(['status' => 'pending']);
    }
}
