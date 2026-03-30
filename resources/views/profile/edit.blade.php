@php
use Illuminate\Contracts\Auth\MustVerifyEmail;

$initials = collect(explode(' ', $user->username ?? 'U'))
    ->take(2)
    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
    ->join('');
@endphp

<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Profile') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Keep your account details, security settings, and recovery actions organized in one quieter workspace.') }}
    </x-slot>

    @php
        $updatedLocale = session('locale-updated-value');
        $updatedLocaleLabel = is_string($updatedLocale) ? ($supportedLocales[$updatedLocale] ?? strtoupper($updatedLocale)) : null;
    @endphp

    <div class="mx-auto max-w-5xl space-y-8" data-page="profile-edit">
        <section class="card-shell overflow-hidden">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.7fr)_minmax(17rem,0.9fr)] lg:items-start">
                <div class="flex min-w-0 items-start gap-5 sm:gap-6">
                    <div class="user-avatar flex h-20 w-20 shrink-0 items-center justify-center text-2xl shadow-lg">
                        <span class="text-2xl font-bold text-white">{{ $initials }}</span>
                    </div>

                    <div class="min-w-0 space-y-4">
                        <div class="space-y-2">
                            <p class="section-kicker">{{ __('Account overview') }}</p>
                            <div>
                                <h2 class="app-shell-heading truncate text-2xl font-semibold">{{ $user->username }}</h2>
                                <p class="app-shell-body-copy truncate text-sm sm:text-base">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            @if ($user->hasVerifiedEmail())
                                <x-status-badge status="active" :text="__('Verified')" />
                            @else
                                <x-status-badge status="warning" :text="__('Unverified')" />
                            @endif

                            <span class="badge badge-default">
                                {{ __('User ID') }}: #{{ $user->id }}
                            </span>
                        </div>

                        <div class="card-shell-muted grid gap-4 p-5 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <p class="section-kicker">{{ __('Profile editing') }}</p>
                                <p class="app-shell-body-copy text-sm">
                                    @if ($isAdmin)
                                        {{ __('You can update your username and email here. Language changes now live only in the sidebar account controls.') }}
                                    @else
                                        {{ __('Your account details are shown for reference here. Language changes now live only in the sidebar account controls.') }}
                                    @endif
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <p class="section-kicker">{{ __('Member since') }}</p>
                                <p class="app-shell-heading text-base font-semibold">{{ $user->created_at->format('M d, Y') }}</p>
                                <p class="app-shell-body-copy text-sm">{{ $user->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="card-shell-muted flex h-full flex-col justify-between gap-5 p-6">
                    <div class="space-y-3">
                        <p class="section-kicker">{{ __('Language') }}</p>
                        <h3 class="card-heading text-lg font-semibold">{{ __('Sidebar control only') }}</h3>
                        <p class="app-shell-body-copy text-sm">
                            {{ __('Use the language menu in the sidebar account area to change your locale. This page no longer repeats that control so profile settings stay focused.') }}
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Security') }}</p>
                            <p class="app-shell-body-copy text-sm">{{ __('Password changes and account deletion remain available below.') }}</p>
                        </div>

                        @if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Verification') }}</p>
                                <p class="app-shell-body-copy text-sm">{{ __('Your email still needs to be verified before all profile actions are fully confirmed.') }}</p>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)] xl:items-start">
            <section class="space-y-6" aria-label="{{ __('Profile settings') }}">
                @if ($isAdmin)
                    <section class="card-shell space-y-6">
                        <div class="space-y-2">
                            <p class="section-kicker">{{ __('Profile details') }}</p>
                            <div>
                                <h3 class="card-form-title text-lg font-semibold">{{ __('Profile Information') }}</h3>
                                <p class="card-form-copy text-sm">
                                    {{ __('Update the account name and email address used across the application.') }}
                                </p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                            @csrf
                            @method('patch')

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div class="card-shell-muted space-y-3 p-5">
                                    <div>
                                        <x-input-label for="username" :value="__('Username')" />
                                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)"
                                            required autofocus autocomplete="username" />
                                        <x-input-error class="mt-2" :messages="$errors->get('username')" />
                                    </div>
                                </div>

                                <div class="card-shell-muted space-y-3 p-5">
                                    <div>
                                        <x-input-label for="email" :value="__('Email')" />
                                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                                            required autocomplete="email" />
                                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                    </div>

                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('status') === 'profile-updated')
                                    <div class="card-shell-muted px-4 py-3">
                                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                                            class="form-note text-sm">
                                            {{ __('Saved.') }}
                                            @if ($updatedLocaleLabel)
                                                {{ __('Current language:') }} {{ $updatedLocaleLabel }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </form>

                        @if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="card-shell-muted space-y-3 p-5">
                                <p class="section-kicker">{{ __('Verification required') }}</p>
                                <p class="card-inline-copy text-sm">
                                    {{ __('Your email address is unverified.') }}
                                </p>

                                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="form-link text-sm underline">
                                        {{ __('Resend verification email') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </section>
                @elseif ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <section class="card-shell space-y-5">
                        <div class="space-y-2">
                            <p class="section-kicker">{{ __('Verification') }}</p>
                            <h3 class="card-form-title text-lg font-semibold">{{ __('Verify your email address') }}</h3>
                            <p class="card-form-copy text-sm">
                                {{ __('Your account details are managed by an administrator, but you can still resend the verification email from here.') }}
                            </p>
                        </div>

                        <div class="card-shell-muted space-y-3 p-5">
                            <p class="card-inline-copy text-sm">{{ __('Your email address is unverified.') }}</p>

                            <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="inline">
                                @csrf
                                <button type="submit" class="form-link text-sm underline">
                                    {{ __('Resend verification email') }}
                                </button>
                            </form>
                        </div>
                    </section>
                @endif

                <section class="card-shell space-y-6">
                    <div class="space-y-2">
                        <p class="section-kicker">{{ __('Security') }}</p>
                        <div>
                            <h3 class="card-form-title text-lg font-semibold">{{ __('Update Password') }}</h3>
                            <p class="card-form-copy text-sm">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf
                        @method('put')

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="card-shell-muted space-y-3 p-5 md:col-span-2">
                                <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                                <x-text-input id="update_password_current_password" name="current_password" type="password"
                                    class="mt-1 block w-full" autocomplete="current-password" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <div class="card-shell-muted space-y-3 p-5">
                                <x-input-label for="update_password_password" :value="__('New Password')" />
                                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full"
                                    autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>

                            <div class="card-shell-muted space-y-3 p-5">
                                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                                    class="mt-1 block w-full" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>

                            @if (session('status') === 'password-updated')
                                <div class="card-shell-muted px-4 py-3">
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="form-note text-sm">{{ __('Saved.') }}</p>
                                </div>
                            @endif
                        </div>
                    </form>
                </section>
            </section>

            <aside class="space-y-6" aria-label="{{ __('Danger zone') }}">
                <section class="card-shell space-y-6">
                    <div class="space-y-2">
                        <p class="section-kicker">{{ __('Danger zone') }}</p>
                        <div>
                            <h3 class="card-form-title text-lg font-semibold">{{ __('Delete Account') }}</h3>
                            <p class="card-form-copy text-sm">
                                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
                            </p>
                        </div>
                    </div>

                    <div class="card-shell-muted space-y-4 p-5">
                        <p class="app-shell-body-copy text-sm">
                            {{ __('Before deleting your account, please download any data or records you want to keep.') }}
                        </p>

                        <x-danger-button x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('Delete Account') }}</x-danger-button>
                    </div>
                </section>
            </aside>
        </div>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-6 p-6">
                @csrf
                @method('delete')

                <div class="space-y-2">
                    <h2 class="card-modal-title text-lg font-medium">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>

                    <p class="card-modal-copy text-sm">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.') }}
                    </p>
                </div>

                <div class="card-shell-muted space-y-3 p-5">
                    <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                        placeholder="{{ __('Password') }}" />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="flex flex-wrap justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button>
                        {{ __('Delete Account') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-sidebar-layout>
