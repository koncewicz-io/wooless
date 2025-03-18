<?php

namespace WC_P24\Models\Simple;

use WC_P24\Models\Database\Reference;

if (!defined('ABSPATH')) {
    exit;
}

class Reference_Notification
{
    public string $email = '';
    public string $reference = '';
    public string $type = '';
    public ?int $status = null;
    public \DateTime $expiration;

    public function __construct(array $data)
    {
        $this->email = $data['email'] ?? '';
        $this->reference = $data['value'] ?? '';
        $this->type = $data['type'] ?? '';
        $this->status = $this->get_status($data['status']);

        try {
            $this->expiration = new \DateTime($data['expirationDate']);
        } catch (\Exception $e) {
            $this->expiration = new \DateTime();
        }
    }

    public function get_status(string $status): ?int
    {
        $statuses = [
            'NOT_CONFIRMED' => Reference::STATUS_NOT_CONFIRMED,
            'REGISTERED' => Reference::STATUS_REGISTERED,
            'UNREGISTERED' => Reference::STATUS_UNREGISTERED,
            'EXPIRED' => Reference::STATUS_EXPIRED,
        ];

        return $statuses[$status] ?? null;
    }

    public function validate(): bool
    {
        if ($this->reference && $this->status !== null) {
            return true;
        }

        return false;
    }
}
