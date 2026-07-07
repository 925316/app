<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('API Signing Keys') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Rotate response signing keys, retain public metadata, and keep private key contents off the web surface.') }}
    </x-slot>

    <div class="space-y-8" data-page="api-signing-keys-index">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3" aria-label="{{ __('Signing key summary') }}">
            <x-stat-card :title="__('Active Key')" :value="$activeKey?->key_id ?? $configKeyId" icon="shield" iconColor="icon-green" />
            <x-stat-card :title="__('Stored Keys')" :value="$keys->total()" icon="document" iconColor="icon-blue" />
            <x-stat-card :title="__('Algorithm')" :value="$activeKey?->algorithm ?? config('services.api_signing.algorithm', 'RSA-2048-SHA256')" icon="lock" iconColor="icon-purple" />
        </section>

        <section class="card-shell space-y-6" data-api-signing-keys-panel>
            <div class="app-toolbar" data-api-signing-keys-toolbar>
                <div>
                    <p class="section-kicker">{{ __('Trust protocol') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Response signing key management') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Private keys stay as files. This page stores public keys, fingerprints, paths, and activation history only.') }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <button type="button" class="btn btn-primary btn-sm gap-2" x-data @click="$dispatch('open-modal', 'rotate-api-signing-key')">
                        <x-icon name="reset" class="h-4 w-4" />
                        {{ __('Rotate Key') }}
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-amber-200/70 bg-amber-50/80 p-4 text-sm text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-100">
                {{ __('Rotation immediately changes meta.signature.key_id for signed API responses. Keep retired public keys available to clients until they have migrated.') }}
            </div>

            <x-table :headers="[__('Key'), __('Status'), __('Fingerprint'), __('Private Path'), __('Activated'), __('Actions')]" :emptyColspan="6" compact="true" ariaLabel="{{ __('API signing keys table') }}">
                @forelse ($keys as $key)
                    <tr class="table-row">
                        <td class="table-cell-primary">
                            <div class="table-stack table-stack-tight min-w-0">
                                <div class="table-title table-truncate table-truncate-md text-sm" title="{{ $key->key_id }}">{{ $key->key_id }}</div>
                                <div class="table-meta">{{ $key->algorithm }}</div>
                            </div>
                        </td>
                        <td class="table-cell table-cell-fit">
                            @if ($key->is_active)
                                <x-status-badge status="success" :text="__('Active')" />
                            @elseif ($key->retired_at)
                                <x-status-badge status="warning" :text="__('Retired')" />
                            @else
                                <x-status-badge status="default" :text="__('Stored')" />
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="table-meta table-truncate table-truncate-md font-mono" title="{{ $key->public_key_fingerprint }}">{{ \Illuminate\Support\Str::limit($key->public_key_fingerprint, 24, '...') }}</span>
                        </td>
                        <td class="table-cell">
                            <span class="table-meta table-truncate table-truncate-md" title="{{ $key->private_key_path }}">{{ $key->private_key_path }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="table-stack table-stack-tight">
                                <div>{{ $key->activated_at?->format('Y-m-d H:i') ?? __('Never') }}</div>
                                <div class="table-meta">{{ $key->creator?->username ?? __('System') }}</div>
                            </div>
                        </td>
                        <td class="table-cell table-cell-fit text-right">
                            <div class="table-actions" aria-label="{{ __('Signing key actions') }}">
                                @if (! $key->is_active)
                                    <form action="{{ route('api-signing-keys.activate', $key) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="table-action table-action--primary">
                                            {{ __('Activate') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="table-meta">{{ __('Current') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr class="table-row">
                        <td colspan="6" class="table-cell">
                            <details class="space-y-3">
                                <summary class="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Show public key') }}</summary>
                                <textarea readonly rows="5" class="form-textarea font-mono text-xs">{{ $key->public_key }}</textarea>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="6" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="shield" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No rotated signing keys yet.') }}</p>
                                <p class="table-empty-copy">{{ __('The API still uses the configured key until an admin rotates a managed key.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div>
                <x-pagination :paginator="$keys" />
            </div>

            <div class="card-shell-muted space-y-2 rounded-2xl p-4 text-sm">
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Config fallback') }}</p>
                <p class="text-gray-600 dark:text-gray-300">{{ __('When no database key is active, signed responses use :key from :path.', ['key' => $configKeyId, 'path' => $configPrivateKeyPath]) }}</p>
            </div>
        </section>

        <x-modal name="rotate-api-signing-key" :show="$errors->has('confirm_rotation')" maxWidth="md">
            <div class="modal-header">
                <div class="flex items-start gap-4">
                    <span class="card-icon-container icon-yellow shrink-0">
                        <x-icon name="warning" class="h-6 w-6" />
                    </span>

                    <div class="space-y-1">
                        <p class="section-kicker">{{ __('Key rotation') }}</p>
                        <h3 class="card-heading text-lg font-semibold">{{ __('Rotate API Signing Key') }}</h3>
                    </div>
                </div>
            </div>

            <form action="{{ route('api-signing-keys.rotate') }}" method="POST">
                @csrf

                <div class="modal-body space-y-4">
                    <p class="card-modal-copy text-sm">
                        {{ __('A new RSA key pair will be generated, the private key will be written to the configured key directory, and the new key becomes active immediately.') }}
                    </p>

                    <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200">
                        <input type="checkbox" name="confirm_rotation" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span>{{ __('I understand clients must trust the new public key for the new key_id.') }}</span>
                    </label>

                    @error('confirm_rotation')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="modal-footer">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'rotate-api-signing-key')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button class="gap-2">
                        <x-icon name="reset" class="h-4 w-4" />
                        {{ __('Rotate Key') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-sidebar-layout>
