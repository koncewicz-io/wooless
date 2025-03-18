<?php
/**
 * @var ?string $session_id
 * @var ?string $order_id
 */
?>

<?php if ($session_id || $order_id): ?>
    <div class="p24-order-details">
        <h3><?= __('Przelewy24 transaction details', 'woocommerce-p24') ?></h3>

        <?php if ($session_id): ?>
            <p>
                <strong><?= __('Session ID', 'woocommerce-p24') ?></strong>
                <?= $session_id ?>
            </p>
        <?php endif; ?>

        <?php if ($order_id): ?>
            <p>
                <strong><?= __('Przelewy24 order ID', 'woocommerce-p24') ?></strong>
                <?= $order_id ?>
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>
