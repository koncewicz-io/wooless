<?php

namespace WC_P24\Gateways\Online_Payments;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Core;
use WC_P24\Utilities\Base_Gateway_Block;

final class Online_Payments_Block extends Base_Gateway_Block
{
    public function get_name()
    {
        return Core::MAIN_METHOD;
    }

    protected function get_script_path(): string
    {
        return WC_P24_PLUGIN_URL . 'assets/blocks/block-p24-online-payments/block-p24-online-payments.js';
    }
}
