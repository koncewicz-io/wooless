<?php

namespace WC_P24\Utilities;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Record
{
    protected bool $new_record = true;
    protected array $attributes = [];
    protected array $additionals = [];

    public static function db()
    {
        global $wpdb;

        return $wpdb;
    }

    abstract public static function table_name(): string;

    abstract protected static function primary_key(): string;

    abstract protected static function get_fields(): array;

    abstract protected function get_primary_key(): int;

    abstract protected function set_primary_key(int $value): void;

    abstract public function parse(array $data): void;

    protected function get_primary_key_name(): string{
        return 'id';
    }
    public function __get($property)
    {
        $result = null;

        if (isset($this->additionals[$property])) {
            $result = $this->additionals[$property];
        }

        return $result;
    }

    protected function build_data(): array
    {
        $attributes = [];
        $attributes_format = [];

        foreach ($this->attributes as $attribute => $value) {
            if (isset($this->get_fields()[$attribute])) {
                $definition = $this->get_fields()[$attribute];
                $parser = isset($definition['parse']) && is_callable($definition['parse']) ? $definition['parse'] : null;

                $attributes[$attribute] = $parser ? $parser($value) : $value;
                $attributes_format[] = $definition['format'];
            }
        }

        return [$attributes, $attributes_format];
    }

    protected static function joins(): string
    {
        return '';
    }

    protected function find(): void
    {
        $query = self::db()->prepare('SELECT t.*
                          FROM ' . static::table_name() . ' as t
                          ' . static::joins() . '
                          WHERE t.id = %d LIMIT 1', $this->get_primary_key());

        try {
            [$record] = self::db()->get_results($query, ARRAY_A);

            if ($record) {
                $this->parse($record);
            } else {
                $this->{$this->get_primary_key_name()} = 0;
            }
        } catch (\Exception $e) {
            $this->{$this->get_primary_key_name()} = 0;
        }
    }

    public static function findAll(array $params): array
    {
        $select = 't.*';
        $where = '';
        $order = '';
        $group_by = '';
        $limit = '';
        $joins = static::joins();

        $_params = [];

        if (!empty($params['select'])) {
            $select = implode(', ', $params['select']);
        }

        if (!empty($params['join'])) {
            $joins = static::joins() . ' ' . $params['join'];
        }

        if (!empty($params['where'])) {
            $query = array_shift($params['where']);
            $where = "WHERE $query";
            $_params = array_merge($_params, $params['where']);
        }

        if (!empty($params['group_by'])) {
            $group_by = 'GROUP BY ' . $params['group_by'];
        }

        if (!empty($params['order'])) {
            $order = 'ORDER BY ' . $params['order'];
        }

        if (!empty($params['limit'])) {
            $limit = 'LIMIT ' . (int)$params['limit'];
        }

        $query_join = implode(' ', array_filter([$joins, $where, $group_by, $order, $limit]));

        $query = "SELECT $select FROM " . static::table_name() . ' as t	' . $query_join . ';';

        if (!empty($_params)) {
            $query = self::db()->prepare($query, $_params);
        }

        $results = self::db()->get_results($query, ARRAY_A);
        $list = [];

        foreach ($results as $result) {
            $item = new static();
            $item->parse($result);

            $list[] = $item;
        }

        return $list;
    }

    protected function create(): ?int
    {
        [$attributes, $formats] = $this->build_data();
        $inserted = self::db()->insert($this->table_name(), $attributes, $formats);

        return $inserted ? self::db()->insert_id : null;
    }

    protected function update(): bool
    {
        [$attributes, $formats] = $this->build_data();

        return (bool)self::db()->update($this->table_name(), $attributes, [static::primary_key() => $this->get_primary_key()], $formats, ['%d']);
    }

    public function delete(): bool
    {
        return self::db()->delete($this->table_name(), [static::primary_key() => $this->get_primary_key()]);
    }

    public function save(): bool
    {
        $result = false;

        if ($this->new_record) {
            $record = $this->create();

            if ($record) {
                $result = true;
                $this->new_record = false;
                $this->set_primary_key($record);
            }
        } else {
            $result = $this->update();
        }

        return $result;
    }
}
