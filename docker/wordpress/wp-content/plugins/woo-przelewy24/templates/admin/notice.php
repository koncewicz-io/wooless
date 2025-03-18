<?php
/**
 * @var string $message
 * @var string $type
 * @var bool $dismissible
 */
?>

<div class="notice notice-<?= $type ?> <?= !empty($dismissible) ? 'is-dismissible' : '' ?>">
    <p><?= $message ?></p>
</div>
