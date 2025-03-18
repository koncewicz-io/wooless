<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Services\Auth;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * @property Client $client
 */
#[AllowDynamicProperties] class LogoutController extends Controller
{
    public function __construct(protected Auth $auth)
    {
        $this->client = new Client([
            'base_uri' => config('services.wordpress.container_url') . '/wordpress/wp-json/',
            'verify' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->auth->logout();
        return response()->redirectToRoute('account.index');
    }
}
