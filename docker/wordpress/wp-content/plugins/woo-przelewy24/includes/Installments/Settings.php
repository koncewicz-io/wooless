<?php

namespace WC_P24\Installments;

use WC_P24\Utilities\Module_Settings;

class Settings extends Module_Settings
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_handle(): string
    {
        return 'installments';
    }

    public function get_label(): string
    {
        return __('Installment widget', 'woocommerce-p24');
    }

    public function settings(): array
    {
        $prefix = 'p24_installments_';

        return [
            [
                'type' => 'title',
                'title' => __('Installment Widget configuration', 'woocommerce-p24'),
                'desc' => __('Configure settings for Installments widgets', 'woocommerce-p24')
            ],
            [
                'id' => Installments::ENABLE_KEY,
                'type' => 'checkbox',
                'desc' => __('Enable Installment widget', 'woocommerce-p24'),
                'info' => '<svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('<h3>Enable Installment widget</h3> Enabling this option will display a widget/simulator with information about the available installment offer. Before enabling this option, please contact Przelewy24. <br />Non-standard WordPress templates may not allow displaying additional content in the product page, cart page, and checkout page areas.<br /><br /><em>Example view for the buyer:</em><br /><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_setting-simulator1.png" alt="Enable Installment widget" style="max-width: 400px">', 'woocommerce-p24'),
                'default' => 'no'
            ],
            [
                'id' => Installments::PREFIX.'product_widget_type',
                'type' => 'select',
                'custom_attributes' => ['required' => true],
                'title' => __('Widget size options', 'woocommerce-p24'),
                'info' => '<svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('<h3>Widget size options</h3> Choose the Size of the Displayed Element: Options are MINI and MAX<br /><br /><em>Example view for the buyer:</em><br /><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_setting-installment3.png" alt="Widget Size Options" style="max-width: 400px">', 'woocommerce-p24'),
                'options' => [
                    'mini' => __('Mini', 'woocommerce-p24'),
                    'max' => __('Max', 'woocommerce-p24'),
                ],
                'default' => 'mini'
            ],
            [
                'id' => Installments::PREFIX.'show_simulator',
                'type' => 'checkbox',
                'title' => __('Show simulator', 'woocommerce-p24'),
                'desc' => __('On click widget open installment simulator modal', 'woocommerce-p24'),
                'info' => '<svg class="p24-ui-icon" style="width:22px;"><use href="#p24-icon-info"></use></svg> '.__('<h3>Installment simulator modal</h3> When a customer clicks on the widget, a pop-up window may appear with information about the installment simulation.<br /><br /><em>Example view for the buyer:</em><br /><img src="https://www.przelewy24.pl/storage/app/media/do-pobrania/gotowe-wtyczki/woocommerce/helper/en_p24_setting-simulator2.png" alt="Installment simulator modal" style="max-width: 400px">', 'woocommerce-p24'),

                'default' => true
            ],
            [
                'id' => Installments::PREFIX.'show_widget_on_product',
                'type' => 'checkbox',
                'title' => __('Show on product', 'woocommerce-p24'),
                'desc' => __('Show Installments widget on product page', 'woocommerce-p24'),
                'default' => true
            ],
            [
                'id' => Installments::PREFIX.'min_product_price',
                'type' => 'number',
                'title' => __('Min product price', 'woocommerce-p24'),
                'desc' => __('Minimal product price to show widget', 'woocommerce-p24'),
                'default' => '100'
            ],
            [
                'id' => Installments::PREFIX.'max_product_price',
                'type' => 'number',
                'title' => __('Max product price', 'woocommerce-p24'),
                'desc' => __('Maximum product price to show widget', 'woocommerce-p24'),
                'default' => '50000'
            ],
            [
                'id' => Installments::PREFIX.'product_widget_position',
                'type' => 'select',
                'custom_attributes' => ['required' => true],
                'title' => __('Position of widget', 'woocommerce-p24'),
                'options' => [
                    'before' => __('Before add to cart', 'woocommerce-p24'),
                    'after' => __('After add to cart', 'woocommerce-p24'),
                ],
                'default' => 'before'
            ],
            [
                'id' => Installments::PREFIX.'show_widget_on_checkout',
                'type' => 'checkbox',
                'title' => __('Show on checkout', 'woocommerce-p24'),
                'desc' => __('Show Installments widget on checkout page under shipping details', 'woocommerce-p24'),
                'default' => true
            ],
            [
                'type' => 'sectionend'
            ]
        ];
    }
}
