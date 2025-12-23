<?php

use WC_P24\Render;

/**
 * @var array $config
 */
?>

<form class="p24-payment-container p24-payment-card">
    <?php if (!empty($config['oneClick']['enabled']) && !empty($config['oneClick']['items'])): ?>
        <div class="p24-1clicks">
            <div class="p24-1clicks__label"> <?= $config['i18n']['use_saved'] ?> </div>
            <div class="p24-1clicks__items">
                <?php foreach ($config['oneClick']['items'] as $card): ?>
                    <?php Render::template('partials/card-item', ['card' => $card]); ?>
                <?php endforeach ?>
            </div>
            <div class="p24-1clicks__or"><?= $config['i18n']['or'] ?></div>
        </div>
    <?php endif ?>

    <div id="card-tokenizer"></div>
    <div id="card-whitelabel"></div>

    <?php if (!empty($config['oneClick']['enabled'])): ?>
        <div class="p24-checkbox">
            <label for="save-one-click">
                <input id="save-one-click" type="checkbox" name="save" value="1" />
                <span><?= $config["i18n"]["label"]["save"] ?></span>
            </label>
        </div>
    <?php endif ?>

    <div class="p24-checkbox">
        <label for="regulation">
            <input id="regulation" type="checkbox" name="regulation" value="1" required />
            <span><?= $config['i18n']['label']['regulation'] ?></span>
        </label>
    </div>

    <button class="woocommerce-button wp-element-button button" type="submit" id="submit">
        <?= $config['i18n']['label']['submit'] ?>
    </button>
</form>

<div id="p24-3ds-modal" class="p24-3ds-modal hidden fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
    <div class="modal-content">
        <div id="p24-3ds-iframe-wrapper"></div>
    </div>
</div>


<script>window._config = <?= json_encode($config) ?></script>
<script src="<?= WC_P24_PLUGIN_URL . 'assets/js/card-receipt.bundle.js' ?>"></script>
