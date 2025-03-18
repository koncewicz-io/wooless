<?php

/**
 * @var bool $show_submit
 * @var string $submit_label
 * @var array $fields
 */
?>

<?php if (!empty($fields)): ?>
    <div class="p24-ui-form">
        <?php foreach ($fields as $field): ?>
            <?php if (!in_array($field['type'], ['text', 'hidden', 'textarea', 'code', 'checkbox', 'number', 'select', 'title', 'description', 'button', 'password_toggle', 'methods'])) continue; ?>

            <?php if ($field['type'] == 'title'): ?>
                <div class="p24-ui-form-title">
                    <h2><?= $field['title'] ?></h2>
                    <?php if($field['description']): ?><p><?= $field['description'] ?></p><?php endif; ?>
                </div>
            <?php elseif ($field['type'] == 'description'): ?>
                <div class="p24-ui-form-description">
                    <?php if($field['description']): ?><p><?= $field['description'] ?></p><?php endif; ?>
                </div>
            <?php elseif ($field['type'] == 'hidden'): ?>
                <input type="hidden" value="<?= $field['value'] ?>" <?= $field['attributes_html'] ?>/>
            <?php else: ?>
                <div class="p24-ui-form__row" data-id="<?= $field['id'] ?>">
                    <?php if ($field['title']): ?>
                        <label for="<?= $field['id'] ?>" class="p24-ui-form__title">
                            <span><?= esc_html($field['title']) ?></span>
                            <?= !empty($field['required']) ? '*' : '' ?>
                        </label>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <div class="p24-ui-field p24-ui-field--<?= $field['type'] ?>">
                        <div class="p24-ui-field__input">
                            <?php switch ($field['type']):
                                case 'number':
                                case 'password':
                                case 'text': ?>
                                    <input class="p24-ui-field__control" type="<?= $field['type'] ?>"
                                           value="<?= $field['value'] ?>" <?= $field['attributes_html'] ?> autocomplete="off" />
                                    <?php break; ?>

                                <?php case 'password_toggle': ?>
                                    <input class="p24-ui-field__control" type="password"
                                           value="<?= $field['value'] ?>" <?= $field['attributes_html'] ?> autocomplete="off" />
                                    <button type="button">
                                        <svg class="p24-ui-icon p24-ui-icon--18">
                                            <use href="#p24-icon-eye-closed" />
                                        </svg>
                                    </button>
                                    <?php break; ?>
                                <?php case 'checkbox': ?>
                                    <label for="<?= $field['name'] ?>" class="p24-ui-switch">
                                        <input type="checkbox"
                                            <?= $field['attributes_html'] ?> value="1" />
                                        <span class="p24-ui-switch__handle"></span>
                                        <?= $field['label'] ?>
                                    </label>
                                    <?php break; ?>

                                <?php case 'textarea': ?>
                                    <textarea
                                        class="p24-ui-field__control" <?= $field['attributes_html'] ?>><?= $field['value'] ?></textarea>
                                    <?php break; ?>
                                <?php case 'select': ?>
                                    <select class="p24-ui-field__control" <?= $field['attributes_html'] ?>>
                                        <?php foreach ($field['options'] as $value => $option): ?>
                                            <option
                                                value="<?= $value ?>" <?= $value == $field['value'] ? 'selected' : '' ?>><?= $option ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php break; ?>
                                <?php case 'methods': ?>
                                    <div class="p24-methods methods-sortable">
                                        <input type="hidden"
                                               value="<?= $field['value'] ?>" <?= $field['attributes_html'] ?> />

                                        <div class="p24-methods__items" data-items>
                                            <?php foreach ($field['methods'] as $method): ?>
                                                <?php $selected = $method['featured'] ?? false; ?>

                                                <label class="p24-method-item" data-item-id="<?= $method['id'] ?>">
                                                    <input type="checkbox" name="<?= $field['id'] ?>_method"
                                                           value="<?= $method['id'] ?>" <?= $selected ? 'checked' : '' ?> />
                                                    <picture>
                                                        <img src="<?= $method['mobileImgUrl'] ?>"
                                                             alt="<?= $method['name'] ?>" />
                                                    </picture>
                                                    <span><?= $method['name'] ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php break; ?>

                                <?php case 'button': ?>
                                    <button id="<?= $field['id'] ?>"
                                            class="p24-ui-button p24-ui-button--secondary p24-ui-button--small"
                                            type="button">
                                        <?= $field['label'] ?: '' ?>
                                    </button>
                                    <?php break; ?>

                                <?php case 'code': ?>
                                    <div class="editor">
                            <textarea
                                class="p24-ui-field__control editor_textarea" <?= $field['attributes_html'] ?>><?= $field['value'] ?></textarea>
                                        <div class="editor_content"></div>
                                    </div>
                                <?php endswitch; ?>
                        </div>

                        <?php if (!empty($field['description'])): ?>
                            <div class="p24-ui-field__desc"><?= $field['description'] ?> </div>
                        <?php endif ?>

                        <?php if (!empty($field['after'])) : ?>
                            <?= $field['after'] ?>
                        <?php endif ?>
                    </div>

                    <?php if (!empty($field['info'])): ?>
                        <div class="p24-ui-form__info">
                            <div role="button">
                                <svg class="p24-ui-icon">
                                    <use href="#p24-icon-info" />
                                </svg>
                            </div>

                            <div class="p24-ui-form__info-content hidden">
                                <?= $field['info'] ?>
                            </div>
                        </div>


                    <?php endif ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($show_submit): ?>
            <div class="p24-ui-form__row">
                <div class="p24-ui-buttons">
                    <button name="save" disabled class="woocommerce-save-button p24-ui-button" type="submit"
                            value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>">
                        <?= !empty($submit_label) ? $submit_label : __('Save changes', 'woocommerce-p24') ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="p24-ui-form__info-details hidden" id="p24_field_details"></div>
<?php endif; ?>
