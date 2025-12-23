<?php

namespace WC_P24\API;

if (!defined('ABSPATH')) {
    exit;
}

class Apple_Client
{
    public function validate($payload, $key_path, $cert_path): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://apple-pay-gateway-cert.apple.com/paymentservices/paymentSession');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

        curl_setopt($ch, CURLOPT_SSLCERT, $cert_path);
        curl_setopt($ch, CURLOPT_SSLKEY, $key_path);

        $result = curl_exec($ch);
        $error = curl_errno($ch);

        if ($error || !$result) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);

        return json_decode($result);
    }
}
