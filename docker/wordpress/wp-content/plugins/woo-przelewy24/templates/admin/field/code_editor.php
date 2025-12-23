<?php
/**
 * @var array $settings
 * @var string $custom_attributes
 */

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?= $settings['id'] ?>">
            <?= esc_html($settings['title']) ?>
            <?php if ($settings['description'] && !empty($settings['desc_tip'])) { ?>
                <span class="woocommerce-help-tip" tabindex="0" aria-label="<?= $settings['description'] ?>"
                      data-tip="<?= $settings['description'] ?>"></span>
            <?php } ?>
        </label>
    </th>

    <td class="forminp formimp-text mailserver-pass-wrap ">
        <div class="editor">
            <textarea class="editor_textarea" name="<?= $settings['id'] ?>"
                      id="<?= $settings['id'] ?>"><?= $settings['value'] ?></textarea>
            <div class="editor_content"></div>
        </div>

        <?php if ($settings['description'] && empty($settings['desc_tip'])) { ?>
            <p class="description"><?= $settings['description'] ?></p>
        <?php } ?>
    </td>
</tr>
