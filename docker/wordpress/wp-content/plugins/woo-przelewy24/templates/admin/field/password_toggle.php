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
            <?php if ($settings['desc'] && !empty($settings['desc_tip'])) { ?>
                <span class="woocommerce-help-tip" tabindex="0" aria-label="<?= $settings['desc'] ?>"
                      data-tip="<?= $settings['desc'] ?>"></span>
            <?php } ?>
        </label>
    </th>

    <td class="forminp formimp-text mailserver-pass-wrap">
        <span class="wp-pwd">
        <input name="<?= $settings['id'] ?>" id="<?= $settings['id'] ?>" type="password"
               value="<?= $settings['value'] ?>"
            <?php if (isset($settings['class'])): ?>
                class="<?= $settings['class'] ?>"
            <?php endif; ?>
            <?php if (isset($settings['placeholder'])): ?>
                placeholder="<?= $settings['placeholder'] ?>"
            <?php endif; ?>
            <?= isset($custom_attributes) ? $custom_attributes : '' ?>
        />
        <button type="button" class="button wp-hide-pw hide-if-no-js"><span class="dashicons dashicons-visibility
        "></span></button>
        </span>

        <?php if ($settings['desc'] && empty($settings['desc_tip'])) { ?>
            <p class="description"><?= $settings['desc'] ?></p>
        <?php } ?>
    </td>
</tr>
