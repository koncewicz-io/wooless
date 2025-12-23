<?php

namespace WC_P24\Migrations;

use WC_P24\Models\Database\Reference;
use WC_P24\Models\Database\Subscription;

if (!defined('ABSPATH')) {
    exit;
}

class Subscriptions_Migration
{
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->create_tables();
    }

    private function create_tables()
    {
        dbDelta('CREATE TABLE IF NOT EXISTS ' . Subscription::table_name() . ' (
    			id BIGINT NOT NULL AUTO_INCREMENT,
    			user_id BIGINT NOT NULL,
    			product_id BIGINT NOT NULL,
    			card_id BIGINT DEFAULT NULL,
    			order_id BIGINT NOT NULL,
    			valid_to DATETIME NOT NULL,
    			status TINYINT NOT NULL,
    			checked_at DATETIME DEFAULT NULL,
    			PRIMARY KEY (id),
                CONSTRAINT fk_card_id_card FOREIGN KEY (card_id) REFERENCES ' . Reference::table_name() . '(id) ON UPDATE CASCADE,
                INDEX (user_id),
    			INDEX (product_id),
    			INDEX (card_id),
    			INDEX (order_id),
    			INDEX (valid_to)
		);');
    }
}
