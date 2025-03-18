<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use App\Services\FrontCart;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class CartController extends Controller
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
            return Inertia::render('Cart/Index', [
                'cart' => [],
                'logged' => $logged
            ]);
        }

        $this->frontCart->saveCartToken($responses['cart']['value']);

        return Inertia::render('Cart/Index', [
            'cart' => $this->frontCart->cartResponse($responses['cart']['value']),
            'logged' => $logged
        ]);
    }

    public function selectShippingRate(Request $request)
    {
        $rules = [
            'package_id' => 'required',
            'rate_id' => 'required'
        ];

        if ($request->furgonetka) {
            $rules['furgonetka.selected_point.service'] = 'required|nullable';
            $rules['furgonetka.selected_point.service_type'] = 'required|nullable';
            $rules['furgonetka.selected_point.code'] = 'required|nullable';
            $rules['furgonetka.selected_point.name'] = 'required|nullable';
        }

        $request->validate($rules);

        $promises = [
            'cart' => $this->client->postAsync(
                uri: 'wc/store/v1/cart/select-shipping-rate',
                options: [
                    'headers' => ['Cart-Token' => $this->frontCart->cartToken()],
                    'json' => [
                        'package_id' => $request->package_id,
                        'rate_id' => $request->rate_id,
                    ]
                ]
            ),
        ];

        try {
            Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'exception' => ['Can not select shipping rate.'],
            ]);
        }

        if ($request->furgonetka) {
            $promises = [
                'extensions' => $this->client->postAsync(
                    uri: 'wc/store/v1/cart/extensions',
                    options: [
                        'headers' => ['Cart-Token' => $this->frontCart->cartToken()],
                        'json' => [
                            'namespace' => 'furgonetka',
                            'data' => [
                                'service' => $request->furgonetka['selected_point']['service'],
                                'service_type' => $request->furgonetka['selected_point']['service_type'],
                                'code' => $request->furgonetka['selected_point']['code'],
                                'name' => $request->furgonetka['selected_point']['name'],
                                'cod' => false
                            ],
                        ]
                    ]
                )
            ];

            try {
                Promise\Utils::unwrap($promises);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'exception' => ['Can not select shipping rate.'],
                ]);
            }
        }

        if ($request->redirect_back) {
            return redirect()->back();
        }

        return redirect()->route('checkout.payment');
    }

    public function updateCustomer(Request $request)
    {
        $promises = [
            'auth' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token/validate',
                options: ['headers' => ['Authorization' => 'Bearer ' . $this->auth->token()]]
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $logged = false;
        if ($responses['auth']['state'] === 'fulfilled') {
            $logged = true;
        }

        $rules = [
            'billing_address' => 'required|string|max:100',
            'billing_postal_code' => 'required|string|max:10',
            'billing_city' => 'required|string|max:50',
            'billing_country' => 'required|string|max:2'
        ];

        if (!$logged) {
            $rules['email'] = 'required|string|lowercase|email|max:100';
        }

        if (!$logged && $request->create_account) {
            $rules['password'] = ['required', Password::min(8)->letters()->numbers()->symbols()];
        }

        $request->validate($rules);

        $email = $request->email;
        if ($logged) {
            $email = $this->auth->customerEmail();
        }

        $promises = [
            'cart' => $this->client->postAsync(
                uri: 'wc/store/v1/cart/update-customer',
                options: [
                    'headers' => ['Cart-Token' => $this->frontCart->cartToken()],
                    'json' => [
                        'billing_address' => [
                            'first_name' => 'Marek',
                            'last_name' => 'Koncewicz',
                            'email' => $email,
                            'address_1' => $request->billing_address,
                            'postcode' => $request->billing_postal_code,
                            'city' => $request->billing_city,
                            'country' => $request->billing_country,
                        ],
                        'shipping_address' => [
                            'first_name' => 'Marek',
                            'last_name' => 'Koncewicz',
                            'email' => $request->email,
                            'address_1' => $request->billing_address,
                            'postcode' => $request->billing_postal_code,
                            'city' => $request->billing_city,
                            'country' => $request->billing_country,
                        ],
                    ]
                ]
            ),
        ];

        try {
            Promise\Utils::unwrap($promises);
        } catch (RequestException $e) {
            $this->exceptionMessage($e);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'exception' => [__('Can not update address.')],
            ]);
        }

        $this->frontCart->clearCreateAccountPassword();
        if ($request->create_account) {
            $this->frontCart->saveCreateAccountPassword($request->password);
        }

        return redirect()->route('checkout.shipping');
    }

    protected function exceptionMessage(RequestException $e)
    {
        $response = json_decode($e->getResponse()->getBody(), true);

        if (!isset($response['code']) || $response['code'] !== 'rest_invalid_param') {
            throw ValidationException::withMessages([
                'exception' => [__('Can not update address.')],
            ]);
        }

        $errors = [];
        $fieldMapping = [
            'billing_address' => [
                'invalid_email' => 'email',
                'invalid_first_name' => 'billing_first_name',
                'invalid_last_name' => 'billing_last_name',
                'invalid_company' => 'billing_company',
                'invalid_address_1' => 'billing_address',
                'invalid_city' => 'billing_city',
                'invalid_state' => 'billing_state',
                'invalid_postcode' => 'billing_postal_code',
                'invalid_country' => 'billing_country',
                'invalid_phone' => 'billing_phone',
            ],
            'shipping_address' => [
                'invalid_email' => 'email',
                'invalid_first_name' => 'shipping_first_name',
                'invalid_last_name' => 'shipping_last_name',
                'invalid_company' => 'shipping_company',
                'invalid_address_1' => 'shipping_address',
                'invalid_city' => 'shipping_city',
                'invalid_state' => 'shipping_state',
                'invalid_postcode' => 'shipping_postal_code',
                'invalid_country' => 'shipping_country',
                'invalid_phone' => 'shipping_phone',
            ],
        ];

        foreach ($response['data']['details'] as $field => $details) {
            if (!isset($fieldMapping[$field])) {
                continue;
            }

            if (!isset($fieldMapping[$field][$details['code']])) {
                continue;
            }

            $errors[$fieldMapping[$field][$details['code']]] = __('The provided data is not valid.');
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        throw ValidationException::withMessages([
            'exception' => [__('Can not update address.')]
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function addItem(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|int',
            'quantity' => 'required|int',
        ]);

        $promises = [
            'cart' => $this->client->postAsync(
                uri: 'wc/store/v1/cart/add-item',
                options: [
                    'headers' => ['Cart-Token' => $this->frontCart->cartToken()],
                    'json' => ['id' => $request->id, 'quantity' => $request->quantity]
                ]
            ),
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'error' => ['Can not add item to your cart.'],
            ]);
        }

        $this->frontCart->saveCartToken($responses['cart']);

        return redirect()->route('cart.index');
    }

    /**
     * @throws ValidationException
     */
    public function removeItem(Request $request): RedirectResponse
    {
        $request->validate([
            'key' => 'required',
        ]);

        $promises = [
            'cart' => $this->client->postAsync(
                uri: 'wc/store/v1/cart/remove-item',
                options: [
                    'headers' => ['Cart-Token' => $this->frontCart->cartToken()],
                    'json' => ['key' => $request->key]
                ]
            ),
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'error' => ['Can not remove item from your cart.'],
            ]);
        }

        $this->frontCart->saveCartToken($responses['cart']);

        return redirect()->route('cart.index');
    }

    /**
     * @throws ValidationException
     */
    public function updateItem(Request $request): RedirectResponse
    {
        $request->validate([
            'key' => 'required',
            'quantity' => 'required|int',
        ]);

        $promises = [
            'cart' => $this->client->postAsync(
                uri: 'wc/store/v1/cart/update-item',
                options: [
                    'headers' => ['Cart-Token' => $this->frontCart->cartToken()],
                    'json' => ['key' => $request->key, 'quantity' => $request->quantity]
                ]
            ),
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'error' => ['Can not update item from your cart.'],
            ]);
        }

        $this->frontCart->saveCartToken($responses['cart']);

        return redirect()->route('cart.index');
    }
}
