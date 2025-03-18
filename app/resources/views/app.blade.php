@php use Illuminate\Support\Facades\Vite; @endphp
<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <style>
            @font-face {
                font-family: 'InterVariable';
                font-style: normal;
                font-weight: 100 900;
                font-display: swap;
                src: url('{{ Vite::asset('resources/fonts/inter/InterVariable.woff2') }}') format('woff2');
            }

            @font-face {
                font-family: 'InterVariable';
                font-style: italic;
                font-weight: 100 900;
                font-display: swap;
                src: url('{{ Vite::asset('resources/fonts/inter/InterVariable-Italic.woff2') }}') format('woff2');
            }
        </style>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="h-full font-sans antialiased">
        @inertia
    </body>
</html>
