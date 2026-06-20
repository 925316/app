@php use App\Enums\LicensePrivilege; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Account Details') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Carry the atelier account directory system into the detail view without changing account actions, modal workflows, or related records.') }}
    </x-slot>

    @php
        $accountPrivilege = LicensePrivilege::tryFrom($account->getPrivilegeLevel());
        $accountPrivilegeLabel = $accountPrivilege?->getLabel() ?? 'default';
        $accountStatusText = $account->isCurrentlySuspended ? __('Suspended') : __('Active');
        $boundDevicesCount = $boundDevices->count();
        $adminUnbindDeviceConfirmation = __('Are you sure you want to unbind this device from the account?');
    @endphp

    <div class="space-y-8" data-page="accounts-show">
        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Account') }}</p>
                    <h2 class="app-toolbar-title">{{ $account->username }}</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$account->isCurrentlySuspended ? 'suspended' : 'active'" :text="$accountStatusText" />
                        <x-status-badge :status="$account->email_verified_at ? 'verified' : 'unverified'" :text="$account->email_verified_at ? __('Verified') : __('Unverified')" />
                        <x-status-badge :status="strtolower($accountPrivilegeLabel)" :text="ucfirst($accountPrivilegeLabel)" />
                    </div>
                    <p class="mt-3 app-shell-body-copy text-sm">{{ __('ID:') }} #{{ $account->id }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <x-primary-button tag="a" href="{{ route('accounts.edit', $account) }}" class="btn-sm gap-2">
                        <x-icon name="document" class="h-4 w-4" />
                        {{ __('Edit Account') }}
                    </x-primary-button>

                    <x-secondary-button tag="a" href="{{ route('accounts.index') }}" class="btn-sm gap-2">
                        <x-icon name="reset" class="h-4 w-4" />
                        {{ __('Back to Accounts') }}
                    </x-secondary-button>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Account statistics') }}">
                <x-stat-card :title="__('Licenses')" :value="$account->licenses->count()" icon="document" iconColor="icon-blue" />
                <x-stat-card :title="__('Devices')" :value="$account->devices_count" icon="desktop" iconColor="icon-purple" />
                <x-stat-card :title="__('Bound Devices')" :value="$boundDevicesCount" icon="success" iconColor="icon-green" />
                <x-stat-card :title="__('HWID Resets')" :value="$account->hwid_reset_count" icon="warning" iconColor="icon-yellow" />
            </section>
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Overview') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Account overview') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Keep the same account facts, but present them in the shared atelier detail language.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                <div class="card-shell-muted space-y-4 p-6">
                    <div>
                        <p class="section-kicker">{{ __('Identity') }}</p>
                        <h3 class="card-heading text-lg font-semibold">{{ __('Account information') }}</h3>
                    </div>

                    <div class="grid gap-4">
                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Username') }}</p>
                            <p class="card-value text-sm font-semibold">{{ $account->username }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Email') }}</p>
                            <p class="card-value break-all text-sm font-semibold">{{ $account->email }}</p>
                            <x-status-badge :status="$account->email_verified_at ? 'verified' : 'unverified'" :text="$account->email_verified_at ? __('Verified') : __('Unverified')" />
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Privilege level') }}</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="strtolower($accountPrivilegeLabel)" :text="ucfirst($accountPrivilegeLabel)" />
                                <span class="app-shell-body-copy text-sm">{{ __('Status:') }} {{ $accountStatusText }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-shell-muted space-y-4 p-6">
                    <div>
                        <p class="section-kicker">{{ __('Access') }}</p>
                        <h3 class="card-heading text-lg font-semibold">{{ __('Login information') }}</h3>
                    </div>

                    <div class="grid gap-4">
                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Last login') }}</p>
                            <p class="card-value text-sm font-semibold">{{ $account->last_login_at ? $account->last_login_at->diffForHumans() : __('Never') }}</p>
                            @if ($account->last_login_at)
                                <p class="app-shell-body-copy text-sm">{{ $account->last_login_at->format('Y-m-d H:i:s') }}</p>
                            @endif
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Last IP address') }}</p>
                            <p class="card-value font-mono text-sm font-semibold">{{ $account->last_ip_address ?? __('N/A') }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Registered') }}</p>
                            <p class="card-value text-sm font-semibold">{{ $account->created_at->format('Y-m-d H:i:s') }}</p>
                            <p class="app-shell-body-copy text-sm">{{ $account->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-shell-muted space-y-4 p-6">
                    <div>
                        <p class="section-kicker">{{ __('Devices') }}</p>
                        <h3 class="card-heading text-lg font-semibold">{{ __('Device information') }}</h3>
                    </div>

                    <div class="grid gap-4">
                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Total devices') }}</p>
                            <p class="card-value text-sm font-semibold">{{ $account->devices_count }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Bound devices') }}</p>
                            <p class="card-value text-sm font-semibold">{{ $boundDevicesCount }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('HWID resets') }}</p>
                            <p class="card-value text-sm font-semibold">{{ $account->hwid_reset_count }}</p>
                            @if ($account->hwid_last_reset_at)
                                <p class="app-shell-body-copy text-sm">{{ __('Last reset:') }} {{ $account->hwid_last_reset_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Operations') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Account actions') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('All existing account actions stay unchanged; this section only brings them into the shared handoff styling.') }}</p>
                </div>
            </div>

            <div class="card-shell-muted p-6">
                <div class="flex flex-wrap gap-3">
                    @if ($account->isCurrentlySuspended)
                        <form action="{{ route('accounts.unsuspend', $account) }}" method="POST">
                            @csrf
                            <x-primary-button type="submit" class="btn-sm">{{ __('Unsuspend Account') }}</x-primary-button>
                        </form>
                    @else
                        <button type="button" onclick="openSuspendModal('{{ $account->id }}')" class="btn btn-danger btn-sm">
                            {{ __('Suspend Account') }}
                        </button>
                    @endif

                    @if (!$account->email_verified_at)
                        <form action="{{ route('accounts.verify-email', $account) }}" method="POST">
                            @csrf
                            <x-primary-button type="submit" class="btn-sm">{{ __('Verify Email') }}</x-primary-button>
                        </form>
                    @endif

                    <button type="button" onclick="openResetHwidModal('{{ $account->id }}')" class="btn btn-secondary btn-sm">
                        {{ __('Reset HWID') }}
                    </button>

                    <form action="{{ route('accounts.destroy', $account) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <x-danger-button type="submit" class="btn-sm">{{ __('Delete Account') }}</x-danger-button>
                    </form>
                </div>
            </div>
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Licenses') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Assigned licenses') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Convert the raw license list into the shared table system without changing destinations or record content.') }}</p>
                </div>
            </div>

            <x-table :headers="[__('License Key'), __('Privilege'), __('Status'), __('Expires At'), __('Actions')]" :emptyColspan="5" ariaLabel="{{ __('Account licenses table') }}">
                @forelse ($account->licenses as $license)
                    <tr class="table-row">
                        <td class="table-cell-primary whitespace-nowrap">
                            <div class="table-stack table-stack-tight">
                                <div class="table-title text-sm">{{ $license->key }}</div>
                                <div class="table-meta">{{ __('ID:') }} {{ $license->id }}</div>
                            </div>
                        </td>
                        <td class="table-cell whitespace-nowrap">
                            <x-status-badge :status="strtolower($license->privilege?->getLabel() ?? 'default')" :text="ucfirst($license->privilege?->getLabel() ?? __('Unknown'))" />
                        </td>
                        <td class="table-cell whitespace-nowrap">
                            <x-status-badge :status="strtolower($license->status?->getLabel() ?? 'default')" :text="$license->status?->getLabel() ?? __('Unknown')" />
                        </td>
                        <td class="table-cell whitespace-nowrap">
                            <div class="table-stack table-stack-tight">
                                <div>{{ $license->expires_at ? $license->expires_at->format('Y-m-d H:i:s') : __('N/A') }}</div>
                                @if ($license->expires_at)
                                    <div class="table-meta">{{ $license->expires_at->diffForHumans() }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell whitespace-nowrap text-right">
                            <div class="table-actions table-actions--nowrap">
                                <a href="{{ route('licenses.show', $license) }}" class="table-action table-action--primary">
                                    {{ __('View') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="5" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="document" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No licenses found for this account.') }}</p>
                                <p class="table-empty-copy">{{ __('Assigned licenses will appear here once they exist for this account.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Devices') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Registered devices') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Keep the same device data while normalizing it into the shared table language.') }}</p>
                </div>
            </div>

            <x-table :headers="[__('Device'), __('HWID Hash'), __('Status'), __('First Seen'), __('Last Seen'), __('Bound At'), __('Actions')]" :emptyColspan="7" compact="true" ariaLabel="{{ __('Account devices table') }}">
                @forelse ($account->devices as $device)
                    <tr class="table-row" id="account-device-{{ $device->id }}">
                        <td class="table-cell-primary">
                            <div class="table-stack table-stack-tight">
                                <div class="table-title text-sm">{{ __('Device') }} #{{ $device->id }}</div>
                                <div class="table-meta">{{ $device->device_name ?? __('Unknown device') }}</div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <span class="table-meta table-code table-truncate table-truncate-md" title="{{ $device->hwid_hash ?? __('N/A') }}">
                                {{ $device->hwid_hash ? substr($device->hwid_hash, 0, 16).'...' : __('N/A') }}
                            </span>
                        </td>
                        <td class="table-cell table-cell-fit">
                            @if ($device->bound_at && !$device->unbound_at)
                                <x-status-badge status="active" :text="__('Bound')" />
                            @elseif ($device->unbound_at)
                                <x-status-badge status="suspended" :text="__('Unbound')" />
                            @else
                                <x-status-badge status="default" :text="__('Not Bound')" />
                            @endif
                        </td>
                        <td class="table-cell">
                            @if ($device->first_seen_at)
                                <div class="table-stack table-stack-tight">
                                    <div>{{ $device->first_seen_at->format('Y-m-d H:i:s') }}</div>
                                    <div class="table-meta">{{ $device->first_seen_at->diffForHumans() }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('N/A') }}</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if ($device->last_seen_at)
                                <div class="table-stack table-stack-tight">
                                    <div>{{ $device->last_seen_at->format('Y-m-d H:i:s') }}</div>
                                    <div class="table-meta">{{ $device->last_seen_at->diffForHumans() }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('N/A') }}</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if ($device->bound_at)
                                <div class="table-stack table-stack-tight">
                                    <div>{{ $device->bound_at->format('Y-m-d H:i:s') }}</div>
                                    <div class="table-meta">{{ $device->bound_at->diffForHumans() }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('N/A') }}</span>
                            @endif
                        </td>
                        <td class="table-cell table-cell-fit text-right">
                            @if ($device->bound_at && ! $device->unbound_at)
                                <div class="table-actions" aria-label="{{ __('Account device row actions') }}">
                                    <form action="{{ route('devices.unbind-admin', $device) }}" method="POST" class="inline" onsubmit="return confirm('{{ $adminUnbindDeviceConfirmation }}')">
                                        @csrf
                                        <button type="submit" class="table-action table-action--danger">
                                            {{ __('Unbind') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="table-meta">{{ __('No actions') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="7" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="desktop" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No devices found for this account.') }}</p>
                                <p class="table-empty-copy">{{ __('Bound and unbound devices will appear here as the account uses the service.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Recent Activity') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Account event stream') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Preserve the existing activity payloads while presenting them as atelier event cards.') }}</p>
                </div>
            </div>

            @if ($account->eventLogs->isEmpty())
                <div class="table-empty-state card-shell-muted px-6 py-12 text-center">
                    <x-icon name="document" class="table-empty-icon" />
                    <p class="table-empty-title">{{ __('No activity found for this account.') }}</p>
                    <p class="table-empty-copy">{{ __('Recent account events will appear here once they are recorded.') }}</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($account->eventLogs as $log)
                        @php
                            $eventBadge = match ($log->event_level) {
                                0 => 'info',
                                1 => 'warning',
                                default => 'danger',
                            };
                        @endphp

                        <div class="card-shell-muted space-y-4 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge :status="$eventBadge" :text="$log->event_type" />
                                    <span class="app-shell-body-copy text-sm">{{ $log->created_at->diffForHumans() }}</span>
                                </div>

                                <span class="badge badge-default">{{ __('ID:') }} {{ $log->id }}</span>
                            </div>

                            <div class="card-shell-muted p-4">
                                <pre class="table-code app-shell-body-copy overflow-x-auto whitespace-pre-wrap break-all bg-transparent text-xs">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <x-modal name="suspend-modal">
            <div class="modal-header">
                <div class="flex items-start gap-4">
                    <span class="card-icon-container icon-red shrink-0">
                        <x-icon name="warning" class="h-6 w-6" />
                    </span>

                    <div class="space-y-1">
                        <p class="section-kicker">{{ __('Account actions') }}</p>
                        <h3 class="card-heading text-lg font-semibold">{{ __('Suspend Account') }}</h3>
                    </div>
                </div>
            </div>

            <form id="suspendForm" method="POST">
                @csrf

                <div class="modal-body space-y-4">
                    <p class="card-modal-copy text-sm">{{ __('Enter suspension details for this account.') }}</p>

                    <div class="space-y-2">
                        <label for="suspend_reason" class="form-label">{{ __('Reason') }}</label>
                        <input type="text" name="reason" id="suspend_reason" class="form-input w-full">
                    </div>

                    <div class="space-y-2">
                        <label for="suspend_duration" class="form-label">{{ __('Duration (days) - Optional') }}</label>
                        <input type="number" name="duration" id="suspend_duration" min="1" max="365" class="form-input w-full">
                    </div>
                </div>

                <div class="modal-footer">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'suspend-modal')">{{ __('Cancel') }}</x-secondary-button>
                    <x-danger-button type="submit">{{ __('Suspend') }}</x-danger-button>
                </div>
            </form>
        </x-modal>

        <x-modal name="reset-hwid-modal">
            <div class="modal-header">
                <div class="flex items-start gap-4">
                    <span class="card-icon-container icon-yellow shrink-0">
                        <x-icon name="warning" class="h-6 w-6" />
                    </span>

                    <div class="space-y-1">
                        <p class="section-kicker">{{ __('Device actions') }}</p>
                        <h3 class="card-heading text-lg font-semibold">{{ __('Reset HWID') }}</h3>
                    </div>
                </div>
            </div>

            <form id="resetHwidForm" method="POST">
                @csrf

                <div class="modal-body space-y-4">
                    <p class="card-modal-copy text-sm">{{ __('This will unbind all devices and reset the HWID for this account.') }}</p>
                    <p class="card-modal-copy text-sm font-medium">{{ __('Warning: This action cannot be undone.') }}</p>
                </div>

                <div class="modal-footer">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reset-hwid-modal')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Reset HWID') }}</x-primary-button>
                </div>
            </form>
        </x-modal>

        <script>
            let suspendAccountId = null;
            let resetHwidAccountId = null;

            function openSuspendModal(accountId) {
                suspendAccountId = accountId;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'suspend-modal' }));
                document.getElementById('suspendForm').action = `/accounts/${accountId}/suspend`;
            }

            function openResetHwidModal(accountId) {
                resetHwidAccountId = accountId;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reset-hwid-modal' }));
                document.getElementById('resetHwidForm').action = `/accounts/${accountId}/reset-hwid`;
            }
        </script>
    </div>
</x-app-sidebar-layout>
