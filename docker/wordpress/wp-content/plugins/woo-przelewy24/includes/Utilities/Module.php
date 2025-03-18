<?php

namespace WC_P24\Utilities;


abstract class Module
{
    public ?Module_Settings $settings;

    public function __construct()
    {
        if (static::is_enabled()) {
            if (is_admin()) {
                $this->on_admin();
            }

            if (!is_admin()) {
                $this->on_client();
            }
        }
    }

    abstract static function is_enabled(): bool;

    abstract protected function on_client(): void;

    abstract protected function on_admin(): void;
}
