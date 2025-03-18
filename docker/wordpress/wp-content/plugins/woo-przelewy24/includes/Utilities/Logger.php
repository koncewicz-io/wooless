<?php

namespace WC_P24\Utilities;

class Logger
{
    const EXCEPTION = 1;
    const DEBUG = 2;

    static public function log($message, int $type = 0, bool $log_request_data = false): void
    {
        $debugging = defined('P24_DEBUG') ? P24_DEBUG : false;
        $should_log = !($type == self::DEBUG && !$debugging);

        if (is_array($message)) {
            $message = json_encode($message);
        }

        if ($should_log) {
            if ($log_request_data) {
                error_log(json_encode(['POST' => $_POST, 'GET' => $_GET]));
            }
            
            error_log((string)$message);
        }
    }
}
