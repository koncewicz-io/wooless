<?php

namespace WC_P24\Gateways\Card;

if (!defined('ABSPATH')) {
    exit;
}

use WC_P24\Utilities\Base_Gateway_Block;

final class Card_Block extends Base_Gateway_Block
{
    protected function get_script_path(): string
    {
        return WC_P24_PLUGIN_URL . 'assets/blocks/block-p24-card/block-p24-card.js';
    }
}
