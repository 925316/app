<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#fafafa">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Early theme detection to prevent FOUC -->
    @include('components.theme-init-script')

    <script>
        (function () {
            const metaThemeColor = document.querySelector('meta[name="theme-color"]');
            if (!metaThemeColor) {
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            metaThemeColor.setAttribute('content', isDark ? '#000000' : '#fafafa');
        })();
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell-page min-h-screen overscroll-y-none font-sans antialiased">
    <div class="min-h-screen">
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
        <main>
            <div class="app-shell-content app-shell-content-wrap mx-auto sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>
