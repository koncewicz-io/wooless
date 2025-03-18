<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class FrontProduct
{
    public function __construct()
    {}

    public function productsResponse(ResponseInterface $body): Collection
    {
        $data = json_decode($body->getBody()->getContents(), true);

        return collect($data)
            ->select(['id', 'name', 'images', 'prices'])
            ->map(function ($item) {
                $item['formated_prices'] = [
                    'price' => $this->formatPrice($item['prices'], 'price')
                ];
                return $item;
            });
    }

    public function productResponse(ResponseInterface $body): array
    {
        $data = json_decode($body->getBody()->getContents(), true);

        $product = [
            'id' => $data['id'],
            'name' => $data['name'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'prices' => $data['prices'],
            'formated_prices' => [
                'price' => $this->formatPrice($data['prices'], 'price')
            ],
            'average_rating' => $data['average_rating'],
            'review_count' => $data['review_count'],
            'images' => $data['images'],
            'is_purchasable' => $data['is_purchasable'],
        ];

        return $product;
    }

    public function productsCategoriesResponse(ResponseInterface $body, $activeCategoryIds): Collection
    {
        $data = json_decode($body->getBody()->getContents(), true);

        return collect($data)
            ->select(['id', 'name'])
            ->map(function ($item) use ($activeCategoryIds) {
                $item['active'] = in_array($item['id'], $activeCategoryIds);
                return $item;
            });
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
