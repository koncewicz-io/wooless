<?php

namespace WC_P24\Models\Simple;

if (!defined('ABSPATH')) {
    exit;
}

class Blik_Alias_Notification
{
    public string $email = '';
    public string $ref_id = '';
    public string $type = '';
    public string $status = '';

    public function __construct(array $data)
    {
        $this->email = $data['email'];
        $this->ref_id = $data['value'];
        $this->type = $data['type'];
        $this->status = $data['status'];
    }
}
