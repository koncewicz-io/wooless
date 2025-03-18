<?php
/**
 * @var \WC_Product $product
 */

defined('ABSPATH') || exit;

if (!$product->possible_to_buy()) {
    return;
}

?>

<?php do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="cart"
      action="<?= esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
      method="post" enctype='multipart/form-data'>

    <?php do_action('woocommerce_before_add_to_cart_button'); ?>

    <button type="submit" name="add-to-cart" value="<?= esc_attr($product->get_id()); ?>"
            class="single_add_to_cart_button button alt<?= esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
        <?= esc_html($product->add_to_cart_text()) ?>
    </button>

    <?php do_action('woocommerce_after_add_to_cart_button'); ?>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>

