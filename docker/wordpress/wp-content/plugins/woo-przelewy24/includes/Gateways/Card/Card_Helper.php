<?php

namespace WC_P24\Gateways\Card;

use WC_P24\Config;
use WC_P24\Utilities\Encryption;

class Card_Helper
{
    public static function generate_keys_for_tokenization(): array
    {
        $config = Config::get_instance();
        $session_id = Encryption::generate_session_id(time());

        $signature = Encryption::generate_signature([
            'merchantId' => $config->get_merchant_id(),
            'sessionId' => $session_id,
            'crc' => $config->get_crc_key()
        ]);

        return [
            'merchant_id' => $config->get_merchant_id(),
            'session_id' => $session_id,
            'signature' => $signature
        ];
    }
}
