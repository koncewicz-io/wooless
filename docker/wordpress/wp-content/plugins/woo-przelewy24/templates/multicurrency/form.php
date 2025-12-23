<?php

/**
 * @var array $fields
 * @var array $currencies
 */

use WC_P24\Render;

?>

<dialog id="p24_currency_dialog" class="p24-ui-dialog">
    <form action="" id="p24_currency_form">
        <div class="p24-ui-dialog__header">
            <div class="p24-ui-form-title">
                <h2><?= _x('Currency configuration', 'multicurrency form', 'woocommerce-p24') ?>:</h2>
            </div>

            <div class="p24-ui-currency p24-ui-currency--alt">
                <div>
                    <strong><span id="p24_form_currency_code"></span> (<span id="p24_form_currency_symbol"></span>)</strong>
                    <span id="p24_form_currency_name"></span>
                </div>
            </div>
        </div>

        <?php Render::template('admin/form', [
            'fields' => $fields,
            'show_submit' => false
        ]) ?>

        <div class="p24-ui-buttons">
            <button type="button" class="p24-ui-button p24-ui-button--link"
                    id="p24_cancel_currency"><?= _x('Cancel', 'multicurrency form', 'woocommerce-p24') ?></button>
            <button type="submit" class="p24-ui-button"><?= _x('Save', 'multicurrency form', 'woocommerce-p24') ?></button>
        </div>
    </form>
</dialog>

<script> window.p24CurrencySettings = <?= json_encode($currencies) ?></script>
