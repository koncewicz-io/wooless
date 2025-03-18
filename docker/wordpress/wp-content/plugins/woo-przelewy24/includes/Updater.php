<?php

namespace WC_P24;

use Exception;
use WC_P24\API\Updater_Client;
use WC_P24\Integrity\Integrity;

class Updater
{

    const VALUE_KEY = '_p24_update_data';

    public function __construct()
    {
        if (!empty(WC_P24_UPDATE_URL)) {
            add_filter('plugins_api', [$this, 'info'], 20, 3);
            add_filter('site_transient_update_plugins', [$this, 'update'], 50, 2);
            add_filter('transient_update_plugins', [$this, 'update'], 50, 2);
            add_action('in_plugin_update_message-' . WC_P24_PLUGIN_BASENAME, [$this, 'update_message'], 20, 2);
        }
    }

    public function update_message($data, $response)
    {
        if (!$response->integrity) {
            echo '<br />';
            echo __('Changes have been detected in the currently installed plug-in, the update will overwrite the changes made.', 'woocommerce-p24');
        }
    }

    public function get_update_data()
    {
        delete_transient(self::VALUE_KEY);
        $value = get_transient(self::VALUE_KEY);

        if (false === $value) {
            try {
                $client = new Updater_Client();
                $value = $client->request();
                set_transient(self::VALUE_KEY, $value, 3600);
            } catch (Exception $e) {
                $value = null;
            }
        }

        return $value;
    }

    public function info($data, $action, $args)
    {
        if ('plugin_information' !== $action || WC_P24_PLUGIN_BASENAME !== $args->slug) {
            return $data;
        }

        $new_data = $this->get_update_data();

        if (empty($new_data)) {
            return $data;
        }

        if (isset($new_data->sections)) {
            $new_data->sections = (array)$new_data->sections;
        }

        if (isset($new_data->banners)) {
            $new_data->banners = (array)$new_data->banners;
        }

        $new_data->download_url = $new_data->package;
        $new_data->download_link = $new_data->package;
        $new_data->trunk = $new_data->package;

        return $new_data;
    }


    public function update($data, $action)
    {
        if ('update_plugins' !== $action || !$data) {
            return $data;
        }

        $new_data = $this->get_update_data();

        if (empty($new_data)) {
            return $data;
        }

        if (version_compare($new_data->version, Core::$version, '<=')) {
            return $data;
        }

        $data->response[WC_P24_PLUGIN_BASENAME] = (object)[
            'id' => 'woo-przelewy24',
            'integrity' => Integrity::check(),
            'slug' => WC_P24_PLUGIN_BASENAME,
            'plugin' => WC_P24_PLUGIN_BASEFILE,
            'url' => $new_data->package,
            'package' => $new_data->package,
            'new_version' => $new_data->version,
        ];

        return $data;
    }
}
