<?php

namespace WC_P24\API\Resources;

if (!defined('ABSPATH')) {
    exit;
}

class Paywall_Resource extends Resource
{
    public const PAYWALL_ENDPOINT = 'trnRequest/{token}';

    public function get_paywall_url(string $transactionToken): string
    {
        $endpoint = str_replace('{token}', $transactionToken, self::PAYWALL_ENDPOINT);

        return $this->get_api_client()->get_api_url() . $endpoint;
    }
}
