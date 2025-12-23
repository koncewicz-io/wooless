<?php

namespace WC_P24\Models\Simple;

use WC_P24\Models\Database\Reference;
use WC_P24\Utilities\Sanitizer;

if (!defined('ABSPATH')) {
    exit;
}

class Reference_Simple
{
    protected string $ref_id = '';
    protected ?\DateTime $valid_to = null;
    protected string $info = '';
    protected string $type = '';
    protected string $hash = '';
    protected ?int $status = null;

    public function set_ref_id(string $ref_id): void
    {
        $ref_id = Sanitizer::sanitize_token($ref_id);
        $this->ref_id = $ref_id;
    }

    public function set_valid_to(string $valid_to): void
    {
        try {
            $this->valid_to = new \DateTime($valid_to);
        } catch (\Exception $e) {
            $this->valid_to = null;
        }
    }

    public function set_info(string $info): void
    {
        $info = Sanitizer::sanitize_string($info);
        $this->info = $info;
    }

    public function set_type(string $type): void
    {
        if (in_array($type, [Reference::TYPE_VISA, Reference::TYPE_MASTERCARD, Reference::TYPE_BLIK, Reference::TYPE_BLIK_RECURRING])) {
            $this->type = $type;
        }
    }

    public function set_hash(string $hash): void
    {
        $hash = Sanitizer::sanitize_token($hash);
        $this->hash = $hash;
    }

    public function set_status(int $status): void
    {
        if (in_array($status, [Reference::STATUS_NOT_CONFIRMED, Reference::STATUS_REGISTERED, Reference::STATUS_UNREGISTERED, Reference::STATUS_EXPIRED])) {
            $this->status = $status;
        }
    }

    public function get_ref_id(): string
    {
        return $this->ref_id;
    }

    public function get_hash(): string
    {
        return $this->hash;
    }

    public function to_array(): array
    {
        return [
            'ref_id' => $this->ref_id,
            'valid_to' => $this->valid_to ? $this->valid_to->format('Y-m-d') : '',
            'info' => $this->info,
            'type' => $this->type,
            'hash' => $this->hash,
            'status' => $this->status
        ];
    }
}
