<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
        </style>
    @endif
</head>

<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
<div class="container mx-auto px-6 py-12 flex-1 flex items-center justify-center">
    <div class="max-w-4xl w-full mx-auto">
        <!-- Logo and Title Section -->
        <div class="text-center mb-16">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full shadow-lg mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h1 class="text-4xl lg:text-6xl font-bold tracking-tight mb-4">
                Welcome to
                <span class="bg-gradient-to-r from-primary-600 to-primary-700 bg-clip-text text-transparent">
                        {{ config('app.name') }}
                    </span>
            </h1>
            <p class="text-lg lg:text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto leading-relaxed">
                A modern platform for managing licenses, devices, and software packages with ease.
            </p>
        </div>

        <!-- Navigation Section -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="group inline-flex items-center px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <span class="mr-2">Go to Dashboard</span>
                        <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="group inline-flex items-center px-8 py-4 bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-medium rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 transform hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <span class="mr-2">Sign In</span>
                        <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="group inline-flex items-center px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            <span class="mr-2">Get Started</span>
                            <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </a>
                    @endif
                @endauth
            @endif
        </div>

        <!-- Features Section -->
        <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center group">
                <div
                    class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl mb-4">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">License Management</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                    Easily create, manage, and track software licenses with detailed analytics and expiration
                    monitoring.
                </p>
            </div>

            <div class="text-center group">
                <div
                    class="inline-flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl mb-4">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Device Tracking</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                    Monitor and manage device registrations with real-time status updates and usage statistics.
                </p>
            </div>

            <div class="text-center group">
                <div
                    class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl mb-4">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Package Distribution</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                    Distribute software packages securely with version control and download analytics.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="border-t border-gray-200 dark:border-gray-800 py-6">
    <div class="container mx-auto px-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Built with Laravel & Tailwind CSS.</p>
    </div>
</footer>
</body>

</html>
