<?php
/**
 * @var array $blik
 */

?>

<button class="p24-1click p24-1click--blik" type="button" data-id="<?= $blik['id'] ?>">
    <?php if (!empty($blik['logo'])): ?>
        <figure class="p24-1click__logo p24-1click--blik__logo" role="presentation">
            <img src="<?= $blik['logo']['url'] ?>" alt="<?= $blik['logo']['alt'] ?>" />
        </figure>
    <?php endif ?>
    <span class="p24-1click--blik__name"><?= $blik['name'] ?></span>
    <span class="p24-1click--blik__valid"><?= $blik['valid_to'] ?></span>
</button>
