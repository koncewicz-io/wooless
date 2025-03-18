<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function rejected(array $responses, array $keys = []): bool
    {
        return !empty(array_filter($responses, function ($item, $key) use ($keys) {
            if (empty($keys)) {
                return $item['state'] === 'rejected';
            }

            return in_array($key, $keys) && $item['state'] === 'rejected';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
