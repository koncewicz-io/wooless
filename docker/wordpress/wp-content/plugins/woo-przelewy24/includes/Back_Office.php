<?php

namespace WC_P24;

use DateTime;
use Exception;
use WC_P24\API\Updater_Client;
use WC_P24\Utilities\Sanitizer;

class Back_Office
{
    const VALUE_KEY = '_p24_backoffice_data';

    public function get_banner(): ?object
    {
        $data = $this->get_backoffice_data();

        if (!$this->validate_info($data))
            return null;

        return $data->backoffice;
    }

    public function get_backoffice_data()
    {
        $value = get_transient(self::VALUE_KEY);

        if (false === $value) {
            try {
                $client = new Updater_Client();
                $value = $client->request();
                set_transient(self::VALUE_KEY, $value, 3600 * 3);
            } catch (Exception $e) {
                $value = null;
            }
        }

        return $value;
    }

    public function validate_info($value): bool
    {
        try {
            if (!isset($value->backoffice))
                return false;

            $backoffice = $value->backoffice;

            $date = isset($backoffice->available) ? new DateTime($backoffice->available) : null;

            if ($date && new DateTime('now') > $date)
                return false;

            if ($backoffice->banner && $backoffice->url)
                return true;

        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
