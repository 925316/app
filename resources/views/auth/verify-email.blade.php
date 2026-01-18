<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Title -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-green-600 shadow-lg">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M4 12h16"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('Verify Your Email') }}
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Please verify your email address to get started') }}
                </p>
            </div>

            <div class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm py-8 px-6 shadow-xl rounded-2xl border border-cool-200/50 dark:border-cool-700/50">
                <div class="text-center text-sm text-gray-600 dark:text-gray-300 mb-6">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 bg-green-50/50 dark:bg-green-900/30 border border-green-200/50 dark:border-green-700/50 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="font-medium text-green-600 dark:text-green-300">
                                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                            </span>
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                        @csrf
                        <x-primary-button class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M4 12h16"></path>
                                </svg>
                                {{ __('Resend Verification Email') }}
                            </span>
                        </x-primary-button>
                    </form>

                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-cool-200/50 dark:border-cool-600/50"></span>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="px-2 bg-white/80 dark:bg-cool-800/80 text-gray-500 dark:text-gray-400">Or</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-cool-300/50 dark:border-cool-600/50 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white/50 dark:bg-cool-700/50 hover:bg-gray-50 dark:hover:bg-cool-600/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cool-500 transition-all duration-200">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                {{ __('Log Out') }}
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
