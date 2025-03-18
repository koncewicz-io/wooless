<?php

namespace WC_P24\Models\Simple;

if (!defined('ABSPATH')) {
    exit;
}

class Refund_Notification
{
    public int $order_id = 0;
    public ?string $session_id = null;
    public int $merchant_id = 0;
    public ?string $request_id = null;
    public ?string $refunds_uuid = null;

    public int $amount = 0;
    public int $timestamp = 0;
    public int $status = 0;
    public ?string $currency = null;
    public ?string $sign = null;

    public function __construct(array $data)
    {
        $this->order_id = $data['orderId'];
        $this->session_id = $data['sessionId'];
        $this->merchant_id = $data['merchantId'];
        $this->request_id = $data['requestId'];
        $this->refunds_uuid = $data['refundsUuid'];
        $this->amount = (int)$data['amount'];
        $this->currency = $data['currency'];
        $this->timestamp = (int)$data['timestamp'];
        $this->status = (int)$data['status'];
        $this->sign = $data['sign'];
    }
}
