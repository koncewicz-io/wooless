<?php

namespace WC_P24\API\Resources;


use WC_P24\API\Api_Client;

if ( !defined('ABSPATH') ) {
    exit;
}

class Payment_Methods_Resource extends Resource
{
    public const PAYMENT_METHODS_ENDPOINT = 'api/v1/payment/methods/{lang}';
    public const LANG_PL = 'pl';
    public const LANG_EN = 'en';

    public function get_payment_methods(?int $amount = null, ?string $currency = null, string $lang = self::LANG_PL): array {
        $endpoint = str_replace('{lang}', $lang, self::PAYMENT_METHODS_ENDPOINT);
        $query = [];

        if (null !== $amount) {
            $query['amount'] = $amount;
        }

        if (null !== $currency) {
            $query['currency'] = $currency;
        }

        return $this->get_api_client()->request($endpoint, Api_Client::METHOD_GET, null, $query);
    }
}
