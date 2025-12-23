<?php
/**
 * @var Subscription[] $subscriptions
 * @var string $nonce
 */

use WC_P24\Models\Database\Subscription;

?>

<?php if (!count($subscriptions)): ?>
    <div class="wc-block-components-notice-banner is-info" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"
             focusable="false">
            <path
                d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path>
        </svg>
        <div
            class="wc-block-components-notice-banner__content"><?= __('No subscriptions has been subscribe yet.', 'woocommerce-p24') ?></div>
    </div>
<?php else: ?>

    <table class="shop_table shop_table_responsive account-p24-subscriptions-table">
        <thead>
        <tr>
            <th>
                <span class="nobr"><?= __('Product', 'woocommerce-p24') ?></span>
            </th>
            <th>
                <span class="nobr"><?= __('Valid to', 'woocommerce-p24') ?></span>
            </th>
            <th>
                <span class="nobr"><?= __('Status', 'woocommerce-p24') ?></span>
            </th>
            <th>
                <span class="nobr"><?= __('Downloads', 'woocommerce-p24') ?></span>
            </th>
            <th>
                <span class="nobr"><?= __('Actions', 'woocommerce-p24') ?></span>
            </th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($subscriptions as $subscription): ?>
            <tr>
                <td data-title="<?= __('Product', 'woocommerce-p24') ?>">
                    <a href="<?= get_permalink($subscription->get_product_id()) ?>"><?= $subscription->product_title ?></a><br />
                </td>

                <td data-title="<?= __('Valid to', 'woocommerce-p24') ?>">
                    <?php if (!$subscription->is_pending()): ?>
                        <time datetime="<?= $subscription->get_valid_to()->format('c') ?>">
                            <?= $subscription->get_valid_to()->format('d-m-Y') ?>
                        </time>
                    <?php endif; ?>
                </td>

                <td data-title="<?= __('Status', 'woocommerce-p24') ?>">
                    <?= $subscription->get_status_label() ?>
                </td>

                <td data-title="<?= __('Downloads', 'woocommerce-p24') ?>">
                    <?php if ($subscription->is_available()): ?>
                        <?php foreach ($subscription->get_downloads() as $download): ?>
                            <a href="<?= $download['download_url'] ?>"><?= $download['download_name'] ?></a><br />
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>

                <?php if ($subscription->is_cancelable()): ?>
                    <td data-title="<?= __('Actions', 'woocommerce-p24') ?>">
                        <button data-delete data-id="<?= $subscription->get_id() ?>" data-nonce="<?= $nonce ?>"
                                class="woocommerce-button wp-element-button button"><?= _x('Cancel', 'subscription', 'woocommerce-p24') ?></button>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
