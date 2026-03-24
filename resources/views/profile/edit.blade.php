@php
use Illuminate\Contracts\Auth\MustVerifyEmail;
$initials = collect(explode(' ', $user->username ?? 'U'))->take(2)->map(fn($word) => strtoupper(substr($word, 0, 1)))->join('');
@endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Profile') }}
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        <!-- Profile Header Card -->
        <div class="card-shell">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-slate-600 to-zinc-500 flex items-center justify-center shadow-lg flex-shrink-0">
                    <span class="text-2xl font-bold text-white">{{ $initials }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white truncate">{{ $user->username }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        @if($user->hasVerifiedEmail())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ __('Verified') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ __('Unverified') }}
                            </span>
                        @endif
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            {{ __('User ID') }}: #{{ $user->id }}
                        </span>
                    </div>
                </div>
                <div class="text-right text-sm text-gray-500 dark:text-gray-400 flex-shrink-0">
                    <div>{{ __('Joined') }}</div>
                    <div class="font-medium text-gray-700 dark:text-gray-300">{{ $user->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        @if($isAdmin)
        <!-- Profile Information (Admin Only) -->
        <div class="card-shell">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Profile Information') }}</h3>
            <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="username" :value="__('Username')" />
                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)"
                            required autofocus autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('username')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                            required autocomplete="email" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />

                        @if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail())
                            <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                                {{ __('Your email address is unverified.') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    @if (session('status') === 'profile-updated')
                        @php
                            $updatedLocale = session('locale-updated-value');
                            $updatedLocaleLabel = is_string($updatedLocale) ? ($supportedLocales[$updatedLocale] ?? strtoupper($updatedLocale)) : null;
                        @endphp
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                            class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Saved.') }}
                            @if ($updatedLocaleLabel)
                                {{ __('Current language:') }} {{ $updatedLocaleLabel }}
                            @endif
                        </p>
                    @endif
                </div>
            </form>

            @if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail())
                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="mt-3 inline">
                    @csrf
                    <button type="submit"
                        class="underline text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        {{ __('Resend verification email') }}
                    </button>
                </form>
            @endif
        </div>
        @endif

        <!-- Language Preferences -->
        <div class="card-shell">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Language Preferences') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ __('Choose your preferred language. If you have not selected one, we will use your browser language.') }}
            </p>

            <form method="post" action="{{ route('profile.update-locale') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="space-y-2">
                    <x-input-label for="locale" :value="__('Language')" />
                    <select id="locale" name="locale" class="form-select form-pill">
                        @if (count($supportedLocales) === 0)
                            <option value="{{ $currentLocale }}" selected>
                                {{ strtoupper($currentLocale) }}
                            </option>
                        @endif
                        @foreach ($supportedLocales as $value => $label)
                            <option value="{{ $value }}" {{ $currentLocale === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('locale')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    @if (session('status') === 'locale-updated')
                        @php
                            $updatedLocale = session('locale-updated-value');
                            $updatedLocaleLabel = is_string($updatedLocale) ? ($supportedLocales[$updatedLocale] ?? strtoupper($updatedLocale)) : null;
                        @endphp
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Saved.') }}
                            @if ($updatedLocaleLabel)
                                {{ __('Current language:') }} {{ $updatedLocaleLabel }}
                            @endif
                        </p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Update Password -->
        <div class="card-shell">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Update Password') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
            
            <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                        <x-text-input id="update_password_current_password" name="current_password" type="password"
                            class="mt-1 block w-full" autocomplete="current-password" />
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>

                    <div></div>

                    <div>
                        <x-input-label for="update_password_password" :value="__('New Password')" />
                        <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full"
                            autocomplete="new-password" />
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    @if (session('status') === 'password-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-gray-600 dark:text-gray-300">{{ __('Saved.') }}</p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Delete Account -->
        <div class="card-shell">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Delete Account') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
            </p>
            
            <x-danger-button x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('Delete Account') }}</x-danger-button>

            <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.') }}
                    </p>

                    <div class="mt-6">
                        <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                            placeholder="{{ __('Password') }}" />
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button class="ms-3">
                            {{ __('Delete Account') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>

</x-app-sidebar-layout>
