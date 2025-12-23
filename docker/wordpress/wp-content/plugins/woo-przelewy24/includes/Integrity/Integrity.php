<?php

namespace WC_P24\Integrity;

class Integrity
{

    static private function file_list(): array
    {
        $checksums = require __DIR__ . '/checksums.php';

        return $checksums;
    }

    static public function check(): bool
    {
        foreach (self::file_list() as $file => $sum) {
            if (!file_exists(WC_P24_PLUGIN_PATH . $file)) {
                return false;
            }

            $expected = hash_file('sha224', WC_P24_PLUGIN_PATH . $file);

            if ($expected !== $sum) {
                return false;
            }
        }

        return true;
    }
}
