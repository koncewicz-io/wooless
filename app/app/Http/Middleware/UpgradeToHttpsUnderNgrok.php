<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class UpgradeToHttpsUnderNgrok
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedDomains = ['.ngrok.app', '.ngrok-free.app'];

        if (array_filter($allowedDomains, fn($domain) => str_ends_with($request->getHost(), $domain))) {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
