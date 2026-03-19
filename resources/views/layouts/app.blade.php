<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Early theme detection to prevent FOUC -->
    <script>
        (function() {
            const savedThemeVariant = localStorage.getItem('theme-variant') ?? 'default';
            document.documentElement.dataset.theme = savedThemeVariant;

            // Check for saved theme preference or default to system preference
            const savedTheme = localStorage.getItem('theme');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

            // Determine initial theme
            let isDark = false;
            if (savedTheme === 'dark') {
                isDark = true;
            } else if (savedTheme === 'light') {
                isDark = false;
            } else {
                // No saved preference, use system preference
                isDark = prefersDarkScheme.matches;
            }

            // Apply theme immediately
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Listen for system theme changes when no explicit preference is set
            if (savedTheme === null) {
                prefersDarkScheme.addEventListener('change', (e) => {
                    if (localStorage.getItem('theme') === null) {
                        if (e.matches) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    }
                });
            }
        })();
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell-page font-sans antialiased min-h-screen transition-colors duration-300">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="app-shell-header">
                <div class="app-shell-content mx-auto py-6 sm:px-6 lg:px-8">
                    <div class="app-toolbar">
                        <div>{{ $header }}</div>
                        <div class="app-toolbar-actions">
                            <x-theme-preset-toggle />
                        </div>
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
