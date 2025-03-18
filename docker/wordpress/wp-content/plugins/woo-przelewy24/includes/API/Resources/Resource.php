<?php

namespace WC_P24\API\Resources;

use WC_P24\API\Api_Client;

abstract class Resource
{
    protected Api_Client $apiClient;

    public function __construct()
    {
        $this->apiClient = new Api_Client();
    }

    public function get_api_client(): Api_Client
    {
        return $this->apiClient;
    }
}
