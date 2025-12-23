<?php

namespace WC_P24\API\Resources;

use WC_P24\API\Api_Client;

if (!defined('ABSPATH')) {
    exit;
}

class Refund_Resource extends Resource
{
    public const REFUND_ENDPOINT = 'api/v1/transaction/refund';
    public const REFUND_DETAILS_ENDPOINT = 'api/v1/refund/by/orderId/{orderId}';

    public function refund(array $refund_data): array
    {
        return $this->get_api_client()->request(
            self::REFUND_ENDPOINT,
            Api_Client::METHOD_POST,
            $refund_data
        );
    }

    public function get_details(int $order_id): array
    {
        $endpoint = str_replace('{orderId}', $order_id, self::REFUND_DETAILS_ENDPOINT);

        return $this->get_api_client()->request(
            $endpoint,
            Api_Client::METHOD_GET
        );
    }
}
