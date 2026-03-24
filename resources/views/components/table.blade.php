@props([
    'headers' => [],
    'items' => null,
    'emptyMessage' => 'No data found.',
    'emptyColspan' => null,
    'searchable' => false,
    'searchPlaceholder' => 'Search...',
    'searchColumn' => null,
    'sortable' => false,
    'sortableColumns' => [],
    'striped' => false,
    'compact' => false,
    'hover' => true,
    'name' => 'table-'.uniqid(),
])

@php
    $searchable = filter_var($searchable, FILTER_VALIDATE_BOOLEAN);
    $sortable = filter_var($sortable, FILTER_VALIDATE_BOOLEAN);
    $striped = filter_var($striped, FILTER_VALIDATE_BOOLEAN);
    $compact = filter_var($compact, FILTER_VALIDATE_BOOLEAN);
    $hover = filter_var($hover, FILTER_VALIDATE_BOOLEAN);
    
    if (is_string($headers)) {
        $headers = explode(',', $headers);
    }
    
    if (!empty($sortableColumns) && is_string($sortableColumns)) {
        $sortableColumns = array_map('trim', explode(',', $sortableColumns));
    }
    
    $emptyColspan = $emptyColspan ?? count($headers);
@endphp

<div class="table-wrapper">
    {{-- Search Bar --}}
    @if($searchable)
    <div class="p-3 border-b border-gray-100 dark:border-gray-700">
        <div class="relative">
            <x-icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input type="text" 
                   id="search-{{ $name }}"
                   placeholder="{{ __($searchPlaceholder) }}"
                   class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-zinc-500"
                   oninput="filterTable_{{ str_replace('-', '_', $name) }}(this.value)">
        </div>
    </div>
    @endif
    
    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="data-table {{ $striped ? 'striped' : '' }} {{ $compact ? 'compact' : '' }} {{ $hover ? 'with-hover' : '' }}"
               id="{{ $name }}">
            <thead class="table-header">
                <tr>
                    @if($headers instanceof \Illuminate\View\ComponentSlot)
                        {{-- Custom header slot --}}
                        {{ $headers }}
                    @else
                        @foreach($headers as $key => $header)
                            @php
                                $columnKey = is_array($header) ? ($key ?? $header['key'] ?? '') : $key;
                                $columnLabel = is_array($header) ? ($header['label'] ?? '') : $header;
                                $isSortable = $sortable && (empty($sortableColumns) || in_array($columnKey, $sortableColumns));
                            @endphp
                            <th scope="col" 
                                class="table-header-cell {{ $isSortable ? 'cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700' : '' }}"
                                @if($isSortable)
                                data-column="{{ $columnKey }}"
                                data-sort-dir=""
                                onclick="sortTable_{{ str_replace('-', '_', $name) }}('{{ $columnKey }}', this)"
                                @endif>
                                <div class="flex items-center gap-2">
                                    <span>{{ $columnLabel }}</span>
                                    @if($isSortable)
                                    <span class="sort-icon text-gray-400 dark:text-gray-500">
                                        <x-icon name="funnel" class="w-3 h-3" />
                                    </span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody class="table-body">
                {{-- Slot content --}}
                {{ $slot }}
                
                {{-- Empty state if no slot content --}}
                @if(trim($slot) === '')
                <tr>
                    <td colspan="{{ $emptyColspan }}" class="table-empty">
                        <div class="flex flex-col items-center py-8">
                            <x-icon name="document" class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" />
                            <p class="text-gray-500 dark:text-gray-400">{{ __($emptyMessage) }}</p>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    {{-- Pagination slot --}}
    @isset($pagination)
    <div class="border-t border-gray-100 dark:border-gray-700">
        {{ $pagination }}
    </div>
    @endisset
</div>

{{-- JavaScript for Search and Sort --}}
@if($searchable || $sortable)
<script>
    @if($searchable)
    function filterTable_{{ str_replace('-', '_', $name) }}(query) {
        const table = document.getElementById('{{ $name }}');
        const rows = table.querySelectorAll('tbody tr');
        const searchQuery = query.toLowerCase().trim();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchQuery) ? '' : 'none';
        });
    }
    @endif
    
    @if($sortable)
    let sortDirection_{{ str_replace('-', '_', $name) }} = {};
    
    function sortTable_{{ str_replace('-', '_', $name) }}(column, th) {
        const table = document.getElementById('{{ $name }}');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr:not(.no-sort)'));
        const columnIndex = Array.from(th.parentElement.children).indexOf(th);
        
        // Toggle direction
        const currentDir = sortDirection_{{ str_replace('-', '_', $name) }}[column] || 'asc';
        const newDir = currentDir === 'asc' ? 'desc' : 'asc';
        sortDirection_{{ str_replace('-', '_', $name) }}[column] = newDir;
        
        // Update sort icons
        table.querySelectorAll('th').forEach(header => {
            header.querySelector('.sort-icon')?.classList.remove('text-zinc-600', 'dark:text-zinc-400');
            header.querySelector('.sort-icon')?.classList.add('text-gray-400', 'dark:text-gray-500');
        });
        th.querySelector('.sort-icon')?.classList.remove('text-gray-400', 'dark:text-gray-500');
        th.querySelector('.sort-icon')?.classList.add('text-zinc-600', 'dark:text-zinc-400');
        
        // Sort rows
        rows.sort((a, b) => {
            let aVal = a.children[columnIndex]?.textContent?.trim() || '';
            let bVal = b.children[columnIndex]?.textContent?.trim() || '';
            
            // Try numeric comparison
            const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
            const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return newDir === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            // String comparison
            return newDir === 'asc' 
                ? aVal.localeCompare(bVal) 
                : bVal.localeCompare(aVal);
        });
        
        // Re-append rows
        rows.forEach(row => tbody.appendChild(row));
    }
    @endif
</script>
@endif
