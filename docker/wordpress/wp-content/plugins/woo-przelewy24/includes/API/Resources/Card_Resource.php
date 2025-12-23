<?php

namespace WC_P24\API\Resources;

use WC_P24\API\Api_Client;

if (!defined('ABSPATH')) {
    exit;
}

class Card_Resource extends Resource
{
    public const CARD_INFO_BY_ORDER_ID_ENDPOINT = 'api/v1/card/info/{orderId}';
    public const CARD_CHARGE_3DS_ENDPOINT = 'api/v1/card/chargeWith3ds';
    public const CARD_CHARGE_ENDPOINT = 'api/v1/card/charge';
    public const CARD_PAY_ENDPOINT = 'api/v1/card/pay';
    public const CARD_REJECT_ENDPOINT = 'api/v1/card/reject';

    public function get_info(string $orderId): array
    {
        $endpoint = str_replace('{orderId}', $orderId, self::CARD_INFO_BY_ORDER_ID_ENDPOINT);

        return $this->get_api_client()->request($endpoint, Api_Client::METHOD_GET);
    }

    public function charge_with_3ds(string $token): array
    {
        return $this->get_api_client()->request(
            self::CARD_CHARGE_3DS_ENDPOINT,
            Api_Client::METHOD_POST,
            ['token' => $token]
        );
    }

    public function charge(string $token): array
    {
        return $this->get_api_client()->request(
            self::CARD_CHARGE_ENDPOINT,
            Api_Client::METHOD_POST,
            ['token' => $token]
        );
    }

    public function pay(array $data): array
    {
        return $this->get_api_client()->request(
            self::CARD_PAY_ENDPOINT,
            Api_Client::METHOD_POST,
            $data
        );
    }

    public function reject(array $data): array
    {
        return $this->get_api_client()->request(
            self::CARD_REJECT_ENDPOINT,
            Api_Client::METHOD_PUT,
            $data
        );
    }
}
