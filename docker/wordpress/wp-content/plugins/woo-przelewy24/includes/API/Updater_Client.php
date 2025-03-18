<?php

namespace WC_P24\API;

class Updater_Client
{
    public function request()
    {
        $request = wp_safe_remote_get(WC_P24_UPDATE_URL, [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $response = wp_remote_retrieve_body($request);

        if (is_wp_error($request) || 200 !== wp_remote_retrieve_response_code($request) || empty($response)) {
            return null;
        }

        return json_decode($response);
    }
}
