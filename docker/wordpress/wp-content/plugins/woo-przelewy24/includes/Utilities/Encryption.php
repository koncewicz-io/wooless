<?php

namespace WC_P24\Utilities;

class Encryption
{
    const CIPHER = 'aes-256-cbc';

    public static function get_iv(): string
    {
        return hex2bin(get_option('p24_iv', ''));
    }

    public static function get_key(): string
    {
        return get_option('p24_encryption_key', '');
    }

    public static function generate_key(): string
    {
        $encryption_key = bin2hex(openssl_random_pseudo_bytes(8));
        $encryption_key = str_replace(['#', '=', '+'], '', $encryption_key);

        return $encryption_key;
    }

    public static function generate_iv(): string
    {
        $iv = bin2hex(openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER)));

        return $iv;
    }

    public static function encrypt($data, $base64_encode_output = true): string
    {
        $encryptedData = openssl_encrypt($data, self::CIPHER, self::get_key(), 0, self::get_iv());

        if ($base64_encode_output) {
            $encryptedData = base64_encode($encryptedData);
        }

        return $encryptedData;
    }

    public static function decrypt($data, $base64_decode_input = true): string
    {
        if ($base64_decode_input) {
            $data = base64_decode($data);
        }

        return openssl_decrypt($data, self::CIPHER, self::get_key(), 0, self::get_iv());
    }


    public static function generate_signature(array $data): string
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha384', $payload);
    }

    public static function generate_session_id(int $order_id): string
    {
        $timestamp = time();
        $session_id_raw = $order_id . '_' . $timestamp;
        $hashed_session_id = hash('sha256', $session_id_raw);

        return $hashed_session_id;

    }
}
