<?php
/**
 * @var array $methods
 * @var array $i18n
 * @var int $method
 */

use WC_P24\Render;

?>

<div class="p24-payment-container p24-online-payments">
    <input type="hidden" name="method" id="p24-online-payments-method" />
    <div class="p24-methods">
        <?php if (!empty($methods['featured'])): ?>
            <div class="p24-methods__items p24-methods__items--featured">
                <?php foreach ($methods['featured'] as $_method): ?>
                    <?php Render::template('gateway/online-payments-item', ['method' => $_method]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($methods['rest'])): ?>
            <div class="p24-methods__items">
                <?php foreach ($methods['rest'] as $_method): ?>
                    <?php Render::template('gateway/online-payments-item', ['method' => $_method]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php Render::template('gateway/regulation-checkbox', ['method' => $method, 'value' => $i18n['label']['regulation']]) ?>
</div>



