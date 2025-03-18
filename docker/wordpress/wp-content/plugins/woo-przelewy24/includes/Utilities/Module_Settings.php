<?php

namespace WC_P24\Utilities;


use WC_P24\UI\Display;

abstract class Module_Settings
{
    public function __construct()
    {
        if ($this->has_settings_page()) {
            add_filter('woocommerce_get_sections_' . WC_P24_SETTINGS_NAME, [$this, 'add_section']);
            add_filter('woocommerce_settings_' . WC_P24_SETTINGS_NAME, [$this, 'render_section'], 30);
            add_action('woocommerce_update_options_' . WC_P24_SETTINGS_NAME, [$this, 'update_settings']);
            add_action('woocommerce_after_settings_' . WC_P24_SETTINGS_NAME, [$this, 'after_form']);
        }
    }

    public function add_section(array $sections): array
    {
        $sections[$this->get_handle()] = $this->get_label();

        return $sections;
    }

    public function update_settings()
    {
        global $current_section;

        if ($current_section === $this->get_handle()) {
            woocommerce_update_options($this->settings());
        }
    }

    public function render_section(): void
    {
        global $current_section;

        if ($current_section === $this->get_handle()) {
            $this->display_settings();
        }
    }

    protected function has_settings_page(): bool
    {
        return true;
    }

    protected function display_settings()
    {
        $display = new Display(Display::ADMIN_API);
        $display->set_fields($this->settings(), $this);

        $display->render($this->before_settings(), $this->after_settings());
    }

    protected function settings(): array
    {
        return [];
    }

    public function get_settings($only_ids = false): array
    {
        $settings = $this->settings();

        if ($only_ids) {
            $only_ids = array_filter($settings, function ($setting) {
                return isset($setting['id']);
            });

            return array_map(function ($setting) {
                return $setting['id'];
            }, $only_ids);
        }

        return $settings;
    }

    protected function is_current_section(): bool
    {
        $page = isset($_GET['page']) ? Sanitizer::sanitize_key($_GET['page']) : null;
        $tab = isset($_GET['tab']) ? Sanitizer::sanitize_key($_GET['tab']) : null;
        $section = isset($_GET['section']) ? Sanitizer::sanitize_key($_GET['section']) : null;

        return $page === 'wc-settings' && $tab === WC_P24_SETTINGS_NAME && $section == $this->get_handle();
    }

    protected function before_settings(): ?string
    {
        return '';
    }

    protected function after_settings(): ?string
    {
        return '';
    }

    public function after_form(): void
    {
    }

    abstract public function get_handle(): string;

    abstract public function get_label(): string;
}
