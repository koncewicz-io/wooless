<?php
/**
 * @var array $sections
 */
?>

<ul class="subsubsub">
    <?php foreach ($sections as $id => $section): ?>
        <li>
            <a href="<?= $section['url'] ?>" class="<?= $section['class'] ?>">
                <?= $section['label'] ?>
            </a>
        </li>
    <?php endforeach ?>
</ul>

<br class="clear" />

<style>
    .subsubsub li:not(:first-child):before {
        content: " | ";
    }
</style>

