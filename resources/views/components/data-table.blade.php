@props(['headers' => [], 'items' => null, 'emptyMessage' => __('No data found.'), 'emptyColspan' => null]) <div class="table-responsive">
    <table class="data-table">
        <thead class="table-header">
            <tr>
                @foreach ($headers as $header)
                    <th scope="col" class="table-header-cell"> {{ $header }} </th>
                    @endforeach
            </tr>
        </thead>
        <tbody class="table-body"> {{-- Items are rendered here --}} {{ $slot }} {{-- Show empty row if no items in slot --}} @if ($items === null && trim($slot) === '')
                <tr>
                    <td colspan="{{ $emptyColspan ?? count($headers) }}" class="table-empty"> {{ $emptyMessage }} </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
