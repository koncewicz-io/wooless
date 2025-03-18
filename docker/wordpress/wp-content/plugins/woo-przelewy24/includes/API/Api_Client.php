<?php

namespace WC_P24\API;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;
use WC_P24\Config;

class Api_Client
{
    public const PRODUCTION_API_URL = 'https://secure.przelewy24.pl/';
    public const SANDBOX_API_URL = 'https://sandbox.przelewy24.pl/';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_GET = 'GET';

    protected Config $config;

    public function __construct()
    {
        $this->config = Config::get_instance();
    }

    public function request(string $endpoint, string $method, ?array $payload = null, ?array $query = null): array
    {
        $encodedCredentials = base64_encode($this->config->get_merchant_id() . ':' . $this->config->get_reports_key());

        $url = $this->get_api_url() . $endpoint;

        $headers = [
            'Authorization' => 'Basic ' . $encodedCredentials,
        ];

        $args = [
            'method' => $method,
            'timeout' => 45,
            'sslverify' => $this->config->is_live(),
        ];

        if ($method === self::METHOD_POST || $method === self::METHOD_PUT) {
            $headers['Content-Type'] = 'application/json';
            $args['body'] = json_encode($payload);
        }

        $args['headers'] = $headers;

        if ($method === self::METHOD_GET && is_array($query)) {
            $url = add_query_arg($query, $url);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new Exception('HTTP request failed: ' . $response->get_error_message());
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);

        return $response;
    }

    public function get_api_url(): string
    {
        return $this->config->is_live() ? self::PRODUCTION_API_URL : self::SANDBOX_API_URL;
    }
}
