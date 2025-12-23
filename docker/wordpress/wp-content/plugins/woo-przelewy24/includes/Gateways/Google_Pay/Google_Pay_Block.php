<?php

namespace WC_P24\Gateways\Google_Pay;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Core;
use WC_P24\Utilities\Base_Gateway_Block;

final class Google_Pay_Block extends Base_Gateway_Block
{
    public function get_name()
    {
        return Core::GOOGLE_PAY_IN_SHOP_METHOD;
    }

    protected function get_script_path(): string
    {
        return WC_P24_PLUGIN_URL . 'assets/blocks/block-p24-google-pay/block-p24-google-pay.js';
    }
}
