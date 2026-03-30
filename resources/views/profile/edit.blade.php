@php
use Illuminate\Contracts\Auth\MustVerifyEmail;
$initials = collect(explode(' ', $user->username ?? 'U'))->take(2)->map(fn($word) => strtoupper(substr($word, 0, 1)))->join('');
@endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Profile') }}
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Profile Header Card -->
        <div class="card-shell">
            <div class="flex items-center gap-6">
                <div class="user-avatar h-20 w-20 flex-shrink-0 text-2xl shadow-lg">
                    <span class="text-2xl font-bold text-white">{{ $initials }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="app-shell-heading truncate text-xl font-semibold">{{ $user->username }}</h2>
                    <p class="app-shell-body-copy truncate text-sm">{{ $user->email }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        @if($user->hasVerifiedEmail())
                            <x-status-badge status="active" :text="__('Verified')" />
                        @else
                            <x-status-badge status="warning" :text="__('Unverified')" />
                        @endif
                        <span class="app-shell-body-copy text-xs">
                            {{ __('User ID') }}: #{{ $user->id }}
                        </span>
                    </div>
                </div>
                <div class="app-shell-body-copy flex-shrink-0 text-right text-sm">
                    <div>{{ __('Joined') }}</div>
                    <div class="app-shell-heading font-medium">{{ $user->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        @if($isAdmin)
        <!-- Profile Information (Admin Only) -->
        <div class="card-shell">
            <h3 class="card-form-title mb-4 text-lg font-semibold">{{ __('Profile Information') }}</h3>
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
                            <p class="card-inline-copy mt-2 text-sm">
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
                            class="form-note text-sm">
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
                    <button type="submit" class="form-link text-sm underline">
                        {{ __('Resend verification email') }}
                    </button>
                </form>
            @endif
        </div>
        @endif

        <!-- Language Preferences -->
        <div class="card-shell">
            <h3 class="card-form-title mb-2 text-lg font-semibold">{{ __('Language Preferences') }}</h3>
            <p class="card-form-copy mb-6 text-sm">
                {{ __('Choose your preferred language. If you have not selected one, we will use your browser language.') }}
            </p>

            <form method="post" action="{{ route('profile.update-locale') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="space-y-2">
                    <x-input-label for="locale" :value="__('Language')" />
                    <select id="locale" name="locale" class="form-select">
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
                            class="form-note text-sm">
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
            <h3 class="card-form-title mb-2 text-lg font-semibold">{{ __('Update Password') }}</h3>
            <p class="card-form-copy mb-6 text-sm">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
            
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
                            class="form-note text-sm">{{ __('Saved.') }}</p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Delete Account -->
        <div class="card-shell">
            <h3 class="card-form-title mb-2 text-lg font-semibold">{{ __('Delete Account') }}</h3>
            <p class="card-form-copy mb-6 text-sm">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
            </p>
            
            <x-danger-button x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('Delete Account') }}</x-danger-button>

            <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="card-modal-title text-lg font-medium">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>

                    <p class="card-modal-copy mt-1 text-sm">
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
