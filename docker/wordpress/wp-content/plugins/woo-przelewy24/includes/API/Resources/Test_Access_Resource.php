<?php

namespace WC_P24\API\Resources;

use WC_P24\API\Api_Client;

if ( !defined('ABSPATH') ) {
    exit;
}

class Test_Access_Resource extends Resource
{
    public const TEST_ACCESS_RESOURCE = 'api/v1/testAccess';

    public function test_access(): array
    {
        return $this->get_api_client()->request(self::TEST_ACCESS_RESOURCE, Api_Client::METHOD_GET);
    }
}
