<?php
/**
 * @var array $items
 * @var string $nonce
 */

use WC_P24\Models\Database\Reference;

?>

<?php if (!count($items)): ?>
    <div class="wc-block-components-notice-banner is-info" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"
             focusable="false">
            <path
                d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path>
        </svg>
        <div
            class="wc-block-components-notice-banner__content"><?= __('No payment/one click has been saved yet.', 'woocommerce-p24') ?></div>
    </div>
<?php else: ?>

    <table class="shop_table shop_table_responsive account-p24-one-clicks-table">
        <thead>
        <tr>
            <th>
                <span class="nobr"><?= __('Type', 'woocommerce-p24') ?></span>
            </th>
            <th>
                <span class="nobr"><?= __('Name/Card number', 'woocommerce-p24') ?></span>
            </th>
            <th>
                <span class="nobr"><?= __('Valid to', 'woocommerce-p24') ?></span>
            </th>
            <th>
                <span class="nobr"><?= __('Actions', 'woocommerce-p24') ?></span>
            </th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($items as $item): ?>
            <?php
            $icon = $item->get_icon();
            $is_blik = in_array($item->get_type(), [Reference::TYPE_BLIK, Reference::TYPE_BLIK_RECURRING]);
            ?>
            <tr>
                <td data-title="<?= __('Type', 'woocommerce-p24') ?>">
                    <?php if (!empty($icon)): ?>
                        <img src="<?= $icon['url'] ?>" alt="<?= $icon['alt'] ?>" />
                    <?php else: ?>
                        <?= ucfirst($item->get_type()) ?>
                    <?php endif; ?>
                </td>

                <td data-title="<?= __('Name/Card number', 'woocommerce-p24') ?>">
                    <?php if ($is_blik) : ?>
                        <?= $item->get_info() ?>
                    <?php else: ?>
                        <small>✱✱✱✱ ✱✱✱✱ ✱✱✱✱</small> <?= $item->get_info() ?>
                    <?php endif; ?>
                </td>

                <td data-title="<?= __('Valid to', 'woocommerce-p24') ?>">
                    <?= $item->get_valid_to()->format($is_blik ? 'd/m/Y' : 'm/Y') ?>
                </td>

                <td data-title="<?= __('Actions', 'woocommerce-p24') ?>">
                    <button data-delete data-id="<?= $item->get_id() ?>" data-nonce="<?= $nonce ?>"
                            class="woocommerce-button wp-element-button button"><?= _x('Remove', 'one click', 'woocommerce-p24') ?></button>
                </td>
            </tr>

            <?php if ($item->has_subscriptions()): ?>
                <tr class="shop_table__cell-description">
                    <td colspan="4">
                        <small><?= __('This payment has a subscription attached to it','woocommerce-p24') ?></small>
                    </td>
                </tr>
            <?php endif; ?>

        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
