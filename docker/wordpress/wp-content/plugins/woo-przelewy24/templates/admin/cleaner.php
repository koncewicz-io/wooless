<?php

use WC_P24\Render;

/**
 * @var $action string
 * @var $fields array
 */

?>

<div class="p24-ui-wrapper">
    <div class="p24-ui-content">
        <form action="" method="post">
            <?php Render::template('admin/form', [
                'fields' => $fields,
                'show_submit' => true,
                'submit_label' => __('Clear data', 'woocommerce-p24'),
            ]) ?>
        </form>
    </div>
</div>


<script>
    (() => {
        const button = document.querySelector("button[name=\"save\"]")
        const checkbox = document.getElementById("p24_clear_accept")

        if (!button || !checkbox) return

        checkbox.addEventListener("change", () => {
            button.disabled = !checkbox.checked
        });
    })()
</script>
