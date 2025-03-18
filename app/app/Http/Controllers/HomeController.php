<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use App\Services\FrontCart;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class HomeController extends Controller
{
    public function __construct(
        protected FrontCart $frontCart,
        protected Auth $auth
    )
    {
        $this->client = new Client([
            'base_uri' => config('services.wordpress.container_url') . '/wordpress/wp-json/',
            'verify' => false,
        ]);
    }

    public function index(Request $request): Response
    {
        $promises = [
            'cart' => $this->client->getAsync(
                uri: 'wc/store/v1/cart',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            ),

            'auth' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token/validate',
                options: [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->auth->token()
                    ]
                ]
            ),
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $logged = false;
        if ($responses['auth']['state'] === 'fulfilled') {
            $logged = true;
        }

        $rejected = $this->rejected($responses, ['cart']);
        if ($rejected) {
            return Inertia::render('Home/Index', [
                'cart' => [],
                'logged' => $logged
            ]);
        }

        $this->frontCart->saveCartToken($responses['cart']['value']);

        return Inertia::render('Home/Index', [
            'cart' => $this->frontCart->cartResponse($responses['cart']['value']),
            'logged' => $logged
        ]);
    }
}
