<?php

namespace WC_P24\Models\Simple;

if (!defined('ABSPATH')) {
    exit;
}

class Card_Simple extends Reference_Simple
{
    private string $last_digits = '';

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->parse($data);
        }
    }

    public function set_valid_to(string $valid_to): void
    {
        try {
            $date = new \DateTime();

            if (preg_match('/^(\d{2})(\d{4})$/', trim($valid_to), $matches)) {
                $month = $matches[1];
                $year = $matches[2];
                $date->setDate($year, $month, 1);
            } elseif (preg_match('/^(\d{2})(\d{2})$/', trim($valid_to), $matches)) {
                $year = $matches[1];
                $month = $matches[2];
                $date->setDate('20' . $year, $month, 1);
            }

            $this->valid_to = $date;
        } catch (\Exception $e) {
            $this->valid_to = null;
        }
    }

    public function set_last_digits(string $mask): void
    {
        $mask = preg_replace('/\D+/', '', $mask);

        $this->last_digits = $mask;
        $this->set_info($mask);
    }

    public function parse(array $data): void
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);

            switch ($key) {
                case 'refid':
                    $this->set_ref_id($value);
                    break;
                case 'carddate':
                    $this->set_valid_to($value);
                    break;
                case 'mask':
                    $this->set_last_digits($value);
                    break;
                case 'cardtype':
                    $this->set_type($value);
                    break;
                case 'hash':
                    $this->set_hash($value);
                    break;
            }
        }
    }

    public function to_array(): array
    {
        return array_merge(parent::to_array(), ['last_digits' => $this->last_digits]);
    }
}
