<?php

use WC_P24\Helper;
use WC_P24\Multicurrency\Currency_Config;

/**
 * @var array $currencies
 * @var Currency_Config[] $currency_configs
 */
?>

<div class="p24_multicurrency">
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="p24_currency_code"><?= __("Select currency", "woocommerce-p24"); ?></label>
            </th>
            <td class="forminp">
                <select id="p24_currency_code" class="wc-enhanced-select">
                    <option value=""><?= __("Select", "woocommerce-p24"); ?></option>
                    <?php foreach ($currencies as $currency_code => $currency_name): ?>
                        <option
                            value="<?= $currency_code ?>"><?= $currency_name . " (" . $currency_code . ")"; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="p24_add_currency"
                        class="button button-primary"><?= __("Add configuration", "woocommerce-p24"); ?></button>
            </td>
        </tr>
    </table>

    <div id="p24_currency_container" class="hidden">
        <h3 id="p24_currency_name"></h3>

        <table class="form-table">
            <tr>
                <th><label for="p24_currency_merchant_id"><?= __("Merchant ID", "woocommerce-p24") ?></label></th>
                <td><input type="text" id="p24_currency_merchant_id" class="regular-text" value="" /></td>
            </tr>
            <tr>
                <th><label for="p24_currency_shop_id"><?= __("Shop ID", "woocommerce-p24") ?></label></th>
                <td><input type="text" id="p24_currency_shop_id" class="regular-text" value="" /></td>
            </tr>
            <tr>
                <th><label for="p24_currency_crc_key"><?= __("CRC key", "woocommerce-p24") ?></label></th>
                <td><input type="text" id="p24_currency_crc_key"
                           placeholder="<?= __("16 characters", "woocommerce-p24") ?>" pattern="^[a-f0-9]{16}$"
                           class="regular-text" value="" />
                </td>
            </tr>
            <tr>
                <th><label for="p24_currency_reports_key"><?= __("Reports key", "woocommerce-p24") ?></label></th>
                <td><input type="text" id="p24_currency_reports_key" class="regular-text" value="" /></td>
            </tr>
            <tr>
                <th><label for="p24_currency_multiplier"><?= __("Multiplier", "woocommerce-p24") ?></label></th>
                <td><input type="number" id="p24_currency_multiplier" class="regular-text" value="" step="0.01" /></td>
            </tr>
            <tr>
                <th></th>
                <td colspan="2">
                    <button type="button" class="button button-primary save-currency"
                            id="p24_save_currency"><?= __("Save", "woocommerce-p24") ?>
                    </button>
                    <button type="button" class="button cancel-currency"
                            id="p24_cancel_currency"><?= __("Cancel", "woocommerce-p24") ?></button>
                </td>
            </tr>
        </table>

    </div>
</div>

<table class="wp-list-table widefat fixed striped" id="p24_currency_settings_table">
    <thead>
    <tr>
        <th><?= __("Currency", "woocommerce-p24") ?></th>
        <th><?= __("Currency code", "woocommerce-p24") ?></th>
        <th><?= __("Merchant ID", "woocommerce-p24") ?></th>
        <th><?= __("Shop ID", "woocommerce-p24") ?></th>
        <th><?= __("CRC key", "woocommerce-p24") ?></th>
        <th><?= __("Reports key", "woocommerce-p24") ?></th>
        <th><?= __("Multiplier", "woocommerce-p24") ?></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($currency_configs as $currency): ?>
        <tr>
            <td><?= $currency->get_currency_name() ?></td>
            <td><?= $currency->get_currency_code() ?></td>
            <td><?= $currency->merchant_id ?></td>
            <td><?= $currency->shop_id ?></td>
            <td><?= Helper::anonymize($currency->crc_key) ?></td>
            <td><?= Helper::anonymize($currency->reports_key) ?></td>
            <td>
                <?= $currency->get_multiplier() ?>
                <br />
                <small>
                    <?= wc_price(1) ?> =
                    <?= wc_price(1 * $currency->get_multiplier(), ["currency" => $currency->get_currency_code()]) ?>
                </small>
            </td>
            <td data-config='<?= $currency->to_json() ?>'>
                <button type="button" class="button button-secondary" data-currency-edit>
                    <?= __("Edit", "woocommerce-p24") ?>
                </button>
                <button type="button" class="button button-secondary" data-currency-delete>
                    <?= __("Delete", "woocommerce-p24") ?>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
