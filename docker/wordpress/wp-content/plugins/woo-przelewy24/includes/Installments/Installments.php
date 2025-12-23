<?php

namespace WC_P24\Installments;

use WC_P24\Config;
use WC_P24\Gateways\Gateways_Manager;
use WC_P24\Gateways\Virtual_Gateway;
use WC_P24\Utilities\Encryption;
use WC_P24\Utilities\Module;
use WC_P24\Utilities\Payment_Methods;

class Installments extends Module
{
    const ENABLE_KEY = 'p24_installments_enabled';
    const PREFIX = 'p24_installments_';

    public function __construct()
    {
        parent::__construct();

        add_action('init', [$this, 'registerBlock']);

        if (self::show_widget_on_checkout() && (!WC()->cart || WC()->cart->total >= self::get_min_product_price())) {
            add_action('woocommerce_blocks_loaded', [$this, 'wooBlocks']);
        }

        $this->settings = new Settings();
    }

    public function wooBlocks()
    {
        add_action('woocommerce_blocks_checkout_block_registration', function ($registry) {
            $registry->register(new Block_Installments());
        });
    }

    public static function registerBlock()
    {
        register_block_type(WC_P24_PLUGIN_PATH . '/assets/blocks/block-p24-installments/block.json');
    }

    static function is_enabled(): bool
    {
        $config = Config::get_instance();
        $isPLNCurrency = $config->get_currency() === 'PLN';

        return get_option(self::ENABLE_KEY, 'no') === 'yes' && $isPLNCurrency;
    }

    static function show_widget_on_product(): bool
    {
        return get_option(self::PREFIX . 'show_widget_on_product', 'no') === 'yes';
    }

    static function show_as_payment_method(): bool
    {
        return get_option(self::PREFIX . 'show_as_payment_method', 'no') === 'yes';
    }

    static function get_min_product_price(): ?int
    {
        return (int)get_option(self::PREFIX . 'min_product_price', 100);
    }

    static function get_max_product_price(): ?int
    {
        return (int)get_option(self::PREFIX . 'max_product_price', 50000);
    }

    static function show_widget_on_checkout(): bool
    {
        return get_option(self::PREFIX . 'show_widget_on_checkout', 'no') === 'yes';
    }

    static function show_simulator(): bool
    {
        return get_option(self::PREFIX . 'show_simulator', 'no') === 'yes';
    }

    static function get_product_widget_position(): string
    {
        return get_option(self::PREFIX . 'product_widget_position', 'before');
    }

    static function get_type_of_widget()
    {
        return get_option(self::PREFIX . 'product_widget_type', 'mini');
    }

    static function get_signature()
    {
        $config = new Config();

        return Encryption::generate_signature([
            'crc' => $config->get_crc_key(),
            'posId' => $config->get_merchant_id(),
            'method' => 303
        ]);
    }

    protected function on_client(): void
    {
        new Client_Side();
    }

    protected function on_admin(): void
    {
    }

    public static function add_as_gateway(): void
    {
        if (self::is_enabled() and self::show_as_payment_method()) {
            $filtered = array_filter(Payment_Methods::get_available_methods(), function ($method) {
                return $method['id'] == Payment_Methods::P24_INSTALLMENTS;
            });

            $method = array_shift($filtered);

            if (!empty($method)) {
                Gateways_Manager::$extra_gateways[] = new Virtual_Gateway\Gateway($method['id'], $method['name'], $method['mobileImgUrl']);
            }
        }
    }

    public static function add_as_gateway_in_block($fetch_methods, &$featured): void
    {
        if (self::is_enabled() && self::show_as_payment_method()) {
            $methods = $fetch_methods ? Gateways_Manager::get_available_methods() : [];

            $installmentGateway = array_values(array_filter($methods, function ($gateway) {
                return $gateway['id'] == Payment_Methods::P24_INSTALLMENTS;
            }));

            if (isset($installmentGateway[0])) {
                $gateway = $installmentGateway[0];
                $gateway['featured'] = true;
                $featured = array_merge($featured, [$gateway]);
            }
        }
    }
}
