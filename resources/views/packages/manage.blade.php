<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Package Management') }}
    </x-slot>

    <div class="py-7">
            <!-- Header with actions -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 lg:max-xl:flex-wrap">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Package Management</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Manage software packages and releases</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('packages.index') }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        View Packages
                    </a>
                    @if ($isAdmin ?? false)
                        <a href="{{ route('packages.upload') }}"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                            Add New Package
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition flex items-center gap-2">
                                <span>Bulk Actions</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-50"
                                x-cloak>
                                <div class="py-1">
                                    <button onclick="bulkAction('delete')"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Delete Selected
                                    </button>
                                    <button onclick="bulkAction('export')"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Export List
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Package Statistics</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-600 dark:text-gray-300">Total Releases:</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $stats['total_releases'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-300">Stable Releases:</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $stats['stable_releases'] ?? 0 }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-300">Dev Releases:</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $stats['dev_releases'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-300">Latest Stable:</div>
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $stats['latest_stable']?->version ?? 'None' }}</div>
                    </div>
                </div>
            </div>

            <!-- Channel Filter -->
            <div class="mb-6">
                <form method="GET" action="{{ route('packages.manage') }}" class="flex items-end gap-4 lg:max-xl:flex-wrap">
                    <div>
                        <label for="channel"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Release
                            Channel</label>
                        <select name="channel" id="channel" onchange="this.form.submit()"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">All Channels</option>
                            <option value="stable" {{ request('channel') === 'stable' ? 'selected' : '' }}>Stable
                            </option>
                            <option value="dev" {{ request('channel') === 'dev' ? 'selected' : '' }}>Development
                            </option>
                        </select>
                    </div>
                    @if (request('channel'))
                        <a href="{{ route('packages.manage') }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition mt-auto">
                            Reset Filter
                        </a>
                    @endif
                </form>
            </div>

            <!-- Packages table -->
            @php
                $tableHeaders =
                    $isAdmin ?? false
                        ? ['Version', 'Channel', 'Released', 'Hash', 'Actions']
                        : ['Version', 'Channel', 'Released', 'Hash'];
                $tableColspan = $isAdmin ?? false ? 5 : 4;
            @endphp
            <x-table :headers="$tableHeaders" :emptyColspan="$tableColspan">
                @forelse($releases as $release)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $release->version }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            <span
                                class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $release->release_channel === 'stable' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                                {{ ucfirst($release->release_channel) }}
                            </span>
                            @if ($release->version === ($stats['latest_stable']?->version ?? null))
                                <span
                                    class="ml-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                                    Latest
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            {{ $release->created_at ? $release->created_at->format('Y-m-d H:i') : 'Unknown' }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            @if ($release->virus_detection_url)
                                <span
                                    class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                    Available
                                </span>
                            @else
                                <span
                                    class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    None
                                </span>
                            @endif
                        </td>
                        @if ($isAdmin ?? false)
                            <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('packages.show', $release) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                    Details
                                </a>
                                <span class="mx-1 text-gray-400">|</span>
                                <form class="inline delete-form" data-version="{{ $release->version }}"
                                    onsubmit="return confirmDelete('{{ $release->version }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $tableColspan }}"
                            class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                            No packages found.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <!-- Pagination -->
            <div class="mt-4">
                <x-pagination :paginator="$releases" />
            </div>

            @if ($isAdmin ?? false)
                <script>
                    function confirmDelete(version) {
                        return confirm(`Are you sure you want to delete package version ${version}? This action cannot be undone.`);
                    }

                    // Select all functionality
                    document.addEventListener('DOMContentLoaded', function() {
                        const selectAll = document.getElementById('select-all');
                        const checkboxes = document.querySelectorAll('.release-checkbox');

                        if (selectAll && checkboxes.length > 0) {
                            selectAll.addEventListener('change', function() {
                                checkboxes.forEach(checkbox => {
                                    checkbox.checked = this.checked;
                                });
                            });

                            checkboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                                    const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                                    selectAll.checked = allChecked;
                                    selectAll.indeterminate = someChecked && !allChecked;
                                });
                            });
                        }
                    });

                    // Bulk actions
                    function bulkAction(action) {
                        const selectedCheckboxes = document.querySelectorAll('.release-checkbox:checked');
                        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

                        if (selectedIds.length === 0) {
                            alert('Please select at least one package to perform this action.');
                            return;
                        }

                        if (action === 'delete') {
                            if (confirm(
                                    `Are you sure you want to delete ${selectedIds.length} selected package(s)? This action cannot be undone.`
                                )) {
                                // Create a form to submit the bulk delete
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = "{{ route('packages.bulk-delete') }}";

                                // Add CSRF token
                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = '_token';
                                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                                form.appendChild(csrfInput);

                                // Add method override for DELETE
                                const methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'DELETE';
                                form.appendChild(methodInput);

                                // Add selected IDs
                                selectedIds.forEach(id => {
                                    const idInput = document.createElement('input');
                                    idInput.type = 'hidden';
                                    idInput.name = 'ids[]';
                                    idInput.value = id;
                                    form.appendChild(idInput);
                                });

                                document.body.appendChild(form);
                                form.submit();
                            }
                        } else if (action === 'export') {
                            // Export selected packages to JSON
                            const exportData = Array.from(selectedCheckboxes).map(checkbox => {
                                const row = checkbox.closest('tr');
                                return {
                                    id: checkbox.value,
                                    version: row.querySelector('td:nth-child(2)').textContent.trim(),
                                    channel: row.querySelector('td:nth-child(3) span').textContent.trim(),
                                    released: row.querySelector('td:nth-child(4)').textContent.trim(),
                                    hashVerification: row.querySelector('td:nth-child(5) span').textContent.trim()
                                };
                            });

                            const blob = new Blob([JSON.stringify(exportData, null, 2)], {
                                type: 'application/json'
                            });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `packages_export_${new Date().toISOString().slice(0, 10)}.json`;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        }
                    }
                </script>
            @endif
    </div>

</x-app-sidebar-layout>
