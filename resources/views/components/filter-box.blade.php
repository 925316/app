@props([
    'action' => null,
    'method' => 'GET',
    'cleanForm' => true,
    'defaultValues' => null,
    'totalCount' => null,
    'showTotal' => false,
    'title' => 'Filter',
])

@php
    $headingId = 'filter-box-title-'.md5((string) $title.(string) $action);
@endphp

<section class="filter-box-shell mb-6 rounded-xl border p-6 shadow-sm" aria-labelledby="{{ $headingId }}" data-filter-box>
    <form method="{{ $method }}" action="{{ $action }}"
        role="search" aria-labelledby="{{ $headingId }}"
        @if ($cleanForm) data-clean-form="true" @endif
        @if ($defaultValues) data-default-values="{{ $defaultValues }}" @endif>
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 id="{{ $headingId }}" class="filter-box-title flex items-center text-lg font-semibold">
                <x-icon name="filter" class="mr-2 h-5 w-5 text-current" /> {{ $title }}
            </h2>
            @if ($showTotal && $totalCount !== null)
                <div class="flex items-center gap-2"><span class="filter-box-total text-sm">
                        {{ $totalCount }} total </span>
                </div>
            @endif
        </div>

        {{ $slot }}
    </form>
</section>
