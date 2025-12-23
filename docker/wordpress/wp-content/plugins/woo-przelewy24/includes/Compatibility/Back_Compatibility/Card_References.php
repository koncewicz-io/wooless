<?php

namespace WC_P24\Compatibility\Back_Compatibility;

use ArrayObject;
use WC_P24\Models\Database\Reference;
use WC_P24\Models\Simple\Card_Simple;

class Card_References
{
    private $db;
    private string $old_table;

    private ?ArrayObject $data = null;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
        $this->old_table = $this->db->prefix . 'woocommerce_p24_data';

        $this->get_settings();
    }

    public function get_settings($fetch = true): ?ArrayObject
    {
        if (empty($this->data)) {
            $data = new ArrayObject();
            $data->result = [];

            if ($fetch) {
                $data->results = $this->db->get_results('SELECT * FROM ' . $this->old_table . " WHERE data_type = 'user_cards'");

                foreach ($data->results as &$reference) {
                    $reference->card_data = json_decode($reference->custom_value);
                }
            }

            $results = $this->db->get_results('SELECT COUNT(*) as quantity FROM ' . $this->old_table . " WHERE data_type = 'user_cards'");
            $data->quantity = isset($results[0]) ? (int) $results[0]->quantity : 0;

            $this->data = $data;
        }

        return $this->data;
    }

    public function import(): void
    {
        $data = $this->get_settings();

        foreach ($data->results as $reference) {
            $customer_id = $reference->data_id;
            $hash = $reference->custom_key;
            $value = $reference->card_data;

            $card = new Card_Simple();
            $card->set_ref_id($value->ref);
            $card->set_valid_to($value->exp);
            $card->set_last_digits($value->mask);
            $card->set_type($value->type);
            $card->set_hash($hash);

            Reference::save_reference($card, null, $customer_id);
        }
    }
}
