<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->get('lang')) {
            return $next($request);
        }

        if (!in_array($request->get('lang'), ['en', 'pl'])) {
            return $next($request);
        }

        App::setLocale($request->get('lang'));

        return $next($request);
    }
}
