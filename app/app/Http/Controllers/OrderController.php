<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use App\Services\FrontCart;
use App\Services\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class OrderController extends Controller
{
    public function __construct(
        protected FrontCart $frontCart,
        protected Auth $auth,
        protected Order $order
    )
    {
        $this->client = new Client([
            'base_uri' => config('services.wordpress.container_url') . '/wordpress/wp-json/',
            'verify' => false,
        ]);
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $promises = [
            'auth' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token/validate',
                options: ['headers' => ['Authorization' => 'Bearer ' . $this->auth->token()]]
            ),
            'cart' => $this->client->getAsync(
                uri: 'wc/store/v1/cart',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            ),
            'orders' => $this->client->getAsync(
                uri: 'wc/v3/orders',
                options: [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode(env('WORDPRESS_WC_CUSTOMER_KEY') . ':' . env('WORDPRESS_WC_CUSTOMER_SECRET'))
                    ],
                    'query' => [
                        'customer' => $this->auth->customerId(),
                        'page' => 1,
                        'per_page' => 10,
                        'orderby' => 'date',
                        'order' => 'desc'
                    ]
                ]
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        if ($responses['auth']['state'] !== 'fulfilled') {
            return response()->redirectToRoute('account.index');
        }

        $cart = [];
        $orders = [];

        if ($responses['cart']['state'] === 'fulfilled') {
            $cart = $this->frontCart->cartResponse($responses['cart']['value']);
        }

        if ($responses['orders']['state'] === 'fulfilled') {
            $orders = $this->order->ordersResponse($responses['orders']['value']);
        }

        return Inertia::render('Order/Index', [
            'cart' => $cart,
            'orders' => $orders,
            'logged' => true
        ]);
    }
}
