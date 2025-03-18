<?php
/**
 * @var $skip string
 * @var $go_to_settings string
 * @var $go_to_plugins string
 * @var $old_plugin array
 * @var $logo_url string
 * @var $action string
 * @var $import string
 * @var $import_message string
 * @var $settings ArrayObject
 * @var $multicurrency ArrayObject
 * @var $subscriptions ArrayObject
 * @var $references ArrayObject
 */
?>

<div class="p24-wizard">
    <div class="py-5 text-center">
        <img class="d-block mx-auto mb-4" src="<?= $logo_url ?>" alt="" width="170" />
        <h2><?= _x('Przelewy24 Payment Gateway', 'wizard', 'woocommerce-p24') ?></h2>
        <p class="lead"> <?= _x('Migrate the setting from the old version of the plug-in', 'wizard', 'woocommerce-p24') ?></p>
    </div>


    <?php if ($import): ?>
        <?php if ($import == 'success'): ?>
            <div class="alert alert-success text-center" role="alert">
                <?= _x('Data import was successful', 'wizard', 'woocommerce-p24') ?>

                <p class="text-sm">
                    <?= sprintf(_x('For the plug-in to work correctly, the old version <strong>(%s)</strong> must be deactivated.', 'wizard', 'woocommerce-p24'), $old_plugin['Name']) ?>
                </p>

                <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                    <a href="<?= $go_to_settings ?>" class="btn btn-danger"
                       style="font-size: 0.85rem; order:1;"><?= _x('Go to plugin settings', 'wizard', 'woocommerce-p24') ?></a>
                    <a href="<?= $go_to_plugins ?>" class="btn btn-danger"
                       style="font-size: 0.85rem; order:1;"><?= _x('Go to plugins list', 'wizard', 'woocommerce-p24') ?></a>
                    <a href="<?= $skip ?>" class="btn btn-danger"
                       style="font-size: 0.85rem; order:1;"><?= _x('Back to Admin panel', 'wizard', 'woocommerce-p24') ?></a>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($import == 'warning'): ?>
            <div class="alert alert-danger" role="alert">
                <?= _x('Import has failed', 'wizard', 'woocommerce-p24') ?>
                <?= $import_message ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($import != 'success'): ?>

        <form action="<?= $action ?>" method="post">
            <div class="row g-3">

                <div class="col-12">
                    <label for="p24_mode" class="form-label"><?= __('Module mode', 'woocommerce-p24') ?></label>
                    <select class="form-select" id="p24_mode" required>
                        <option value="sandbox"><?= __('Test - sandbox', 'woocommerce-p24') ?></option>
                        <option value="production"><?= __('Normal', 'woocommerce-p24') ?></option>
                    </select>
                </div>

                <div class="col-12">
                    <label for="p24_merchant_id" class="form-label"><?= __('Merchant ID', 'woocommerce-p24') ?></label>
                    <input type="text" class="form-control" name="p24_merchant_id" pattern="^[0-9]+$"
                           value="<?= $settings->merchant_id ?? '' ?>" required>
                </div>

                <div class="col-12">
                    <label for="p24_crc_key" class="form-label"><?= __('CRC key', 'woocommerce-p24') ?></label>
                    <input type="text" class="form-control" name="p24_crc_key" pattern="^[a-f0-9]{16}$"
                           value="<?= $settings->crc_key ?? '' ?>" required>
                </div>

                <div class="col-12">
                    <label for="p24_reports_key"
                           class="form-label"><?= __('API key', 'woocommerce-p24') ?></label>
                    <input type="text" class="form-control" name="p24_reports_key" pattern="^[a-f0-9]+$"
                           value="<?= $settings->reports_key ?? '' ?>" required>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex flex-column flex-md-row align-items-center justify-content-center">
                <div class="list-group">
                    <label class="list-group-item d-flex gap-3">
                        <input class="form-check-input flex-shrink-0" type="checkbox" name="multicurrency" value="1"
                               style="font-size: 1.375em;" <?= $multicurrency->enabled ? 'checked' : '' ?>
                            <?= !$multicurrency->enabled ? 'disabled' : '' ?>
                        >
                        <span class="pt-1 form-checked-content">
                  <strong><?= _x('Import multicurrency settings', 'wizard', 'woocommerce-p24') ?></strong>
                </span>
                    </label>

                    <label class="list-group-item d-flex gap-3">
                        <input class="form-check-input flex-shrink-0" type="checkbox" name="subscriptions" value="1"
                               style="font-size: 1.375em;" <?= $subscriptions->enabled ? 'checked' : '' ?>
                            <?= !$subscriptions->enabled ? 'disabled' : '' ?>
                        >
                        <span class="pt-1 form-checked-content">
                  <strong><?= __('Import users subscriptions') ?></strong>
                  <small class="d-block text-body-secondary">
                      <?= sprintf(_nx('%d subscription found', '%d subscriptions found', 'wizard', 'woocommerce-p24'), $subscriptions->quantity) ?>
                  </small>
                </span>
                    </label>

                    <label class="list-group-item d-flex gap-3">
                        <input class="form-check-input flex-shrink-0" type="checkbox" name="references" value="1"
                               style="font-size: 1.375em;" <?= $references->quantity ? 'checked' : '' ?>
                            <?= !$references->quantity ? 'disabled' : '' ?>
                        >
                        <span class="pt-1 form-checked-content">
                  <strong><?= _x('Import users saved credit cards references', 'wizard', 'woocommerce-p24') ?></strong>
                  <small class="d-block text-body-secondary">
                    <?= sprintf(_nx('%d saved user card found', '%d saved user cards found', 'wizard', 'woocommerce-p24'), $references->quantity) ?>
                  </small>
                </span>
                    </label>


                </div>
            </div>
            <hr class="my-4">

            <div class="d-flex align-items-center justify-content-between">
                <button class="btn btn-danger" type="submit" name="migrate" style="order:2;"
                        value="yes"><?= _x('Migrate', 'wizard', 'woocommerce-p24') ?></button>

                <a href="<?= $skip ?>" class="btn link-secondary"
                   style="font-size: 0.85rem; order:1;"><?= _x('Skip migration', 'wizard', 'woocommerce-p24') ?></a>
            </div>

        </form>

    <?php endif; ?>

</div>

