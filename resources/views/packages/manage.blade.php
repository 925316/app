<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Package Management') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Keep bulk actions and filtering behavior intact while smoothing the visual handoff from the cinematic package index.') }}
    </x-slot>

    @php
        $latestStable = $stats['latest_stable'] ?? null;
        $hasChannelFilter = filled(request('channel'));
        $tableHeaders =
            $isAdmin ?? false
                ? ['', __('Version'), __('Channel'), __('Released'), __('Hash'), __('Actions')]
                : [__('Version'), __('Channel'), __('Released'), __('Hash')];
        $tableColspan = $isAdmin ?? false ? 6 : 4;
    @endphp

    <div class="space-y-8" data-page="packages-manage">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Package statistics') }}">
            <x-stat-card :title="__('Total Releases')" :value="$stats['total_releases'] ?? 0" icon="cube" iconColor="icon-blue" />
            <x-stat-card :title="__('Stable Releases')" :value="$stats['stable_releases'] ?? 0" icon="success" iconColor="icon-green" />
            <x-stat-card :title="__('Dev Releases')" :value="$stats['dev_releases'] ?? 0" icon="lightning" iconColor="icon-purple" />
            <x-stat-card :title="__('Latest Stable')" :value="$latestStable?->version ?? __('None')" icon="cloud" iconColor="icon-yellow" />
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Release Operations') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Package management') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Preserve the same manage routes, bulk actions, and filters while aligning the surface language to the newer package list.') }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm gap-2">
                        <x-icon name="cube" class="h-4 w-4" />
                        {{ __('View Packages') }}
                    </a>

                    @if ($isAdmin ?? false)
                        <a href="{{ route('packages.upload') }}" class="btn btn-primary btn-sm gap-2">
                            <x-icon name="plus" class="h-4 w-4" />
                            {{ __('Add New Package') }}
                        </a>

                        <div class="relative" x-data="{ open: false }">
                            <button x-on:click="open = !open" type="button" class="btn btn-primary btn-sm gap-2">
                                <span>{{ __('Bulk Actions') }}</span>
                                <x-icon name="filter" class="h-4 w-4" />
                            </button>

                            <div x-show="open" x-on:click.away="open = false" class="absolute right-0 mt-2 w-48 card-shell z-50" x-cloak>
                                <div class="py-1">
                                    <button onclick="bulkAction('delete')" class="w-full px-4 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                        {{ __('Delete Selected') }}
                                    </button>
                                    <button onclick="bulkAction('export')" class="w-full px-4 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                        {{ __('Export List') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <x-filter-box :action="route('packages.manage')" :title="__('Filter releases')">
                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                    <div class="md:col-span-5">
                        <x-filter-dropdown
                            id="channel"
                            name="channel"
                            :label="__('Release Channel')"
                            :value="request('channel')"
                            :submit-on-select="true"
                            :options="[
                                '' => __('All Channels'),
                                'stable' => __('Stable'),
                                'dev' => __('Development'),
                            ]"
                        />
                    </div>

                    <div class="space-y-2 md:col-span-7 filter-box-actions">
                        <span class="form-label text-transparent">{{ __('Actions') }}</span>
                        <div class="form-actions-cluster">
                            <button type="submit" class="btn btn-primary btn-sm flex-1 justify-center gap-2 md:flex-none">
                                <x-icon name="search" class="h-4 w-4" />
                                {{ __('Apply Filter') }}
                            </button>

                            @if ($hasChannelFilter)
                                <a href="{{ route('packages.manage') }}" class="btn btn-secondary btn-sm justify-center gap-2">
                                    <x-icon name="reset" class="h-4 w-4" />
                                    {{ __('Reset Filter') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($hasChannelFilter)
                    <div class="active-filters" data-active-filters>
                        <div class="active-filters__header">
                            <div class="active-filters__copy">
                                <p class="active-filters__title">{{ __('Active Filters') }}</p>
                                <p class="active-filters__subtitle">{{ __('Clear the current manage view without changing any staff-only package behavior.') }}</p>
                            </div>
                            <a href="{{ route('packages.manage') }}" class="active-filters__clear">
                                {{ __('Clear All') }}
                            </a>
                        </div>

                        <div class="active-filters__list">
                            <x-filter-badge :label="__('Channel:').' '.(request('channel') === 'dev' ? __('Development') : __('Stable'))" color="green" :removeUrl="request()->fullUrlWithQuery(['channel' => null])" />
                        </div>
                    </div>
                @endif
            </x-filter-box>

            <x-table :headers="$tableHeaders" :emptyColspan="$tableColspan" compact="true" ariaLabel="{{ __('Managed packages table') }}">
                @forelse ($releases as $release)
                    <tr class="table-row">
                        @if ($isAdmin ?? false)
                            <td class="table-cell">
                                <input type="checkbox" class="release-checkbox form-checkbox" value="{{ $release->id }}">
                            </td>
                        @endif

                        <td class="table-cell-primary">
                            {{ $release->version }}
                        </td>

                        <td class="table-cell">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$release->release_channel === 'stable' ? 'active' : 'info'" :text="ucfirst($release->release_channel)" />

                                @if ($release->version === ($latestStable?->version ?? null))
                                    <span class="badge badge-info">{{ __('Latest') }}</span>
                                @endif
                            </div>
                        </td>

                        <td class="table-cell text-sm text-gray-600 dark:text-gray-300">
                            {{ $release->created_at ? $release->created_at->format('Y-m-d H:i') : __('Unknown') }}
                        </td>

                        <td class="table-cell">
                            @if ($release->virus_detection_url)
                                <x-status-badge status="verified" :text="__('Available')" />
                            @else
                                <span class="badge badge-default">{{ __('None') }}</span>
                            @endif
                        </td>

                        @if ($isAdmin ?? false)
                            <td class="table-cell text-right">
                                <div class="table-actions">
                                    <a href="{{ route('packages.show', $release) }}" class="table-action table-action--primary">
                                        {{ __('Details') }}
                                    </a>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="{{ $tableColspan }}" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="cube" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No packages found.') }}</p>
                                <p class="table-empty-copy">{{ __('Try removing the current channel filter to surface more releases.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div>
                <x-pagination :paginator="$releases" />
            </div>

            @if ($isAdmin ?? false)
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const headerRow = document.querySelector('table thead tr');
                        if (headerRow && !document.getElementById('select-all')) {
                            const firstHeader = headerRow.querySelector('th');
                            if (firstHeader) {
                                firstHeader.innerHTML = '<input type="checkbox" id="select-all" class="form-checkbox">';
                            }
                        }

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

                    function bulkAction(action) {
                        const selectedCheckboxes = document.querySelectorAll('.release-checkbox:checked');
                        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

                        if (selectedIds.length === 0) {
                            alert("{{ __('Please select at least one package to perform this action.') }}");
                            return;
                        }

                        if (action === 'delete') {
                            if (confirm(`{{ __('Are you sure you want to delete') }} ${selectedIds.length} {{ __('selected package(s)? This action cannot be undone.') }}`)) {
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = "{{ route('packages.bulk-delete') }}";

                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = '_token';
                                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                                form.appendChild(csrfInput);

                                const methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'DELETE';
                                form.appendChild(methodInput);

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
                            const exportData = Array.from(selectedCheckboxes).map(checkbox => {
                                const row = checkbox.closest('tr');
                                const cells = row.querySelectorAll('td');
                                const offset = 1;

                                return {
                                    id: checkbox.value,
                                    version: cells[offset]?.textContent.trim() ?? '',
                                    channel: cells[offset + 1]?.querySelector('span')?.textContent.trim() ?? '',
                                    released: cells[offset + 2]?.textContent.trim() ?? '',
                                    hashVerification: cells[offset + 3]?.querySelector('span')?.textContent.trim() ?? ''
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
        </section>
    </div>
</x-app-sidebar-layout>
