<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('License Details') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- License Header -->
                    <div class="mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="text-lg font-medium">License: {{ $license->key }}</h3>
                                <div class="mt-2 flex items-center gap-4">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $license->getStatusColorAttribute() }}">
                                        {{ $license->getStatusTextAttribute() }}
                                    </span>
                                    @if($license->isActive() && !$license->isExpired())
                                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">
                                            {{ $license->daysUntilExpiry() }} days remaining
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex gap-2">
                                @if($isAdmin ?? false)
                                    <a href="{{ route('licenses.edit', $license) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                        Edit
                                    </a>
                                @endif
                                <a href="{{ route('licenses.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
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
                                    <span class="font-medium">{{ $license->key }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Type:</span>
                                    <span class="font-medium">N/A (deprecated)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Privilege:</span>
                                    <span class="font-medium">{{ $license->getPrivilegeTextAttribute() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Status:</span>
                                    <span class="font-medium">{{ $license->getStatusTextAttribute() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Created At:</span>
                                    <span class="font-medium">{{ $license->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Created From IP:</span>
                                    <span class="font-medium">{{ $license->created_from_ip ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Assignment & Expiration</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Assigned To:</span>
                                    <span class="font-medium">
                                        @if($license->account)
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
                                    <span class="font-medium">{{ $license->activated_at?->format('Y-m-d H:i:s') ?? 'Not activated' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Expires At:</span>
                                    <span class="font-medium">{{ $license->expires_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Suspended At:</span>
                                    <span class="font-medium">{{ $license->suspended_at?->format('Y-m-d H:i:s') ?? 'Never' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Days Until Expiry:</span>
                                    <span class="font-medium">{{ $license->daysUntilExpiry() }} days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($license->notes)
                        <div class="mb-6">
                            <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">Administrator Notes</h4>
                            <div class="bg-yellow-50 dark:bg-yellow-900/50 p-4 rounded-lg">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200 whitespace-pre-wrap">{{ $license->notes }}</p>
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
                                    <span class="font-medium">{{ $statusHistory['current_status'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Activated At:</span>
                                    <span class="font-medium">{{ $statusHistory['activated_at']?->format('Y-m-d H:i:s') ?? 'Not activated' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Suspended At:</span>
                                    <span class="font-medium">{{ $statusHistory['suspended_at']?->format('Y-m-d H:i:s') ?? 'Never' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Expires At:</span>
                                    <span class="font-medium">{{ $statusHistory['expires_at']?->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Days Until Expiry:</span>
                                    <span class="font-medium">{{ $statusHistory['days_until_expiry'] }} days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Actions -->
                    @if($isAdmin ?? false)
                        <div class="mb-6">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Admin Actions</h4>
                            <div class="flex flex-wrap gap-2">
                                @if($license->canActivate())
                                    <form action="{{ route('licenses.activate', $license) }}" method="POST" onsubmit="return confirm('Are you sure you want to activate this license?')">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                            Activate
                                        </button>
                                    </form>
                                @endif

                                @if($license->status->canSuspend())
                                    <form action="{{ route('licenses.suspend', $license) }}" method="POST" onsubmit="return confirm('Are you sure you want to suspend this license?')">
                                        @csrf
                                        <input type="hidden" name="suspension_reason" value="Administrative action">
                                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition">
                                            Suspend
                                        </button>
                                    </form>
                                @endif

                                @if($license->status->canReactivate())
                                    <form action="{{ route('licenses.reactivate', $license) }}" method="POST" onsubmit="return confirm('Are you sure you want to reactivate this license?')">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                            Reactivate
                                        </button>
                                    </form>
                                @endif

                                @if($license->status->canUpgrade())
                                    <button onclick="showUpgradeModal()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                                        Upgrade
                                    </button>
                                @endif

                                @if($license->status->canRevoke())
                                    <form action="{{ route('licenses.revoke', $license) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke this license? This action cannot be undone.')">
                                        @csrf
                                        <input type="hidden" name="revocation_reason" value="Administrative action">
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                            Revoke
                                        </button>
                                    </form>
                                @endif

                                <button onclick="showExtendModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                    Extend Expiration
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- User Actions -->
                    @if(!($isAdmin ?? false) && $license->canActivate() && !$license->used_by)
                        <div class="mb-6">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Available Actions</h4>
                            <form action="{{ route('licenses.activate', $license) }}" method="POST" onsubmit="return confirm('Are you sure you want to activate this license for your account?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                    Activate This License
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upgrade Modal -->
    @if($isAdmin ?? false)
        <div id="upgradeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg max-w-md w-full mx-4">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Upgrade License</h3>
                <form action="{{ route('licenses.upgrade', $license) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="new_privilege" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Privilege Level</label>
                        <select name="new_privilege" id="new_privilege" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            @foreach(\App\Enums\LicensePrivilege::options() as $value => $label)
                                @if($value > $license->privilege->value)
                                    <option value="{{ $value }}">{{ ucfirst($label) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="upgrade_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upgrade Notes</label>
                        <textarea name="upgrade_notes" id="upgrade_notes" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideUpgradeModal()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                            Upgrade License
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Extend Modal -->
        <div id="extendModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg max-w-md w-full mx-4">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Extend Expiration</h3>
                <form action="{{ route('licenses.extend', $license) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Days to Add</label>
                        <input type="number" name="days" id="days" min="1" max="365" value="30"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideExtendModal()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                            Extend Expiration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function showUpgradeModal() {
                document.getElementById('upgradeModal').classList.remove('hidden');
                document.getElementById('upgradeModal').classList.add('flex');
            }

            function hideUpgradeModal() {
                document.getElementById('upgradeModal').classList.add('hidden');
                document.getElementById('upgradeModal').classList.remove('flex');
            }

            function showExtendModal() {
                document.getElementById('extendModal').classList.remove('hidden');
                document.getElementById('extendModal').classList.add('flex');
            }

            function hideExtendModal() {
                document.getElementById('extendModal').classList.add('hidden');
                document.getElementById('extendModal').classList.remove('flex');
            }

            // Close modals when clicking outside
            document.getElementById('upgradeModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideUpgradeModal();
                }
            });

            document.getElementById('extendModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideExtendModal();
                }
            });
        </script>
    @endif
</x-app-layout>
