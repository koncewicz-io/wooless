<?php

namespace WC_P24\Installments;

use WC_P24\Config;
use WC_P24\Utilities\Encryption;
use WC_P24\Utilities\Module;

class Installments extends Module
{
    const ENABLE_KEY = 'p24_installments_enabled';
    const PREFIX = 'p24_installments_';

    public function __construct()
    {
        parent::__construct();

        add_action('init', [$this, 'registerBlock']);

        if (self::show_widget_on_checkout()) {
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
}
