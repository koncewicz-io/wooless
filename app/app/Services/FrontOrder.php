<?php

namespace App\Services;

use Psr\Http\Message\ResponseInterface;

class FrontOrder
{
    public function __construct()
    {}

    public function orderResponse(ResponseInterface $body): array
    {
        $data = json_decode($body->getBody()->getContents(), true);

        return [
            'id' => $data['id'],
            'status' => $data['status'],
            'items' => collect($data['items'])
                ->select([
                    'key',
                    'id',
                    'quantity',
                    'name',
                    'short_description',
                    'description',
                    'sku',
                    'images',
                    'prices',
                    'totals'
                ])->map(function ($item) {
                    $item['formatted_prices'] = [
                        'price' => $this->formatPrice($item['prices'], 'price'),
                        'regular_price' => $this->formatPrice($item['prices'], 'regular_price'),
                        'sale_price' => $this->formatPrice($item['prices'], 'sale_price')
                    ];

                    return $item;
                })->toArray(),
            'totals' => $data['totals'],
            'formatted_totals' => [
                'total_price' => $this->formatPrice($data['totals'], 'total_price'),
                'total_items' => $this->formatPrice($data['totals'], 'total_items'),
                'total_shipping' => $this->formatPrice($data['totals'], 'total_shipping'),
            ],
            'shipping_address' => $data['shipping_address'],
            'billing_address' => $data['billing_address'],
            'needs_payment' => $data['needs_payment'],
            'needs_shipping' => $data['needs_shipping']
        ];
    }

    protected function formatPrice($prices, $field = 'price'): string
    {
        return number_format(
            $prices[$field] / (pow(10, $prices['currency_minor_unit'])),
            $prices['currency_minor_unit'],
            $prices['currency_decimal_separator'],
            $prices['currency_thousand_separator']
        );
    }
}
