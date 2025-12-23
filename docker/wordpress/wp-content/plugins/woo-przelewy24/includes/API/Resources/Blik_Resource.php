<?php

namespace WC_P24\API\Resources;

use WC_P24\API\Api_Client;

if (!defined('ABSPATH')) {
    exit;
}

class Blik_Resource extends Resource
{
    public const BLIK_CHARGE_BY_CODE_ENDPOINT = 'api/v1/paymentMethod/blik/chargeByCode';
    public const BLIK_CHARGE_BY_ALIAS_ENDPOINT = 'api/v1/paymentMethod/blik/chargeByAlias';
    public const BLIK_GET_ALIASES_ENDPOINT = 'api/v1/paymentMethod/blik/getAliasesByEmail/{email}';
    public const BLIK_GET_ALIASES_CUSTOM_ENDPOINT = 'api/v1/paymentMethod/blik/getAliasesByEmail/{email}/custom';

    public function charge_by_code(array $data): array
    {
        return $this->get_api_client()->request(
            self::BLIK_CHARGE_BY_CODE_ENDPOINT,
            Api_Client::METHOD_POST,
            $data
        );
    }

    public function charge_by_alias(array $data): array
    {
        return $this->get_api_client()->request(
            self::BLIK_CHARGE_BY_ALIAS_ENDPOINT,
            Api_Client::METHOD_POST,
            $data
        );
    }

    public function get_aliases(string $email): array
    {
        $endpoint = str_replace('{email}', $email, self::BLIK_GET_ALIASES_ENDPOINT);

        return $this->get_api_client()->request(
            $endpoint,
            Api_Client::METHOD_GET,
        );
    }

    public function get_aliases_custom(string $email): array
    {
        $endpoint = str_replace('{email}', $email, self::BLIK_GET_ALIASES_CUSTOM_ENDPOINT);

        return $this->get_api_client()->request(
            $endpoint,
            Api_Client::METHOD_GET,
        );
    }
}
