<?php

namespace WC_P24\Subscriptions;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Subscriptions\Product\Initiator as Product_Initiator;
use WC_P24\Utilities\Module;

class Subscriptions extends Module
{
    const ENABLE_KEY = 'p24_subscription_enabled';
    const RENEW_KEY = 'p24_subscription_renew';

    public function __construct()
    {
        parent::__construct();

        $this->settings = new Settings();

        new Product_Initiator();

        if ($this->is_enabled()) {
            new Filter_Gateways();
            new Export();
            new Manager();
            new Client_Page();
        }
    }

    public static function is_enabled(): bool
    {
        return get_option(self::ENABLE_KEY, 'no') === 'yes';
    }

    public static function days_to_renew(): int
    {
        return (int)get_option(self::RENEW_KEY, 3);
    }

    protected function on_client(): void
    {
    }

    protected function on_admin(): void
    {
        new Admin_Page();
    }
}
