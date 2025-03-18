<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use App\Services\FrontCart;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class LoginController extends Controller
{
    public function __construct(
        protected FrontCart $frontCart,
        protected Auth $auth,
    )
    {
        $this->client = new Client([
            'base_uri' => config('services.wordpress.container_url') . '/wordpress/wp-json/',
            'verify' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $promises = [
            'auth' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token',
                options: [
                    'json' => [
                        'username' => $request->email,
                        'password' => $request->password
                    ]
                ]
            )
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'exception' => [__('E-mail or password are incorrect.')],
            ]);
        }

        $this->auth->save($responses['auth']);

        return response()->redirectToRoute('order.index');
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $promises = [
            'auth' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token/validate',
                options: [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->auth->token()
                    ]
                ]
            ),

            'cart' => $this->client->getAsync(
                uri: 'wc/store/v1/cart',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        if ($responses['auth']['state'] === 'fulfilled') {
            return response()->redirectToRoute('account.index');
        }

        $rejected = $this->rejected($responses, ['cart']);
        if ($rejected) {
            return Inertia::render('Login/Index', [
                'cart' => []
            ]);
        }

        $this->frontCart->saveCartToken($responses['cart']['value']);

        return Inertia::render('Login/Index', [
            'cart' => $this->frontCart->cartResponse($responses['cart']['value'])
        ]);
    }
}
