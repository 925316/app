<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8"> <!-- Logo and Title --> <x-auth-header title="{{ __('Forgot Password') }}"
                :subtitle="__('Reset your password in a few steps')" /> <!-- Auth Form Card --> <x-auth-card :showStatus="true">
                <form method="POST" action="{{ route('password.email') }}" class="space-y-6"> @csrf <div
                        class="text-center text-sm text-gray-600 dark:text-gray-300 mb-6">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    </div> <!-- Email Address -->
                    <div> <x-input-label for="email" :value="__('Email Address')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" /> <x-input-with-icon
                            id="email" name="email" type="email" :value="old('email')" required autofocus
                            :placeholder="__('your@email.com')" icon="mail" /> <x-input-error :messages="$errors->get('email')"
                            class="mt-2" /> </div> <!-- Submit Button -->
                    <div> <x-primary-button class="w-full flex justify-center py-3 px-4">
                            <span class="flex items-center"> <x-icon name="mail" class="w-4 h-4 mr-2" />
                                {{ __('Send Reset Link') }} </span> </x-primary-button> </div>
                </form> <!-- Back to Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300"> {{ __('Remember your password?') }} <a
                            href="{{ route('login') }}"
                            class="font-medium text-zinc-600 hover:text-zinc-500 dark:text-zinc-300 dark:hover:text-zinc-200 transition-colors duration-200">
                            {{ __('Sign in here') }} </a> </p>
                </div>
            </x-auth-card> </div>
    </div>
</x-guest-layout>
