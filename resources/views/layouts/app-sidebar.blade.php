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

    <!-- x-cloak styles to hide elements until Alpine.js initializes -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Initialize Alpine store for sidebar state -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                open: localStorage.getItem('sidebar-collapsed') === 'true' ? false : true,
                toggle() {
                    this.open = !this.open;
                    localStorage.setItem('sidebar-collapsed', !this.open);
                }
            });
        });
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans antialiased bg-gradient-to-br from-cool-50 to-cool-100 dark:from-cool-900 dark:to-cool-800 min-h-screen overflow-x-hidden transition-colors duration-300">
    <div class="flex min-h-screen w-full overflow-x-hidden" x-data>
        <!-- Sidebar Navigation -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="relative z-0 flex flex-col w-full min-w-0 transition-all duration-300 ml-0"
            :class="{ 'lg:ml-64 lg:w-[calc(100%-16rem)]': $store.sidebar.open, 'lg:ml-16 lg:w-[calc(100%-4rem)]': !$store.sidebar.open }" x-cloak>
            <!-- Top Header -->
            <header
                class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm border-b border-cool-200/50 dark:border-cool-700/50 shadow-sm">
                <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <!-- Page Title -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            @isset($header)
                                {{ $header }}
                            @else
                                {{ __('Dashboard') }}
                            @endisset
                        </h1>
                        @isset($subheader)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $subheader }}
                            </p>
                        @endisset
                    </div>

                    <!-- Additional Header Actions (if any) -->
                    <div class="flex items-center space-x-4">
                        <!-- Breadcrumb or other actions can go here -->
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 min-w-0 overflow-y-auto overflow-x-hidden">
                <div class="py-8">
                    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer
                class="bg-white/50 dark:bg-cool-800/50 border-t border-cool-200/50 dark:border-cool-700/50 py-4 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('All rights reserved.') }}
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
