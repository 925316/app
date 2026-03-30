@props([
    'headers' => [],
    'items' => null,
    'emptyMessage' => __('No data found.'),
    'emptyColspan' => null,
    'ariaLabel' => __('Data table'),
])

<div class="table-wrapper" data-table-shell>
    <div class="table-scroll">
        <table class="data-table compact with-hover" aria-label="{{ $ariaLabel }}">
        <thead class="table-header">
            <tr>
                @foreach ($headers as $header)
                    <th scope="col" class="table-header-cell">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="table-body">
            {{ $slot }}

            @if ($items === null && trim($slot) === '')
                <tr>
                    <td colspan="{{ $emptyColspan ?? count($headers) }}" class="table-empty">
                        <div class="table-empty-state">
                            <x-icon name="document" class="table-empty-icon" />
                            <p class="table-empty-title">{{ $emptyMessage }}</p>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
        </table>
    </div>
</div>
