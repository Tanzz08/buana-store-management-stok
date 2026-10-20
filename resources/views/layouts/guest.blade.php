<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Buana Store') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Stylesheet -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">

        <!-- Alpine.js (compiled via Vite) -->
        @vite(['resources/js/app.js'])
    </head>
    <body>
        <div class="guest-shell">
            <div class="guest-card">
                <!-- Logo -->
                <div class="guest-logo">
                    <a href="/" aria-label="{{ config('app.name', 'Buana Store') }} — Beranda">
                        <div class="guest-logo__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </div>
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
