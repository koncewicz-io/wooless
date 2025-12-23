<?php
/**
 * @var array $settings
 * @var array $methods
 * @var array $featured
 */
?>

<tr valign="top" data-id="<?= $settings['id'] ?>">
    <th scope="row" class="titledesc">
        <label for="<?= $settings['id'] ?>">
            <?php if (!empty($settings['title'])) : ?>
                <?= esc_html($settings['title']) ?>
            <?php endif; ?>
            <?php if (!empty($settings['description']) && !empty($settings['desc_tip'])) { ?>
                <span class="woocommerce-help-tip" tabindex="0" aria-label="<?= $settings['description'] ?>"
                      data-tip="<?= $settings['description'] ?>"></span>
            <?php } ?>
        </label>
    </th>

    <td class="forminp formimp-text">
        <?php if (!empty($settings['description']) && empty($settings['desc_tip'])) { ?>
            <p><?= $settings['description'] ?></p>
        <?php } ?>

        <div class="p24-methods methods-sortable">
            <input name="<?= $settings['id'] ?>" type="hidden"
                   id="<?= $settings['id'] ?>" value="<?= $settings['value'] ?>" />

            <div class="p24-methods__items" data-items>
                <?php foreach ($methods as $method): ?>
                    <?php $selected = $method['featured'] ?? false; ?>

                    <label class="p24-method-item" data-item-id="<?= $method['id'] ?>">
                        <input type="checkbox" name="<?= $settings['id'] ?>_method"
                               value="<?= $method['id'] ?>" <?= $selected ? 'checked' : '' ?> />
                        <picture>
                            <img src="<?= $method['mobileImgUrl'] ?>" alt="<?= $method['name'] ?>" />
                        </picture>
                        <span><?= $method['name'] ?></span>
                    </label>

                <?php endforeach; ?>
            </div>
    </td>
</tr>

<script>
    jQuery(function() {
        jQuery(".p24-methods__items").sortable()
    })
</script>
