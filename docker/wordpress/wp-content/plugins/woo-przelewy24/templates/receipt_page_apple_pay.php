<?php

/**
 * @var array $config
 */
?>

<form class="p24-payment-container p24-payment-apple-pay">
    <div id="card-whitelabel"></div>

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

<script>window._config = <?= json_encode($config) ?></script>
<script src="<?= WC_P24_PLUGIN_URL . 'assets/js/apple-pay-receipt.bundle.js' ?>"></script>
