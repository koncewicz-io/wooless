<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Psr\Http\Message\ResponseInterface;

class FrontCart
{
    public function __construct()
    {}

    public function saveCreateAccountPassword($password): void
    {
        session(['create_account_password' => Crypt::encryptString($password)]);
    }

    public function createAccountPassword(): string|null
    {
        $data = session('create_account_password');

        if (!$data) {
            return null;
        }

        return Crypt::decryptString($data);
    }

    public function clearCreateAccountPassword(): void
    {
        session(['create_account_password' => null]);
    }

    public function saveCartToken(ResponseInterface $response): void
    {
        session(['cart_token' => $response->getHeader('Cart-Token')[0]]);
    }

    public function cartToken(): string|null
    {
        return session('cart_token');
    }

    public function clearCartToken(): void
    {
        session(['cart_token' => null]);
    }

    public function cartResponse(ResponseInterface $body): array
    {
        $data = json_decode($body->getBody()->getContents(), true);

        return [
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
            'items_count' => $data['items_count'],
            'needs_payment' => $data['needs_payment'],
            'shipping_rates' => collect($data['shipping_rates'])
                ->select([
                    'package_id',
                    'shipping_rates'
                ])
                ->map(function ($shipment) {
                    $shipment['shipping_rates'] = collect($shipment['shipping_rates'])
                        ->map(function ($rate) {
                            $rate['formated_price'] = $this->formatPrice($rate, 'price');

                            return $rate;
                        })->toArray();
                    return $shipment;
                })
                ->toArray(),
            'extensions' => $data['extensions']
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
