<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use App\Services\FrontCart;
use App\Services\FrontProduct;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class ProductController extends Controller
{
    public function __construct(
        protected FrontProduct $frontProduct,
        protected FrontCart $frontCart,
        protected Auth $auth,
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
            'categories' => $this->client->getAsync(
                uri: 'wc/store/v1/products/categories',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            ),

            'products' => $this->client->getAsync(
                uri: 'wc/store/v1/products',
                options: ['query' => [
                    'page' => 1,
                    'per_page' => 50,
                    'category' => implode(',', (array)$request->category)
                ]]
            ),

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

        $rejected = $this->rejected($responses, ['categories', 'products', 'cart']);
        if ($rejected) {
            return Inertia::render('Product/Index', [
                'products' => [],
                'cart' => [],
                'filters' => [[
                    'id' => 'category',
                    'name' => __('Category'),
                    'options' => []
                ]],
                'logged' => $logged,
            ]);
        }

        return Inertia::render('Product/Index', [
            'products' => $this->frontProduct->productsResponse($responses['products']['value']),
            'cart' => $this->frontCart->cartResponse($responses['cart']['value']),
            'filters' => [[
                'id' => 'category',
                'name' => __('Category'),
                'options' => $this->frontProduct->productsCategoriesResponse(
                    $responses['categories']['value'],
                    (array)$request->category
                )
            ]],
            'logged' => $logged,
        ]);
    }

    public function show(Request $request, $product): Response
    {
        $promises = [
            'product' => $this->client->getAsync(
                uri: 'wc/store/v1/products/' . $product
            ),

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

        $rejected = $this->rejected($responses, ['product', 'cart']);

        if ($rejected) {
            abort(404);
        }

        $this->frontCart->saveCartToken($responses['cart']['value']);

        return Inertia::render('Product/Show', [
            'product' => $this->frontProduct->productResponse($responses['product']['value']),
            'cart' => $this->frontCart->cartResponse($responses['cart']['value']),
            'logged' => $logged,
        ]);
    }
}
