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
                        <x-primary-button class="w-full flex justify-center py-3 px-4">
                            <span class="flex items-center"> <x-icon name="reset" class="w-4 h-4 mr-2" />
                                {{ __('Reset Password') }} </span>
                        </x-primary-button>
                    </div>
                </form> <!-- Back to Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300"> {{ __('Remember your password?') }} <a
                            href="{{ route('login') }}"
                            class="font-medium text-zinc-600 hover:text-zinc-500 dark:text-zinc-300 dark:hover:text-zinc-200 transition-colors duration-200">
                            {{ __('Sign in here') }} </a>
                    </p>
                </div>
            </x-auth-card>
        </div>
    </div>
</x-guest-layout>
