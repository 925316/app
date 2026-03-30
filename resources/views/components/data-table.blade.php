@props([
    'headers' => [],
    'items' => null,
    'emptyMessage' => __('No data found.'),
    'emptyColspan' => null,
    'ariaLabel' => __('Data table'),
])

<div class="table-responsive" data-table-shell>
    <table class="data-table" aria-label="{{ $ariaLabel }}">
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
                    <td colspan="{{ $emptyColspan ?? count($headers) }}" class="table-empty">{{ $emptyMessage }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
