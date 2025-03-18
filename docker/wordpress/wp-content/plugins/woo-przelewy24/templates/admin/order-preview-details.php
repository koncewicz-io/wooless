<# if(data.p24_payment){ #>
<div class="wc-order-preview-addresses">
    <div class="wc-order-preview-address p24-order-preview-details">
        <h2><?= __('Przelewy24 transaction details', 'woocommerce-p24') ?></h2>

        <# if(data.p24_session_id){ #>
        <strong><?= __('Session ID', 'woocommerce-p24') ?></strong>
        {{ data.p24_session_id }}
        <# } #>

        <br />
        <# if(data.p24_order_id){ #>
        <strong><?= __('Przelewy24 order ID', 'woocommerce-p24') ?></strong>
        {{ data.p24_order_id }}
        <# } #>

        <# if(data.p24_payment_title){ #>
        <strong><?= __('Payment method', 'woocommerce-p24') ?></strong>
        {{ data.p24_payment_title }}
        <# } #>
    </div>
</div>
<# } #>


