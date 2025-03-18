<?php

namespace App\Services;

use Psr\Http\Message\ResponseInterface;

class Auth
{
    public function __construct()
    {}

    public function save(ResponseInterface $response): void
    {
        $data = json_decode($response->getBody()->getContents(), true);
        session(['auth' => $data['data']]);
    }

    public function logout(): void
    {
        session(['auth' => null]);
    }

    public function token(): string|null
    {
        return $this->data('token');
    }

    public function customerId(): int|null
    {
        return $this->data('id');
    }

    public function customerEmail(): string|null
    {
        return $this->data('email');
    }

    protected function data($key): mixed
    {
        $data = session('auth');

        if (!$data) {
            return null;
        }

        return $data[$key];
    }
}
