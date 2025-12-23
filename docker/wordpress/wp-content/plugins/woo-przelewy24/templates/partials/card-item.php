<?php
/**
 * @var array $card
 */
?>

<button class="p24-1click p24-1click--card" type="button" data-id="<?= $card['id'] ?>">
    <?php if (!empty($card['logo'])): ?>
        <figure class="p24-1click__logo p24-1click--card__logo" role="presentation">
            <img src="<?= $card['logo']['url'] ?>" alt="<?= $card['logo']['alt'] ?>" />
        </figure>
    <?php endif ?>
    <span
        class="p24-1click--card__number"><small>✱✱✱✱ ✱✱✱✱ ✱✱✱✱</small> <?= $card['last_digits'] ?></span>
    <span class="p24-1click--card__valid"><?= $card['valid_to'] ?></span>
</button>
