@props([
    'action' => null,
    'method' => 'GET',
    'cleanForm' => true,
    'defaultValues' => null,
    'totalCount' => null,
    'showTotal' => false,
    'title' => 'Filter',
])
<div
    class="mb-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
    <form method="{{ $method }}" action="{{ $action }}"
        @if ($cleanForm) data-clean-form="true" @endif
        @if ($defaultValues) data-default-values="{{ $defaultValues }}" @endif>
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                <x-icon name="filter" class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" /> {{ $title }}
            </h4>
            @if ($showTotal && $totalCount !== null)
                <div class="flex items-center space-x-2"><span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $totalCount }} total </span>
                </div>
            @endif
        </div> {{ $slot }}
    </form>
</div>
