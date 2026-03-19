<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8"> <!-- Logo and Title -->
            <x-auth-header title="{{ __('Reset Password') }}" :subtitle="__('Set a new password for your account')" />
            <!-- Auth Form Card -->
            <x-auth-card>
                <form method="POST" action="{{ route('password.store') }}" class="space-y-6"> @csrf
                    <!-- Password Reset Token --> <input type="hidden" name="token"
                        value="{{ $request->route('token') }}" />
                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email Address')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="email" name="email" type="email" :value="old('email', $request->email)" required
                            autofocus autocomplete="username" :placeholder="__('your@email.com')" icon="mail" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div> <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('New Password')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="password" name="password" type="password" required
                            autocomplete="new-password" placeholder="••••••••" icon="lock" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div> <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm New Password')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <x-input-with-icon id="password_confirmation" name="password_confirmation" type="password"
                            required autocomplete="new-password" placeholder="••••••••" icon="lock" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div> <!-- Submit Button -->
                    <div>
                        <x-primary-button
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                            <span class="flex items-center"> <x-icon name="reset" class="w-4 h-4 mr-2" />
                                {{ __('Reset Password') }} </span>
                        </x-primary-button>
                    </div>
                </form> <!-- Back to Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300"> {{ __('Remember your password?') }} <a
                            href="{{ route('login') }}"
                            class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                            {{ __('Sign in here') }} </a>
                    </p>
                </div>
            </x-auth-card>
        </div>
    </div>
</x-guest-layout>
