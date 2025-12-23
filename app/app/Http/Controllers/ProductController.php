<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
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
        protected FrontProduct $frontProduct
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
                uri: 'wc/store/v1/products/categories'
            ),

            'products' => $this->client->getAsync(
                uri: 'wc/store/v1/products',
                options: ['query' => [
                    'page' => 1,
                    'per_page' => 50,
                    'category' => implode(',', (array)$request->category)
                ]]
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $rejected = $this->rejected($responses, ['categories', 'products']);
        if ($rejected) {
            return Inertia::render('Product/Index', [
                'products' => [],
                'filters' => [[
                    'id' => 'category',
                    'name' => __('Category'),
                    'options' => []
                ]],
            ]);
        }

        return Inertia::render('Product/Index', [
            'products' => $this->frontProduct->productsResponse($responses['products']['value']),
            'filters' => [[
                'id' => 'category',
                'name' => __('Category'),
                'options' => $this->frontProduct->productsCategoriesResponse(
                    $responses['categories']['value'],
                    (array)$request->category
                )
            ]]
        ]);
    }

    public function show(Request $request, $product): Response
    {
        $promises = [
            'product' => $this->client->getAsync(
                uri: 'wc/store/v1/products/' . $product
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $rejected = $this->rejected($responses, ['product']);

        if ($rejected) {
            abort(404);
        }

        return Inertia::render('Product/Show', [
            'product' => $this->frontProduct->productResponse($responses['product']['value']),
        ]);
    }
}
