<?php
/**
 * @var WP_List_Table $table
 * @var string $url
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?= __('Subscriptions P24', 'woocommerce-p24') ?></h1>
    <a href="<?= $url ?>" target="_blank" class="page-title-action"><?= __('Export', 'woocommerce-p24') ?></a>
    <h2 class="screen-reader-text"><?= __('Subscriptions P24', 'woocommerce-p24') ?></h2>
    <hr class="wp-header-end">

    <?php $table->views() ?>

    <form method="get">
        <?php $table->display() ?>
    </form>
</div>
