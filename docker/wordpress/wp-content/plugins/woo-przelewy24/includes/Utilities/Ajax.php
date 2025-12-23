<?php

namespace WC_P24\Utilities;

if (!defined('ABSPATH')) {
    exit;
}

class Ajax
{
    static function add_action(string $name, array $function, bool $private = false): void
    {
        add_action('wp_ajax_' . $name, $function);
        !$private && add_action('wp_ajax_nopriv_' . $name, $function);
    }
}




