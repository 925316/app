<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Title -->
            <x-auth-header title="{{ __('Create Account') }}" :subtitle="__('Join us today and get started')"
                logoClass="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-accent-600 shadow-lg" />

            <!-- Auth Form Card -->
            <x-auth-card>
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Username -->
                    <div>
                        <x-input-label for="username" :value="__('Username')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="username" name="username" type="text" :value="old('username')" required
                            autofocus autocomplete="username" placeholder="Username" icon="user" />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email Address')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="email" name="email" type="email" :value="old('email')" required
                            autocomplete="username" placeholder="Email" icon="mail" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="password" name="password" type="password" required
                            autocomplete="new-password" placeholder="Password" icon="lock" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="password_confirmation" name="password_confirmation" type="password"
                            required autocomplete="new-password" placeholder="Confirm password" icon="lock" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <x-primary-button
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-accent-600 to-accent-700 hover:from-accent-700 hover:to-accent-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                            <span class="flex items-center">
                                <x-icon name="plus" class="w-4 h-4 mr-2" />
                                {{ __('Create Account') }}
                            </span>
                        </x-primary-button>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}"
                            class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400 dark:hover:text-accent-300 transition-colors duration-200">
                            {{ __('Sign in here') }}
                        </a>
                    </p>
                </div>
            </x-auth-card>
        </div>
    </div>
</x-guest-layout>
