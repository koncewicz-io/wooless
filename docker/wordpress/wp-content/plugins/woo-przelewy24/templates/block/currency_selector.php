<?php
/**
 * @var bool $enabled
 * @var string $current
 * @var \WC_P24\Multicurrency\Currency_Config[] $currencies
 */
?>

<?php if ($enabled): ?>
    <form class="p24-currency" method="post">
        <select name="currency" id="p24_currency" class="p24-currency__select">
            <?php foreach ($currencies as $currency): ?>
                <option
                    value="<?= $currency->get_currency_code() ?>"
                    <?= $current === $currency->get_currency_code() ? 'selected' : '' ?>
                >
                    <?= $currency->get_currency_code() ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button alt wp-element-button p24-currency__submit">
            <?= _x('Change', 'currency', 'woocommerce-p24') ?>
        </button>
    </form>
<?php endif; ?>
