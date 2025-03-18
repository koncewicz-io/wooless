<?php

namespace WC_P24\Utilities;

class User_Data
{
    public static function get_user_ip(): ?string
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }

        return (string)$ip_address;
    }

    public static function get_user_agent(): ?string
    {
        return (string)$_SERVER['HTTP_USER_AGENT'];
    }
}
