<?php

namespace WC_P24\Emails;

if (!defined('ABSPATH')) {
    exit;
}

class SendMailToAdminOnNewOrder
{
    public function __construct()
    {
        add_action('woocommerce_new_order', [$this, 'trigger'], 20, 1);
    }

    public function trigger($order_id): void
    {
        $enabled = get_option('p24_send_mail_to_admin_on_new_order', 'no');
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('[P24] wc_get_order failed');
            return;
        }

        if ($enabled === 'yes') {
            $emails = \WC()->mailer()->get_emails();
            if (!empty($emails['WC_Email_New_Order'])) {
                $emails['WC_Email_New_Order']->trigger($order->get_id());
            }
        }
    }
}
