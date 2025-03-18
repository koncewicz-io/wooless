<?php

namespace WC_P24\Migrations;

use WC_P24\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_Manager
{
    public function __construct()
    {
        $version = Core::check_version();

        if ($version) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            new References_Migration();
            new Subscriptions_Migration();

            update_option(Core::INSTALLED_VERSION, $version, true);
        }
    }
}
