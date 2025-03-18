<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use App\Services\FrontCart;
use App\Services\FrontOrder;
use App\Services\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class CheckoutController extends Controller
{
    protected const array paymentMethods = [
        'blik' => '154',
        'ing' => '112',
        'pko' => '31',
        'mbank' => '270',
        'santander' => '20',
    ];

    public function __construct(
        protected FrontCart $frontCart,
        protected FrontOrder $frontOrder,
        protected Auth $auth,
        protected Order $order
    )
    {
        $this->client = new Client([
            'base_uri' => config('services.wordpress.container_url') . '/wordpress/wp-json/',
            'verify' => false,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:blik,ing,mbank,pko,santander'
        ]);

        $promises = [
            'auth' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token/validate',
                options: ['headers' => ['Authorization' => 'Bearer ' . $this->auth->token()]]
            ),
            'cart' => $this->client->getAsync(
                uri: 'wc/store/v1/cart',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $logged = false;
        if ($responses['auth']['state'] === 'fulfilled') {
            $logged = true;
        }

        $rejected = $this->rejected($responses, ['cart']);
        if ($rejected) {
            throw ValidationException::withMessages([
                'exception' => ['Can not process.'],
            ]);
        }

        $this->frontCart->saveCartToken($responses['cart']['value']);

        $cart = $this->frontCart->cartResponse($responses['cart']['value']);

        if (!$cart['items_count']) {
            throw ValidationException::withMessages([
                'exception' => ['Can not process.'],
            ]);
        }

        if (!$cart['billing_address']['email']) {
            throw ValidationException::withMessages([
                'exception' => ['Can not process.'],
            ]);
        }

        $password = $this->frontCart->createAccountPassword();

        $checkoutHeaders = [
            'Cart-Token' => $this->frontCart->cartToken()
        ];

        if ($logged) {
            $password = null;
            $cart['billing_address']['email'] = $this->auth->customerEmail();
            $checkoutHeaders['Authorization'] = 'Bearer ' . $this->auth->token();
        }

        $promises = [
            'checkout' => $this->client->postAsync(
                uri: 'wc/store/v1/checkout',
                options: [
                    'headers' => $checkoutHeaders,
                    'json' => [
                        'create_account' => (bool)$password,
                        'customer_password' => $password ?? '',
                        'billing_address' => $cart['billing_address'],
                        'shipping_address' => $cart['shipping_address'],
                        'payment_method' => 'p24-online-payments-' . self::paymentMethods[$request->payment_method],
                        'payment_data' => [
                            ['key' => 'regulation', 'value' => true],
                            ['key' => 'wc-p24-online-payments-' . self::paymentMethods[$request->payment_method] . '-new-payment-method', 'value' => false]
                        ]
                    ]
                ]
            ),
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
        } catch (RequestException $e) {
            $this->processExceptionMessage($e);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'exception' => ['Can not process.'],
            ]);
        }

        $order = json_decode($responses['checkout']->getBody()->getContents(), true);

        return Inertia::location($order['payment_result']['redirect_url']);
    }

    /**
     * @throws ValidationException
     */
    protected function processExceptionMessage(RequestException $e)
    {
        $response = json_decode($e->getResponse()->getBody(), true);

        if (!isset($response['code'])) {
            throw ValidationException::withMessages([
                'exception' => ['Can not process.'],
            ]);
        }

        throw ValidationException::withMessages([
            'exception' => [$response['message']],
        ]);
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $promises = [
            'auth' => $this->client->postAsync(
                uri: 'jwt-auth/v1/token/validate',
                options: ['headers' => ['Authorization' => 'Bearer ' . $this->auth->token()]]
            ),
            'settings' => $this->client->getAsync(
                uri: 'wc/store/v1/settings'
            ),
            'cart' => $this->client->getAsync(
                uri: 'wc/store/v1/cart',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            )
        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $logged = false;
        if ($responses['auth']['state'] === 'fulfilled') {
            $logged = true;
        }

        $rejected = $this->rejected($responses, ['cart']);
        if ($rejected) {
            abort(503);
        }

        $this->frontCart->saveCartToken($responses['cart']['value']);
        $cart = $this->frontCart->cartResponse($responses['cart']['value']);

        if (!$cart['items_count']) {
            return redirect()->route('cart.index');
        }

        $password = $this->frontCart->createAccountPassword();

        return Inertia::render('Checkout/Index', [
            'settings' => json_decode($responses['settings']['value']->getBody()->getContents(), true),
            'cart' => $cart,
            'create_account' => (bool)$password,
            'password' => $password,
            'logged' => $logged,
            'customer_email' => $logged ? $this->auth->customerEmail() : '',
        ]);
    }

    public function shipping(Request $request): Response|RedirectResponse
    {
        $promises = [
            'cart' => $this->client->getAsync(
                uri: 'wc/store/v1/cart',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            ),
            'settings' => $this->client->getAsync(
                uri: 'wc/store/v1/settings'
            ),
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            abort(404);
        }

        $this->frontCart->saveCartToken($responses['cart']);

        $cart = $this->frontCart->cartResponse($responses['cart']);

        if (!$cart['items_count']) {
            return redirect()->route('cart.index');
        }

        if (!$cart['billing_address']['email']) {
            return redirect()->route('checkout.index');
        }

        return Inertia::render('Checkout/Shipping', [
            'cart' => $cart,
            'settings' => json_decode($responses['settings']->getBody()->getContents(), true),
        ]);
    }

    public function payment(Request $request): Response|RedirectResponse
    {
        $promises = [
            'cart' => $this->client->getAsync(
                uri: 'wc/store/v1/cart',
                options: ['headers' => ['Cart-Token' => $this->frontCart->cartToken()]]
            ),
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
        } catch (\Throwable $e) {
            abort(404);
        }

        $this->frontCart->saveCartToken($responses['cart']);

        $cart = $this->frontCart->cartResponse($responses['cart']);

        if (!$cart['items_count']) {
            return redirect()->route('cart.index');
        }

        if (!$cart['billing_address']['email']) {
            return redirect()->route('checkout.index');
        }

        return Inertia::render('Checkout/Payment', [
            'cart' => $cart,
        ]);
    }

    public function order(Request $request, $order): Response|RedirectResponse
    {
        if (in_array(request()->header('Referer'), [
            'https://sandbox-go.przelewy24.pl/',
            'https://go.przelewy24.pl/'
        ])) {
            $this->frontCart->clearCartToken();
            $this->frontCart->clearCreateAccountPassword();

            return response()
                ->redirectToRoute('checkout.order', ['order' => $order, 'key' => $request->key])
                ->header('Referrer-Policy', 'no-referrer');
        }

        $promises = [
            'order' => $this->client->getAsync(
                uri: 'wc/store/v1/order/' . $order . '?key=' . $request->key . '&billing_email=' . $request->email,
                options: ['headers' => ['Authorization' => 'Bearer ' . $this->auth->token()]]
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

        $rejected = $this->rejected($responses, ['cart']);
        if ($rejected) {
            abort(503);
        }

        $rejected = $this->rejected($responses, ['order']);
        $orderRejected = false;
        $orderRejectedCode = null;
        if ($rejected) {
            $orderRejected = true;
            $orderRejectedCode = $this->orderRejectedCode($responses['order']['reason']);
        }

        $this->frontCart->saveCartToken($responses['cart']['value']);

        $orderId = $order;
        $order = [];
        if (!$orderRejected) {
            $order = $this->frontOrder->orderResponse($responses['order']['value']);
        }

        return Inertia::render('Checkout/Order', [
            'order' => $order,
            'cart' => $this->frontCart->cartResponse($responses['cart']['value']),
            'orderId' => (int)$orderId,
            'logged' => $logged,
            'orderRejected' => $orderRejected,
            'orderRejectedCode' => $orderRejectedCode,
        ]);
    }

    protected function orderRejectedCode($reason)
    {
        $data = [];
        if ($reason instanceof RequestException && $reason->hasResponse()) {
            $data = json_decode($reason->getResponse()->getBody(), true);
        }

        return $data['code'] ?? null;
    }
}
