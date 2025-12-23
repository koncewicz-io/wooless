<?php

namespace WC_P24;

use DateTime;
use Exception;
use WC_P24\API\Updater_Client;

class Back_Office
{
    const ON_PLUGIN_PAGE = 0;
    const ON_ORDER_PAGE = 1;
    const VALUE_KEY = '_p24_backoffice_data';

    private static $data = null;

    public function __construct()
    {
        add_action('admin_footer', [$this, 'inject_ad_on_order_page']);
    }

    public static function get_banner(int $type = self::ON_PLUGIN_PAGE): ?object
    {
        self::$data = self::get_backoffice_data();

        if (!self::validate_info($type))
            return null;

        return self::$data->backoffice;
    }

    public static function get_backoffice_data()
    {
        if (!($value = get_transient(self::VALUE_KEY))) {
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

    public static function validate_info(int $type): bool
    {
        try {
            if (!isset(self::$data->backoffice))
                return false;

            $backoffice = self::$data->backoffice;

            [$banner_key, $url_key, $available_key] = array_map(function ($item) use ($type) {
                return $type === self::ON_ORDER_PAGE ? $item . '_order' : $item;
            }, ['banner', 'url', 'available']);

            $date = isset($backoffice->{$available_key}) ? new DateTime($backoffice->{$available_key}) : null;

            if ($date && new DateTime('now') > $date)
                return false;

            if ($backoffice->{$banner_key} && $backoffice->{$url_key})
                return true;

        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    public function inject_ad_on_order_page()
    {
        if (get_current_screen()->id === 'woocommerce_page_wc-orders' && isset($_GET['id'])) {
            $order = wc_get_order((int)$_GET['id']);

            $banner = trim(Render::return('admin/order-ad', ['banner' => Back_Office::get_banner(self::ON_ORDER_PAGE)]));

            if ($order && strpos($order->get_payment_method(), "p24-") === 0) {
                echo "<script>window.p24_order_ad = `" . $banner . "`;</script>";
            }
        }
    }
}
