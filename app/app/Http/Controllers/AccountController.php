<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class AccountController extends Controller
{
    public function __construct(protected Auth $auth)
    {
        $this->client = new Client([
            'base_uri' => config('services.wordpress.container_url') . '/wordpress/wp-json/',
            'verify' => false,
        ]);
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $promises = [
            'token' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token/validate',
                options: [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->auth->token()
                    ]
                ]
            ),
        ];

        try {
            Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            return response()->redirectToRoute('login.index');
        }

        return response()->redirectToRoute('order.index');
    }
}
