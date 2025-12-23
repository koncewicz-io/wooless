<?php

namespace WC_P24\Models\Simple;

if (!defined('ABSPATH')) {
    exit;
}

class Notification
{
    public int $merchant_id = 0;
    public int $pos_id = 0;
    public ?string $session_id = null;
    public int $amount = 0;
    public int $origin_amount = 0;
    public ?string $currency = 'PLN';
    public int $order_id = 0;
    public int $method_id = 0;
    public ?string $statement = null;
    public ?string $sign = null;

    public function __construct(array $data)
    {
        $this->merchant_id = $data['merchantId'];
        $this->pos_id = $data['posId'];
        $this->session_id = $data['sessionId'];
        $this->amount = $data['amount'];
        $this->origin_amount = $data['originAmount'];
        $this->currency = $data['currency'];
        $this->order_id = $data['orderId'];
        $this->method_id = $data['methodId'];
        $this->statement = $data['statement'];
        $this->sign = $data['sign'];
    }
}
