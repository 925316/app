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
        class="filter-box-form"
        role="search" aria-labelledby="{{ $headingId }}"
        @if ($cleanForm) data-clean-form="true" @endif
        @if ($defaultValues) data-default-values="{{ $defaultValues }}" @endif>
        <div class="filter-box-header">
            <div class="filter-box-heading">
                <h2 id="{{ $headingId }}" class="filter-box-title text-lg font-semibold">
                    <span class="filter-box-icon" aria-hidden="true">
                        <x-icon name="filter" class="h-5 w-5 text-current" />
                    </span>
                    <span>{{ $title }}</span>
                </h2>
            </div>
            @if ($showTotal && $totalCount !== null)
                <div class="filter-box-summary">
                    <span class="filter-box-total-label">{{ __('Showing') }}</span>
                    <span class="filter-box-total">{{ $totalCount }} {{ __('total') }}</span>
                </div>
            @endif
        </div>

        <div class="filter-box-body">
            {{ $slot }}
        </div>
    </form>
</section>
