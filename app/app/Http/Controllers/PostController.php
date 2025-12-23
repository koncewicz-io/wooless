<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\FrontPost;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class PostController extends Controller
{
    public function __construct(
        protected FrontPost $frontPost
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
            'posts' => $this->client->getAsync(
                uri: 'wp/v2/posts',
                options: ['query' => [
                    '_embed' => 'wp:featuredmedia',
                    '_fields' => 'id,title,excerpt,_links.wp:featuredmedia,_embedded.wp:featuredmedia',
                    'page' => 1,
                    'per_page' => 10,
                    'status' => 'publish',
                    'type' => 'post',
                    'categories' => (array)$request->category,
                ]],
            ),

            'categories' => $this->client->getAsync(
                uri: 'wp/v2/categories',
                options: ['query' => [
                    'page' => 1,
                    'per_page' => 10,
                    'exclude' => 1
                ]],
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $rejected = $this->rejected($responses, ['posts', 'categories']);
        if ($rejected) {
            return Inertia::render('Post/Index', [
                'posts' => [],
                'filters' => [
                    [
                        'id' => 'category',
                        'name' => __('Category'),
                        'options' => []
                    ],
                ]
            ]);
        }

        return Inertia::render('Post/Index', [
            'posts' => $this->frontPost->postsResponse($responses['posts']['value']),
            'filters' => [
                [
                    'id' => 'category',
                    'name' => __('Category'),
                    'options' => $this->frontPost->postsCategoriesResponse(
                        $responses['categories']['value'],
                        (array)$request->category
                    )
                ],
            ]
        ]);
    }
}
