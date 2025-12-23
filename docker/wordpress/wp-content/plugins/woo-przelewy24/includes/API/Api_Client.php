<?php

namespace WC_P24\API;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;
use WC_P24\Config;
use WC_P24\Core;

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
            'P24-PLUGIN-NAME' => 'Woocommerce',
            'P24-PLUGIN-VERSION' => Core::$version,
            'P24-PLUGIN-MERCHANT-ID' => $this->config->get_merchant_id(),
            'P24-PLUGIN-WEBPAGE' => get_bloginfo('url')
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
            wc_get_logger()->error(
                'P24 API request error: ' . $response->get_error_message(),
                ['source' => 'woo-przelewy24']
            );
            return ['error' => true, 'message' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            wc_get_logger()->error(
                'P24 API returned invalid JSON response: ' . $body,
                ['source' => 'woo-przelewy24']
            );
            return ['error' => true, 'message' => 'Invalid JSON from P24 API'];
        }

        return $decoded;
    }

    public function get_api_url(): string
    {
        return $this->config->is_live() ? self::PRODUCTION_API_URL : self::SANDBOX_API_URL;
    }
}
