<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|atkinson-hyperlegible:400,700&display=swap" rel="stylesheet" />

    <!-- Early theme detection to prevent FOUC -->
    @include('components.theme-init-script')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell-page shell-atelier shell-atelier--guest font-sans antialiased min-h-screen transition-colors duration-300"
    data-shell-theme="atelier" data-shell-variant="guest">
    <a href="#guest-content" class="shell-atelier__skip-link">
        {{ __('Skip to guest content') }}
    </a>

    <main id="guest-content" class="guest-shell" aria-label="{{ __('Guest content') }}">
        <div class="guest-shell__frame">
            <div class="auth-shell-stack">
                {{ $slot }}
            </div>
        </div>
    </main>
</body>

</html>
