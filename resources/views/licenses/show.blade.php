@php use App\Enums\LicensePrivilege; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('License Details') }}
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6" data-page="licenses-show">
        <div class="card-shell">
            {{-- License Header --}}
            <div class="app-toolbar mb-0">
                <div>
                    <p class="section-kicker">{{ __('License') }}</p>
                    <h3 class="app-shell-heading text-lg font-semibold">{{ $license->key }}</h3>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$license->status->value" :text="$license->getStatusTextAttribute()" />
                        @if ($license->isActive() && !$license->isExpired())
                            <span class="app-shell-body-copy text-sm">{{ $license->daysUntilExpiry() }} {{ __('days remaining') }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    @if ($isAdmin ?? false)
                        <x-primary-button tag="a" href="{{ route('licenses.edit', $license) }}">{{ __('Edit') }}</x-primary-button>
                    @endif
                    <x-secondary-button tag="a" href="{{ route('licenses.index') }}">{{ __('Back to List') }}</x-secondary-button>
                </div>
            </div>

        {{-- License Details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="card-shell-muted">
                <h4 class="card-info-title mb-3 font-medium">{{ __('Basic Information') }}</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('License Key:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->key }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Privilege:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->getPrivilegeTextAttribute() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Status:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->getStatusTextAttribute() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Created At:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->created_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Created From IP:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->created_from_ip ?? __('N/A') }}</span>
                    </div>
                </div>
            </div>

            <div class="card-shell-muted">
                <h4 class="card-info-title mb-3 font-medium">{{ __('Assignment & Expiration') }}</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Assigned To:') }}</span>
                        <span class="card-label-strong font-medium">
                            @if ($license->account)
                                <a href="#" class="table-link hover:underline">
                                    {{ $license->account->username }}
                                </a>
                            @else
                                {{ __('Unassigned') }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Activated At:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->activated_at?->format('Y-m-d H:i:s') ?? __('Not activated') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Expires At:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->expires_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Suspended At:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->suspended_at?->format('Y-m-d H:i:s') ?? __('Never') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Days Until Expiry:') }}</span>
                        <span class="card-label-strong font-medium">{{ $license->daysUntilExpiry() }} {{ __('days') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if ($license->notes)
            <div class="card-shell-muted mb-6">
                <p class="section-kicker mb-2">{{ __('Administrator Notes') }}</p>
                <p class="card-inline-copy whitespace-pre-wrap text-sm">{{ $license->notes }}</p>
            </div>
        @endif

        {{-- Status History --}}
        <div class="card-shell-muted mb-6">
            <p class="section-kicker mb-3">{{ __('Status History') }}</p>
            <div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Current Status:') }}</span>
                        <span class="card-label-strong font-medium">{{ $statusHistory['current_status'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Activated At:') }}</span>
                        <span class="card-label-strong font-medium">{{ $statusHistory['activated_at']?->format('Y-m-d H:i:s') ?? __('Not activated') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Suspended At:') }}</span>
                        <span class="card-label-strong font-medium">{{ $statusHistory['suspended_at']?->format('Y-m-d H:i:s') ?? __('Never') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Expires At:') }}</span>
                        <span class="card-label-strong font-medium">{{ $statusHistory['expires_at']?->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="card-inline-copy">{{ __('Days Until Expiry:') }}</span>
                        <span class="card-label-strong font-medium">{{ $statusHistory['days_until_expiry'] }} {{ __('days') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin Actions --}}
        @if ($isAdmin ?? false)
            <div class="card-shell-muted mb-6">
                <p class="section-kicker mb-3">{{ __('Admin Actions') }}</p>
                <div class="flex flex-wrap gap-2">
                    @if ($license->canActivate())
                        <form action="{{ route('licenses.activate', $license) }}" method="POST">
                            @csrf
                            <x-primary-button type="submit">{{ __('Activate') }}</x-primary-button>
                        </form>
                    @endif

                    @if ($license->status->canSuspend())
                        <form action="{{ route('licenses.suspend', $license) }}" method="POST">
                            @csrf
                            <input type="hidden" name="suspension_reason" value="{{ __('Administrative action') }}">
                            <x-secondary-button type="submit">{{ __('Suspend') }}</x-secondary-button>
                        </form>
                    @endif

                    @if ($license->status->canReactivate())
                        <form action="{{ route('licenses.reactivate', $license) }}" method="POST">
                            @csrf
                            <x-primary-button type="submit">{{ __('Reactivate') }}</x-primary-button>
                        </form>
                    @endif

                    @if ($license->status->canUpgrade())
                        <x-secondary-button onclick="showUpgradeModal()">{{ __('Upgrade') }}</x-secondary-button>
                    @endif

                    @if ($license->status->canRevoke())
                        <form action="{{ route('licenses.revoke', $license) }}" method="POST">
                            @csrf
                            <input type="hidden" name="revocation_reason" value="{{ __('Administrative action') }}">
                            <button type="submit" class="btn btn-danger btn-sm">
                                Revoke
                            </button>
                        </form>
                    @endif

                    <button onclick="showExtendModal()" class="btn btn-primary btn-sm">
                        Extend Expiration
                    </button>
                </div>
            </div>
        @endif

        {{-- User Actions --}}
        @if (!($isAdmin ?? false) && $license->canActivate() && !$license->used_by)
            <div class="card-shell-muted mb-6">
                <p class="section-kicker mb-3">{{ __('Available Actions') }}</p>
                <form action="{{ route('licenses.activate', $license) }}" method="POST">
                    @csrf
                    <x-primary-button type="submit">{{ __('Activate This License') }}</x-primary-button>
                </form>
            </div>
        @endif

        <!-- Upgrade Modal -->
        @if ($isAdmin ?? false)
            <x-modal name="upgrade-modal">
                <div class="p-6">
                    <h3 class="card-modal-title mb-4 text-lg font-medium">{{ __('Upgrade License') }}</h3>
                    <form action="{{ route('licenses.upgrade', $license) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="new_privilege" class="form-label mb-1">{{ __('New Privilege Level') }}</label>
                            <select name="new_privilege" id="new_privilege" class="form-select">
                                @foreach (LicensePrivilege::options() as $value => $label)
                                    @if ($value > $license->privilege->value)
                                        <option value="{{ $value }}">{{ ucfirst($label) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="upgrade_notes" class="form-label mb-1">{{ __('Upgrade Notes') }}</label>
                            <textarea name="upgrade_notes" id="upgrade_notes" rows="2" class="form-textarea"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <x-secondary-button type="button" x-on:click="show = false">{{ __('Cancel') }}</x-secondary-button>
                            <x-primary-button type="submit">{{ __('Upgrade License') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </x-modal>

            <!-- Extend Modal -->
            <x-modal name="extend-modal">
                <div class="p-6">
                    <h3 class="card-modal-title mb-4 text-lg font-medium">{{ __('Extend Expiration') }}</h3>
                    <form action="{{ route('licenses.extend', $license) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="days" class="form-label mb-1">{{ __('Days to Add') }}</label>
                            <input type="number" name="days" id="days" min="1" max="365"
                                value="30" class="form-input w-full">
                        </div>
                        <div class="flex justify-end gap-2">
                            <x-secondary-button type="button" x-on:click="show = false">{{ __('Cancel') }}</x-secondary-button>
                            <x-primary-button type="submit">{{ __('Extend Expiration') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </x-modal>

            <script>
                function showUpgradeModal() {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upgrade-modal' }));
                }

                function showExtendModal() {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'extend-modal' }));
                }
            </script>
        @endif
        </div>
    </div>
</x-app-sidebar-layout>
