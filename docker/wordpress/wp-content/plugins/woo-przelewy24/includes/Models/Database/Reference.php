<?php

namespace WC_P24\Models\Database;

use DateTime;
use WC_Customer;
use WC_P24\Models\Simple\Card_Simple;
use WC_P24\Models\Simple\Reference_Simple;
use WC_P24\Utilities\Record;

if (!defined('ABSPATH')) {
    exit;
}

class Reference extends Record
{
    const TYPE_BLIK = 'blik';
    const TYPE_BLIK_RECURRING = 'blik-recurring';
    const TYPE_VISA = 'visa';
    const TYPE_MASTERCARD = 'mastercard';

    const STATUS_NOT_CONFIRMED = 0;
    const STATUS_REGISTERED = 1;
    const STATUS_UNREGISTERED = 2;
    const STATUS_EXPIRED = 3;

    private int $id = 0;
    private string $ref_id;
    private DateTime $valid_to;
    private ?WC_Customer $customer;
    private ?string $info = null;
    private int $customer_id = 0;
    private ?string $type = null;
    private ?string $hash = null;
    private ?string $status = null;
    private ?bool $is_connected = null;

    public function __construct(int $reference_id = 0)
    {
        if ($reference_id > 0) {
            $this->id = $reference_id;
        }

        if ($this->get_id()) {
            $this->find();
        }
    }

    public static function get(int $reference_id): ?Reference
    {
        $reference = new static($reference_id);

        return $reference->get_id() ? $reference : null;
    }

    public static function get_and_check(int $reference_id, int $user_id): ?Reference
    {
        $reference = static::get($reference_id);

        return $reference && $reference->get_customer_id() === $user_id ? $reference : null;
    }

    public static function table_name(): string
    {
        return self::db()->prefix . 'wc_p24_references';
    }

    protected static function primary_key(): string
    {
        return 'id';
    }

    protected static function get_fields(): array
    {
        return [
            'id' => ['format' => '%d'],
            'user_id' => ['format' => '%d'],
            'ref_id' => ['format' => '%s'],
            'valid_to' => ['format' => '%s', 'parse' => function (DateTime $value): string {
                return $value->format('Y-m-d');
            }],
            'info' => ['format' => '%s'],
            'type' => ['format' => '%s'],
            'hash' => ['format' => '%s'],
            'status' => ['format' => '%d']
        ];
    }

    protected function set_primary_key(int $value): void
    {
        $this->new_record = false;
        $this->id = $value;
    }

    public function set_customer_id(int $customer_id): void
    {
        $this->attributes['user_id'] = $customer_id;
        $this->customer_id = $customer_id;
        $this->customer = null;
    }

    public function set_ref_id(string $ref_id)
    {
        $this->attributes['ref_id'] = $ref_id;
        $this->ref_id = $ref_id;
    }

    public function set_valid_to(DateTime $valid_to): void
    {
        $this->attributes['valid_to'] = $valid_to;
        $this->valid_to = $valid_to;
    }

    public function set_info(string $info): void
    {
        $this->attributes['info'] = $info;
        $this->info = $info;
    }

    public function set_type(string $type): void
    {
        $this->attributes['type'] = $type;
        $this->type = $type;
    }

    public function set_hash(string $hash): void
    {
        $this->attributes['hash'] = $hash;
        $this->hash = $hash;
    }

    public function set_status(int $status): void
    {
        $this->attributes['status'] = $status;
        $this->status = $status;
    }

    public function is_connected(bool $value): void
    {
        $this->is_connected = $value;
    }

    protected function get_primary_key(): int
    {
        return $this->get_id();
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_ref_id(): string
    {
        return $this->ref_id;
    }

    public function get_customer_id(): int
    {
        return $this->customer_id;
    }

    public function get_valid_to(): ?DateTime
    {
        return $this->valid_to;
    }

    public function get_info(): string
    {
        return $this->info;
    }

    public function get_type(): ?string
    {
        return $this->type;
    }

    public function get_hash(): ?string
    {
        return $this->hash;
    }

    public function get_status(): ?int
    {
        return $this->status;
    }

    public function get_customer(): ?WC_Customer
    {
        if (isset($this->customer)) {
            $this->customer = new WC_Customer($this->get_customer_id());
        }

        return $this->customer;
    }

    protected static function joins(): string
    {
        return 'LEFT JOIN ' . self::db()->prefix . 'users as user ON user.ID = t.user_id';
    }

    public static function save_reference(?Reference_Simple $reference, $order, ?int $customer_id = null): ?int
    {
        if (empty($reference)) {
            return null;
        }

        $customer_id = $customer_id ?: (int)$order->get_customer_id();

        $references = static::findAll(['where' =>
            ['t.user_id = %d AND t.hash = %s', $customer_id, $reference->get_hash()]
        ]);

        $founded_reference = array_shift($references);
        $reference_id = null;

        if (!empty($founded_reference)) {
            $reference_id = $founded_reference->get_id();
        } else {
            $new_reference = new Reference();
            $new_reference->parse($reference->to_array());
            $new_reference->set_customer_id($customer_id);

            if ($new_reference->save()) {
                $reference_id = $new_reference->get_id();
            }
        }

        return $reference_id;
    }

    public function get_icon(): ?array
    {
        $logo = null;

        if (in_array($this->get_type(), [self::TYPE_VISA, self::TYPE_MASTERCARD, self::TYPE_BLIK])) {
            $logo = [
                'url' => WC_P24_PLUGIN_URL . 'assets/svg/' . $this->get_type() . '.svg',
                'alt' => ucfirst($this->get_type())
            ];
        }

        return $logo;
    }

    public function to_card_simple(): Card_Simple
    {
        $simple = new Card_Simple();
        $simple->set_ref_id($this->get_ref_id());

        return $simple;
    }

    public function has_subscriptions(): bool
    {
        return !!$this->subs;
    }

    public function parse(array $data): void
    {
        foreach ($data as $key => $value) {
            if (isset(self::get_fields()[$key])) {
                switch ($key) {
                    case 'id':
                        $this->set_primary_key((int)$value);
                        break;
                    case 'user_id':
                        $this->set_customer_id((int)$value);
                        break;
                    case 'ref_id':
                        $this->set_ref_id((string)$value);
                        break;
                    case 'valid_to':
                        $this->set_valid_to(new DateTime($value));
                        break;
                    case 'info':
                        $this->set_info((string)$value);
                        break;
                    case 'type':
                        $this->set_type((string)$value);
                        break;
                    case 'hash':
                        $this->set_hash((string)$value);
                        break;
                    case 'status':
                        $this->set_status((int)$value);
                        break;
                }
            } else {
                $this->additionals[$key] = $value;
            }
        }
    }
}
