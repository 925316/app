<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Session Management') }}
    </x-slot>

    @php
        $terminateSessionConfirmation = __('Are you sure you want to terminate this session? The client will be disconnected on next heartbeat check. This action cannot be undone.');
    @endphp

    <div>
            @if ($isAdmin && $statistics)
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <x-stat-card :title="__('Total Sessions')" :value="$statistics['total']" icon="server" iconColor="icon-blue" />
                    <x-stat-card :title="__('Active Sessions')" :value="$statistics['active']" icon="success" iconColor="icon-green" />
                    <x-stat-card :title="__('Expired Sessions')" :value="$statistics['expired']" icon="error" iconColor="icon-red" />
                    <x-stat-card :title="__('Unique Accounts')" :value="$statistics['unique_accounts']" icon="users" iconColor="icon-gray" />
                    <x-stat-card :title="__('Unique Devices')" :value="$statistics['unique_devices']" icon="desktop" iconColor="icon-indigo" />
                </div>
            @endif

            <div class="card-shell overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            @if ($isAdmin)
                                {{ __('Session Management') }}
                            @else
                                {{ __('My Sessions') }}
                            @endif
                        </h3>
                    </div>

                    <!-- Filters -->
                    <x-filter-box :action="route('sessions.index')" :showTotal="true" :totalCount="$sessions->total()" :title="__('Filter Sessions')">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                            <div class="space-y-2 md:col-span-3">
                                <label for="status"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</label>
                                <select name="status" id="status" class="form-select form-pill form-select-enhanced">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $currentFilters['status'] === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2 md:col-span-4">
                                <label for="sort"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Sort By') }}</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <select name="sort" id="sort" class="form-select form-pill form-select-enhanced col-span-3">
                                        <option value="last_heartbeat_at"
                                            {{ $currentFilters['sort'] === 'last_heartbeat_at' ? 'selected' : '' }}>
                                            {{ __('Last Heartbeat') }}
                                        </option>
                                        <option value="created_at" {{ $currentFilters['sort'] === 'created_at' ? 'selected' : '' }}>
                                            {{ __('Created') }}
                                        </option>
                                    </select>
                                    <select name="direction" class="form-select form-pill form-select-enhanced col-span-1 px-2 text-center">
                                        <option value="desc" {{ $currentFilters['direction'] === 'desc' ? 'selected' : '' }}>↓</option>
                                        <option value="asc" {{ $currentFilters['direction'] === 'asc' ? 'selected' : '' }}>↑</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-2 md:col-span-5">
                                <label for="search"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search') }}</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="search" id="search" value="{{ $currentFilters['search'] }}"
                                        class="form-input form-pill py-2 pl-10 pr-4"
                                        placeholder="{{ $isAdmin ? __('Search by account username, device name, or session token...') : __('Search by device name or session token...') }}">
                                </div>
                            </div>

                            <div class="flex items-end gap-2 md:col-span-12 md:justify-end">
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('sessions.index') }}" class="btn btn-secondary btn-sm">
                                    {{ __('Reset') }}
                                </a>
                            </div>
                        </div>

                        @php
                            $hasFilters =
                                request()->filled('status') ||
                                request()->filled('search') ||
                                request()->filled('sort');
                        @endphp

                        @if ($hasFilters)
                            <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active Filters') }}</span>
                                    <a href="{{ route('sessions.index') }}"
                                        class="text-sm text-slate-600 transition-colors hover:text-slate-800 dark:text-zinc-300 dark:hover:text-zinc-100">
                                        {{ __('Clear All') }}
                                    </a>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if (request()->filled('status'))
                                        @php
                                            $statusValue = request('status');
                                            $statusLabel = $statusOptions[$statusValue] ?? null;
                                        @endphp
                                        @if ($statusLabel)
                                            <x-filter-badge :label="__('Status:').' '.$statusLabel" color="gray"
                                                :removeUrl="request()->fullUrlWithQuery(['status' => null])" />
                                        @endif
                                    @endif

                                    @if (request()->filled('search'))
                                        <x-filter-badge :label="__('Search:').' &quot;'.request('search').'&quot;'" color="gray"
                                            :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
                                    @endif

                                    @if (request()->filled('sort'))
                                        @php
                                            $sortValue = request('sort');
                                            $directionValue = request('direction', 'desc');
                                            $sortLabel = match ($sortValue) {
                                                'last_heartbeat_at' => __('Last Heartbeat'),
                                                'created_at' => __('Created'),
                                                default => ucfirst($sortValue),
                                            };
                                            $directionArrow = $directionValue === 'desc' ? '↓' : '↑';
                                        @endphp
                                        <x-filter-badge :label="__('Sort:').' '.$sortLabel.' '.$directionArrow" color="gray"
                                            :removeUrl="request()->fullUrlWithQuery(['sort' => null, 'direction' => null])" />
                                    @endif
                                </div>
                            </div>
                        @endif
                    </x-filter-box>

                    <!-- Sessions table -->
                    <x-table :emptyColspan="$isAdmin ? 8 : 7">
                        <x-slot:header>
                            @if ($isAdmin)
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Account
                            </th>
                            @endif
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Device') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('IP Address') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Client Version') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Last Heartbeat') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Created') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                        </x-slot:header>
                        @forelse($sessions as $session)
                            <tr>
                                @if ($isAdmin)
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if ($session->account)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-zinc-500 flex items-center justify-center text-white font-bold text-sm">
                                                    {{ $session->account->initials() }}
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $session->account->username }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $session->account->email }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Unknown') }}</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if ($session->device)
                                        <div>
                                            <div class="text-sm font-medium">
                                                {{ $session->device->hwid_hash ?? __('Unknown Device') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('ID:') }} {{ $session->device->id }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Unknown') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $session->ip_address ?? __('N/A') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $session->client_version ?? __('Unknown') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if ($session->isActive())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                            {{ __('Expired') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if ($session->last_heartbeat_at)
                                        {{ $session->last_heartbeat_at->diffForHumans() }}
                                        <br>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $session->last_heartbeat_at->format('Y-m-d H:i:s') }}</span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('Never') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if ($session->created_at)
                                        {{ $session->created_at->diffForHumans() }}
                                        <br>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $session->created_at->format('Y-m-d H:i:s') }}</span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('Unknown') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('sessions.show', $session) }}"
                                        class="text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-zinc-100">
                                        {{ __('View') }}
                                    </a>
                                    <span class="mx-1 text-gray-400">{{ '|' }}</span>
                                    <form action="{{ route('sessions.destroy', $session) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('{{ $terminateSessionConfirmation }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                            {{ __('Terminate') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 7 }}" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                    {{ __('No sessions found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </x-table>

                    <!-- Pagination -->
                    <div class="mt-4 pagination-container">
                        <x-pagination :paginator="$sessions" />
                    </div>
                </div>
        </div>
    </div>

</x-app-sidebar-layout>
