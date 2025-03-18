<?php

namespace WC_P24;

if (!defined('ABSPATH')) {
    exit;
}

class Render
{
    public static function template(string $name, array $params = [], bool $can_be_override = false): void
    {
        $file = self::get_local_file_path($name);
        $theme_file = self::get_theme_file_path($name);

        if ($can_be_override && file_exists($theme_file)) {
            $file = $theme_file;
        }

        if (is_file($file) && file_exists($file)) {
            extract($params);
            include $file;
        }
    }

    public static function return(string $name, array $params = [], bool $can_be_override = false): string
    {
        $content = '';

        ob_start();
        self::template($name, $params, $can_be_override);

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    private static function get_local_file_path($name)
    {
        return WC_P24_PLUGIN_TEMPLATES . '/' . $name . '.php';
    }

    private static function get_theme_file_path($name)
    {
        $theme_path = get_template_directory();

        return $theme_path . '/' . WC_P24_PLUGIN_BASENAME . '/' . $name . '.php';
    }
}
