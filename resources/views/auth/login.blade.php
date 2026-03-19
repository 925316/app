<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Title -->
            <x-auth-header title="{{ __('Welcome Back') }}" :subtitle="__('Sign in to your account to continue')" />

            <!-- Auth Form Card -->
            <x-auth-card :showStatus="true">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email Address')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="email" name="email" type="email" :value="old('email')" required
                            autofocus autocomplete="username" :placeholder="__('Email')" icon="user" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="password" name="password" type="password" required
                            autocomplete="current-password" :placeholder="__('Password')" icon="lock" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded transition-colors duration-200"
                                name="remember">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200"
                                href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <x-primary-button
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                            <span class="flex items-center">
                                <x-icon name="login" class="w-4 h-4 mr-2" />
                                {{ __('Sign in') }}
                            </span>
                        </x-primary-button>
                    </div>
                </form>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __("Don't have an account?") }}
                        <a href="{{ route('register') }}"
                            class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                            {{ __('Sign up for free') }}
                        </a>
                    </p>
                </div>
            </x-auth-card>
        </div>
    </div>
</x-guest-layout>
