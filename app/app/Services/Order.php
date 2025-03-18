<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class Order
{
    public function __construct()
    {}

    public function orderResponse(ResponseInterface $body): array
    {
        $data = json_decode($body->getBody()->getContents(), true);
        $data = $this->response([$data]);

        return $data->first();
    }

    public function ordersResponse(ResponseInterface $body): Collection
    {
        $data = json_decode($body->getBody()->getContents(), true);
        return $this->response($data);
    }

    protected function response(array $data): Collection
    {
        return collect($data)
            ->select([
                'id',
                'status',
                'order_key',
                'payment_method_title',
                'needs_payment',
                'needs_processing',
                'shipping_total',
                'currency_symbol',
                'line_items',
                'total',
                'shipping',
                'billing'
            ])
            ->map(function ($item) {
                $item['line_items'] = collect($item['line_items'])
                    ->select(['id', 'name', 'product_id', 'quantity', 'sku', 'image', 'price', 'subtotal', 'total'])
                    ->map(function ($rate) {
                        return $rate;
                    })->toArray();
                return $item;
            });
    }
}
