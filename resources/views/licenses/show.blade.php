@php use App\Enums\LicensePrivilege; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('License Details') }}
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <div
            class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm rounded-xl shadow-sm border border-cool-200/50 dark:border-cool-700/50 p-6">
        <!-- License Header -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">License: {{ $license->key }}</h3>
                    <div class="mt-2 flex items-center gap-4">
                        <span
                            class="px-3 py-1 rounded-full text-sm font-medium {{ $license->getStatusColorAttribute() }}">
                            {{ $license->getStatusTextAttribute() }}
                        </span>
                        @if ($license->isActive() && !$license->isExpired())
                            <span
                                class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">
                                {{ $license->daysUntilExpiry() }} days remaining
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex gap-2">
                    @if ($isAdmin ?? false)
                        <a href="{{ route('licenses.edit', $license) }}" class="btn btn-indigo btn-sm">
                            Edit
                        </a>
                    @endif
                    <a href="{{ route('licenses.index') }}" class="btn btn-secondary btn-sm">
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- License Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Basic Information</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">License Key:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $license->key }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Privilege:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $license->getPrivilegeTextAttribute() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Status:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $license->getStatusTextAttribute() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Created At:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $license->created_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Created From IP:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $license->created_from_ip ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Assignment & Expiration</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Assigned To:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            @if ($license->account)
                                <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $license->account->username }}
                                </a>
                            @else
                                Unassigned
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Activated At:</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $license->activated_at?->format('Y-m-d H:i:s') ?? 'Not activated' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Expires At:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $license->expires_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Suspended At:</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $license->suspended_at?->format('Y-m-d H:i:s') ?? 'Never' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Days Until Expiry:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $license->daysUntilExpiry() }} days</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if ($license->notes)
            <div class="mb-6">
                <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">Administrator Notes</h4>
                <div class="bg-yellow-50 dark:bg-yellow-900/50 p-4 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200 whitespace-pre-wrap">{{ $license->notes }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Status History -->
        <div class="mb-6">
            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Status History</h4>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Current Status:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $statusHistory['current_status'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Activated At:</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $statusHistory['activated_at']?->format('Y-m-d H:i:s') ?? 'Not activated' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Suspended At:</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $statusHistory['suspended_at']?->format('Y-m-d H:i:s') ?? 'Never' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Expires At:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $statusHistory['expires_at']?->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Days Until Expiry:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $statusHistory['days_until_expiry'] }} days</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        @if ($isAdmin ?? false)
            <div class="mb-6">
                <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Admin Actions</h4>
                <div class="flex flex-wrap gap-2">
                    @if ($license->canActivate())
                        <form action="{{ route('licenses.activate', $license) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-green btn-sm">
                                Activate
                            </button>
                        </form>
                    @endif

                    @if ($license->status->canSuspend())
                        <form action="{{ route('licenses.suspend', $license) }}" method="POST">
                            @csrf
                            <input type="hidden" name="suspension_reason" value="Administrative action">
                            <button type="submit" class="btn btn-yellow btn-sm">
                                Suspend
                            </button>
                        </form>
                    @endif

                    @if ($license->status->canReactivate())
                        <form action="{{ route('licenses.reactivate', $license) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-blue btn-sm">
                                Reactivate
                            </button>
                        </form>
                    @endif

                    @if ($license->status->canUpgrade())
                        <button onclick="showUpgradeModal()" class="btn btn-purple btn-sm">
                            Upgrade
                        </button>
                    @endif

                    @if ($license->status->canRevoke())
                        <form action="{{ route('licenses.revoke', $license) }}" method="POST">
                            @csrf
                            <input type="hidden" name="revocation_reason" value="Administrative action">
                            <button type="submit" class="btn btn-danger btn-sm">
                                Revoke
                            </button>
                        </form>
                    @endif

                    <button onclick="showExtendModal()" class="btn btn-indigo btn-sm">
                        Extend Expiration
                    </button>
                </div>
            </div>
        @endif

        <!-- User Actions -->
        @if (!($isAdmin ?? false) && $license->canActivate() && !$license->used_by)
            <div class="mb-6">
                <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Available Actions</h4>
                <form action="{{ route('licenses.activate', $license) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-green btn-sm">
                        Activate This License
                    </button>
                </form>
            </div>
        @endif

        <!-- Upgrade Modal -->
        @if ($isAdmin ?? false)
            <x-modal name="upgrade-modal">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Upgrade License</h3>
                    <form action="{{ route('licenses.upgrade', $license) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="new_privilege"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Privilege
                                Level</label>
                            <select name="new_privilege" id="new_privilege"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                @foreach (LicensePrivilege::options() as $value => $label)
                                    @if ($value > $license->privilege->value)
                                        <option value="{{ $value }}">{{ ucfirst($label) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="upgrade_notes"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upgrade
                                Notes</label>
                            <textarea name="upgrade_notes" id="upgrade_notes" rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="show = false"
                                class="btn btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-purple">
                                Upgrade License
                            </button>
                        </div>
                    </form>
                </div>
            </x-modal>

            <!-- Extend Modal -->
            <x-modal name="extend-modal">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Extend Expiration</h3>
                    <form action="{{ route('licenses.extend', $license) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="days"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Days
                                to Add</label>
                            <input type="number" name="days" id="days" min="1" max="365"
                                value="30"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="show = false"
                                class="btn btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-indigo">
                                Extend Expiration
                            </button>
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
