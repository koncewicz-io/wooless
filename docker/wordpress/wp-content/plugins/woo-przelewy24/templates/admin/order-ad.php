<?php
/**
 * @var object $banner
 */
?>

<div class="p24-ui-banner p24-ui-banner--order">
    <?php if ($banner): ?>
        <figure>
            <a href="<?= $banner->url_order ?>" target="_blank" title="Przelewy24">
                <img src="<?= $banner->banner_order . '?t=' . time() ?>" alt="Przelewy24" />
            </a>
        </figure>
    <?php endif; ?>
</div>
