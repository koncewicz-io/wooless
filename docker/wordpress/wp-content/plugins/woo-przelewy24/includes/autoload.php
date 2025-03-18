<?php

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function (string $class_name): void {
    // Remove base namespace
    $class_name = str_replace('WC_P24\\', '', $class_name);
    $class_name_as_file = str_replace('\\', '/', $class_name) . '.php';

    $file_absolute_path = __DIR__ . '/' . $class_name_as_file;

    if (file_exists($file_absolute_path)) {
        require_once $file_absolute_path;
    }
});
