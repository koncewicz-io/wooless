<?php
/**
 * @var array $i18n
 * @var int $method
 */

use WC_P24\Render;

?>

<div class="p24-payment-container p24-virtual-payment">
    <?php Render::template('gateway/regulation-checkbox', ['method' => $method, 'value' => $i18n['label']['regulation']]) ?>
</div>
