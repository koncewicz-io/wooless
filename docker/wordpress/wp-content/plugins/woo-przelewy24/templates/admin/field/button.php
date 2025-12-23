<?php
/**
 * @var array $settings
 */
?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <span><?= esc_html($settings['title']) ?></span>
    </th>

    <td class="forminp formimp-button">
        <button id="<?= esc_html($settings['id']) ?>" class="button-primary" type="button">
            <?php if (isset($settings['label'])) : ?>
                <?= esc_html($settings['label']) ?>
            <?php endif ?>
        </button>

        <?php if (isset($settings['after'])) : ?>
            <?= $settings['after'] ?>
        <?php endif ?>
    </td>
</tr>
