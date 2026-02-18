<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title> <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Early theme detection to prevent FOUC -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            let isDark = false;
            if (savedTheme === 'dark') {
                isDark = true;
            } else if (savedTheme === 'light') {
                isDark = false;
            } else {
                isDark = prefersDarkScheme.matches;
            }
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
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
    </script> <!-- x-cloak styles to hide elements until Alpine.js initializes -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style> <!-- Initialize Alpine store for sidebar state -->
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
    </script> <!-- Scripts --> @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans antialiased bg-gradient-to-br from-cool-50 to-cool-100 dark:from-cool-900 dark:to-cool-800 min-h-screen transition-colors duration-300">
    <div class="flex min-h-screen" x-data> <!-- Sidebar Navigation --> @include('layouts.sidebar')
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col transition-all duration-300"
            :style="{ marginLeft: $store.sidebar.open ? '16rem' : '4rem' }" x-cloak> <!-- Top Header -->
            <header
                class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm border-b border-cool-200/50 dark:border-cool-700/50 shadow-sm">
                <div class="flex items-center justify-between px-6 py-4"> <!-- Page Title -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            @isset($header)
                                {{ $header }}
                            @else
                                {{ __('Dashboard') }}
                                @endisset </h1> @isset($subheader)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"> {{ $subheader }} </p>
                        @endisset
                    </div> <!-- Additional Header Actions (if any) -->
                    <div class="flex items-center space-x-4"> <!-- Breadcrumb or other actions can go here --> </div>
                </div>
            </header> <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="px-6 py-8"> {{ $slot }} </div>
            </main> <!-- Footer -->
            <footer
                class="bg-white/50 dark:bg-cool-800/50 border-t border-cool-200/50 dark:border-cool-700/50 py-4 px-6">
                <div class="text-center text-sm text-gray-600 dark:text-gray-400"> &copy; {{ date('Y') }}
                    {{ config('app.name', 'Laravel') }}. {{ __('All rights reserved.') }} </div>
            </footer>
        </div>
    </div>
</body>

</html>
