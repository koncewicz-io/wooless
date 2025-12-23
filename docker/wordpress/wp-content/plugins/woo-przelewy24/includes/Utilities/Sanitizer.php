<?php

namespace WC_P24\Utilities;

class Sanitizer
{
    private array $input = [];
    private $filters;

    public function __construct(array $input, $filters = FILTER_SANITIZE_STRING)
    {
        $this->input = $input;
        $this->filters = $filters;
    }

    public function run(): array
    {
        return filter_var_array($this->input, $this->filters);
    }

    public static function sanitize_key(string $value): string
    {
        return preg_replace('/[^a-z0-9_\-]/', '', trim(strtolower(strip_tags($value))));
    }

    public static function sanitize_token(string $value): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', trim(strip_tags($value)));
    }

    public static function sanitize_string(string $value): string
    {
        return trim(htmlentities(strip_tags($value)));
    }

    public static function sanitize_key_as_filter(): array
    {
        return [
            'filter' => FILTER_CALLBACK,
            'options' => function ($value) {
                return self::sanitize_key($value);
            }
        ];
    }
}
