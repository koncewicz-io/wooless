<?php
/**
 * @var array $pending_refunds
 */
?>


</tbody>
<tbody id="order_pending_refunds">

<?php foreach ($pending_refunds as $refund) : ?>
    <tr class="refund">
        <td class="thumb">
            <div></div>
        </td>

        <td class="name">
            <?= __('Pending refund', 'woocommerce-p24') ?>

            <?php if (!empty($refund['reason'])) : ?>
                <p class="description"><?= $refund['reason'] ?></p>
            <?php endif; ?>
        </td>

        <td class="item_cost" width="1%">&nbsp;</td>
        <td class="quantity" width="1%">&nbsp;</td>

        <td class="line_cost" width="1%">
            <div class="view">-<?= $refund['amount'] ?></div>
        </td>

        <td class="wc-order-edit-line-item">&nbsp;</td>
    </tr>
<?php endforeach; ?>
</tbody>
