<?php

use WC_P24\Helper;
use WC_P24\Multicurrency\Currency_Config;

/**
 * @var array $currencies
 * @var array $featured_currencies
 * @var Currency_Config[] $currency_configs
 */
?>

<div class="p24_multicurrency">
    <div class="p24-ui-currency-selector">
        <label for="p24_currency_code"><strong><?= __('Select currency', 'woocommerce-p24'); ?></strong></label>

        <div class="p24-ui-currency-selector__input">
            <?php foreach ($featured_currencies as $currency_code => $currency): ?>
                <div role="button" class="p24-ui-currency" data-currency="<?= $currency_code ?>">
                    <span class="p24-ui-currency__plus">+</span>
                    <div>
                        <strong><?= $currency_code ?> (<?= $currency['symbol'] ?>)</strong>
                        <span><?= $currency['name'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="p24-ui-field p24-ui-field--select">
                <select id="p24_currency_code_select" class="wc-enhanced-select p24-ui-field__control">
                    <option value=""><?= __('Select other currency', 'woocommerce-p24'); ?></option>
                    <?php foreach ($currencies as $currency_code => $currency): ?>
                        <option
                            value="<?= $currency_code ?>"><?= $currency['name'] . ' (' . $currency['symbol'] . ') - ' . $currency_code; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" id="p24_add_currency" disabled
                    class="p24-ui-button"><?= __('Add configuration', 'woocommerce-p24'); ?></button>
        </div>
    </div>

    <?php if (count($currency_configs)): ?>
        <table class="p24-ui-table" id="p24_currency_settings_table">
            <thead>
            <tr>
                <th><?= __('Currency', 'woocommerce-p24') ?>
                <th><?= __('Currency code', 'woocommerce-p24') ?></th>
                <th><?= __('Merchant ID', 'woocommerce-p24') ?></th>
                <th><?= __('CRC key', 'woocommerce-p24') ?></th>
                <th><?= __('API key', 'woocommerce-p24') ?></th>
                <th><?= __('Multiplier', 'woocommerce-p24') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($currency_configs as $currency): ?>
                <tr>
                    <td><?= $currency->get_currency_name() ?></td>
                    <td><?= $currency->get_currency_code() ?></td>
                    <td><?= $currency->merchant_id ?></td>
                    <td><?= Helper::anonymize($currency->crc_key) ?></td>
                    <td><?= Helper::anonymize($currency->reports_key) ?></td>
                    <td>
                        <?= $currency->get_multiplier() ?>
                        <br />
                        <small>
                            <?= wc_price(1) ?> =
                            <?= wc_price(1 * $currency->get_multiplier(), ['currency' => $currency->get_currency_code()]) ?>
                        </small>
                    </td>
                    <td data-config='<?= $currency->to_json() ?>'>
                        <div class="p24-ui-buttons">
                            <button type="button" class="p24-ui-button p24-ui-button--tiny" data-currency-edit>
                                <?= __('Edit', 'woocommerce-p24') ?>
                            </button>
                            <button type="button" class="p24-ui-button p24-ui-button--secondary p24-ui-button--tiny" data-currency-delete>
                                <?= __('Delete', 'woocommerce-p24') ?>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

