<?php

namespace WC_P24\Migrations;

use WC_P24\Models\Database\Reference;

if (!defined('ABSPATH')) {
    exit;
}

class References_Migration
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
        dbDelta('CREATE TABLE IF NOT EXISTS ' . Reference::table_name() . ' (
    			id BIGINT NOT NULL AUTO_INCREMENT,
    			user_id BIGINT NOT NULL,
    			ref_id VARCHAR(64) NOT NULL,
    			valid_to DATE NOT NULL,
    			info VARCHAR(4) DEFAULT NULL, 
    			type VARCHAR(32) DEFAULT NULL,
    			hash VARCHAR(64) DEFAULT NULL,
    			status TINYINT(1) DEFAULT NULL,
    			PRIMARY KEY (id),
    			INDEX (user_id),
    			INDEX (ref_id),
    			INDEX (valid_to),
    			INDEX (hash)
        );');
    }
}
