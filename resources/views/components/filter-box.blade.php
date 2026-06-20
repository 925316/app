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
    $shouldShowTotal = filter_var($showTotal, FILTER_VALIDATE_BOOLEAN) && $totalCount !== null;
@endphp

<section class="filter-box-shell atelier-filter-console mb-6" aria-labelledby="{{ $headingId }}" data-filter-box data-atelier-filter-console>
    <form method="{{ $method }}" action="{{ $action }}"
        class="filter-box-form"
        role="search" aria-labelledby="{{ $headingId }}"
        @if ($cleanForm) data-clean-form="true" @endif
        @if ($defaultValues) data-default-values="{{ $defaultValues }}" @endif>
        <div class="filter-box-header atelier-filter-console__header">
            <div class="filter-box-heading">
                <h2 id="{{ $headingId }}" class="filter-box-title text-lg font-semibold">
                    <span class="filter-box-icon" aria-hidden="true">
                        <x-icon name="filter" class="h-5 w-5 text-current" />
                    </span>
                    <span>{{ $title }}</span>
                </h2>
            </div>
            @if ($shouldShowTotal)
                <div class="filter-box-summary" aria-live="polite">
                    <span class="filter-box-total-label">{{ __('Showing') }}</span>
                    <span class="filter-box-total">{{ $totalCount }} {{ __('total') }}</span>
                </div>
            @endif
        </div>

        <div class="filter-box-body atelier-filter-console__body">
            {{ $slot }}
        </div>
    </form>
</section>
