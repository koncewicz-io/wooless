<?php

namespace WC_P24\API\Resources;

use WC_P24\API\Api_Client;

if (!defined('ABSPATH')) {
    exit;
}

class Transaction_Resource extends Resource
{
    public const TRANSACTION_REGISTER_ENDPOINT = 'api/v1/transaction/register';
    public const TRANSACTION_VERIFY_ENDPOINT = 'api/v1/transaction/verify';
    public const TRANSACTION_DO_PAYMENT_ENDPOINT = 'api/v1/transaction/doPayment';
    public const TRANSACTION_BY_SESSION_ID_ENDPOINT = 'api/v1/transaction/by/sessionId/{sessionId}';
    public const TRANSACTION_REJECT_ENDPOINT = 'api/v1/transaction/reject';

    public function register_transaction(array $transactionData): array
    {
        return $this->get_api_client()->request(
            self::TRANSACTION_REGISTER_ENDPOINT,
            Api_Client::METHOD_POST,
            $transactionData
        );
    }

    public function verify_transaction(array $transactionVerifyData): array
    {
        return $this->get_api_client()->request(
            self::TRANSACTION_VERIFY_ENDPOINT,
            Api_Client::METHOD_PUT,
            $transactionVerifyData
        );
    }

    public function do_payment(array $data): array
    {
        return $this->get_api_client()->request(
            self::TRANSACTION_DO_PAYMENT_ENDPOINT,
            Api_Client::METHOD_POST,
            $data
        );
    }

    public function reject_transaction(array $data): array
    {
        return $this->get_api_client()->request(
            self::TRANSACTION_REJECT_ENDPOINT,
            Api_Client::METHOD_PUT,
            $data
        );
    }

    public function get_transaction_by_session_id(string $transactionId): array
    {
        $endpoint = str_replace('{sessionId}', $transactionId, self::TRANSACTION_BY_SESSION_ID_ENDPOINT);

        return $this->get_api_client()->request($endpoint, Api_Client::METHOD_GET);
    }
}
