<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|ibm-plex-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Early theme detection to prevent FOUC -->
    @include('components.theme-init-script')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell-page shell-cinematic shell-cinematic--topnav font-sans antialiased min-h-screen transition-colors duration-300"
    data-shell-theme="cinematic" data-shell-variant="topnav">
    <a href="#app-main-content" class="shell-cinematic__skip-link">
        {{ __('Skip to main content') }}
    </a>

    <div class="app-shell-body min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="app-shell-header">
                <div class="app-shell-content mx-auto py-6 sm:px-6 lg:px-8">
                    <div class="app-toolbar">
                        <div>{{ $header }}</div>
                        <div class="app-toolbar-actions"></div>
                    </div>
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main id="app-main-content">
            <div class="app-shell-content app-shell-content-wrap mx-auto sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>
