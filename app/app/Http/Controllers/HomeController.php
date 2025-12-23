<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

#[AllowDynamicProperties] class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Home/Index');
    }
}
