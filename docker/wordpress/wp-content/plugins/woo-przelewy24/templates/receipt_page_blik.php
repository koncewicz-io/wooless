<?php

use WC_P24\Render;

/**
 * @var array $config
 */

?>

<form class="p24-payment-container p24-payment-blik">
    <?php if (!empty($config['oneClick']['enabled']) && !empty($config['oneClick']['items'])): ?>
        <div class="p24-1clicks">
            <div class="p24-1clicks__label"><?= $config['i18n']['use_saved'] ?></div>
            <div class="p24-1clicks__items">
                <?php foreach ($config['oneClick']['items'] as $blik): ?>
                    <?php Render::template('partials/blik-item', ['blik' => $blik]); ?>
                <?php endforeach ?>
            </div>
            <div class="p24-1clicks__or"><?= $config['i18n']['or'] ?></div>
        </div>
    <?php endif ?>

    <div class="p24-payment-input">
        <label for="code">
            <input type="text" name="code" id="blik-code" placeholder="<?= $config['i18n']['label']['input'] ?>" />
        </label>
    </div>

    <?php if (!empty($config['oneClick']['enabled'])): ?>
        <div class="p24-checkbox">

            <label for="save-one-click">
                <input id="save-one-click" type="checkbox" name="save" value="1" />
                <span><?= $config["i18n"]["label"]["save"] ?></span>
            </label>
        </div>

        <div class="p24-waiting alias hidden" id="waiting-alias">
            <?= $config['i18n']['confirm']['transaction'] ?>
            <button type="button" id="waiting-alias-cancel"
                    class="woocommerce-button wp-element-button button"><?= $config['i18n']['label']['cancel'] ?></button>
        </div>
    <?php endif ?>

    <div class="p24-checkbox">
        <label for="regulation">
            <input id="regulation" type="checkbox" name="regulation" value="1" required />
            <span><?= $config['i18n']['label']['regulation'] ?></span>
        </label>
    </div>

    <div class="p24-waiting hidden" id="waiting-status">
        <?= $config['i18n']['confirm']['transaction'] ?>
    </div>

    <button class="woocommerce-button wp-element-button button" type="submit" id="submit">
        <?= $config['i18n']['label']['submit'] ?>
    </button>
</form>

<script>window._config = <?= json_encode($config) ?></script>
<script src="<?= WC_P24_PLUGIN_URL . 'assets/js/blik-receipt.bundle.js' ?>"></script>
