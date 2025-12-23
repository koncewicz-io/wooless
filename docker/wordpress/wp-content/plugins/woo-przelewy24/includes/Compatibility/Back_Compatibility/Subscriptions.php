<?php

namespace WC_P24\Compatibility\Back_Compatibility;

use ArrayObject;
use DateTime;
use WC_P24\Models\Database\Reference;
use WC_P24\Models\Database\Subscription;
use WC_P24\Models\Simple\Card_Simple;

class Subscriptions
{
    private $db;
    private string $old_table;

    private ?ArrayObject $data = null;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
        $this->old_table = $this->db->prefix . 'woocommerce_p24_subscription';

        $this->get_settings();
    }

    public function get_settings($fetch = true): ?ArrayObject
    {
        if (empty($this->data)) {
            $data = new ArrayObject();

            $data->days = get_option('przelewy24_subscriptions_days', 3);
            $data->enabled = get_option('przelewy24_subscriptions_active', 'no') === 'yes';

            if ($fetch) {
                $data->results = $this->db->get_results('SELECT * FROM ' . $this->old_table);
            }

            $results = $this->db->get_results('SELECT COUNT(*) as quantity FROM ' . $this->old_table);
            $data->quantity = isset($results[0]) ? (int) $results[0]->quantity : 0;

            $this->data = $data;
        }

        return $this->data;
    }

    public function import(): void
    {
        $data = $this->get_settings();

        if ($data->enabled) {
            update_option('p24_subscription_enabled', 'yes');
        }

        if ($data->days) {
            update_option('p24_subscription_renew', (int)$data->days);
        }

        if (empty($data->results)) return;

        foreach ($data->results as $result) {
            if (empty($result->card_ref)) continue;

            $card = new Card_Simple();
            $card->set_ref_id((string)$result->card_ref);
            $card->set_info('');

            $id = Reference::save_reference($card, null, $result->user_id);
            if (!$id) continue;

            $subscriptions = Subscription::findAll(['where' =>
                ['t.user_id = %d AND t.product_id = %d AND t.order_id = %d AND t.card_id = %d', $result->user_id, $result->product_id, $result->last_order_id, $id]
            ]);

            if (!empty($subscriptions)) continue;

            $subscription = new Subscription();
            $subscription->set_customer_id($result->user_id);
            $subscription->set_product_id($result->product_id);
            $subscription->set_order_id($result->last_order_id);
            $subscription->set_valid_to(new DateTime($result->valid_to));
            $subscription->set_checked_at(new DateTime($result->last_checked));
            $subscription->set_card_id($id);

            $status = Subscription::STATUS_ACTIVE;

            if ($result->extend == '0') {
                $status = Subscription::STATUS_PROCESSING;
            }

            $subscription->set_status($status);
            $subscription->save();
        }
    }
}
