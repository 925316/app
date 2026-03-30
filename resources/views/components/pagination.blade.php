@props([
    'paginator' => null,
    'showTotal' => true,
    'label' => __('Showing :a to :b of :c results'),
    'size' => 'regular',
])

@php
    $showTotal = filter_var($showTotal, FILTER_VALIDATE_BOOLEAN);
    
    if (!$paginator) {
        return;
    }
    
    $total = $paginator->total();
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $perPage = $paginator->perPage();
    
    $from = ($currentPage - 1) * $perPage + 1;
    $to = min($currentPage * $perPage, $total);
    
    $sizeClasses = [
        'tiny' => '!px-2 !py-1 !text-xs',
        'small' => '!px-2.5 !py-1.5 !text-xs',
        'regular' => '!px-3 !py-2 !text-sm',
        'big' => '!px-4 !py-2.5 !text-base',
    ];
    
    $btnSize = $sizeClasses[$size] ?? $sizeClasses['regular'];
@endphp

@if($paginator->hasPages())
<nav class="flex flex-col justify-between gap-3 px-4 py-4 text-sm sm:flex-row sm:items-center" 
     role="navigation" 
     aria-label="{{ __('Pagination Navigation') }}">
    
    {{-- Total Records Info --}}
    @if($showTotal && $total > 0)
    <div class="app-shell-body-copy">
        {!! str_replace(':c', '<span class="app-shell-chip-strong font-semibold">'.$total.'</span>',
            str_replace(':b', '<span class="app-shell-chip-strong font-semibold">'.$to.'</span>',
            str_replace(':a', '<span class="app-shell-chip-strong font-semibold">'.$from.'</span>', $label))) !!}
    </div>
    @endif
    
    {{-- Pagination Controls --}}
    <div class="flex items-center gap-1.5">
        {{-- Previous Page --}}
        @if($paginator->onFirstPage())
        <span class="app-shell-body-copy inline-flex cursor-not-allowed items-center rounded-full {{ $btnSize }}">
            <x-icon name="arrow-left" class="w-4 h-4" />
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" 
           rel="prev"
           class="btn btn-secondary inline-flex items-center rounded-full {{ $btnSize }} !px-3 !py-2">
            <x-icon name="arrow-left" class="w-4 h-4" />
        </a>
        @endif
        
        {{-- Page Numbers --}}
        <div class="hidden items-center gap-1.5 sm:flex">
            @php
                $start = max(1, $currentPage - 2);
                $end = min($lastPage, $currentPage + 2);
                
                if ($end - $start < 4) {
                    if ($start == 1) {
                        $end = min($lastPage, $start + 4);
                    } elseif ($end == $lastPage) {
                        $start = max(1, $end - 4);
                    }
                }
            @endphp
            
            {{-- First Page --}}
            @if($start > 1)
            <a href="{{ $paginator->url(1) }}" 
               class="btn btn-secondary inline-flex items-center rounded-full {{ $btnSize }} !px-3 !py-2">
                1
            </a>
            @if($start > 2)
            <span class="app-shell-body-copy px-2">...</span>
            @endif
            @endif
            
            {{-- Page Links --}}
            @for($page = $start; $page <= $end; $page++)
                @if($page == $currentPage)
                <span class="btn btn-primary inline-flex items-center rounded-full {{ $btnSize }} !px-3 !py-2 font-semibold">
                    {{ $page }}
                </span>
                @else
                <a href="{{ $paginator->url($page) }}" 
                   class="btn btn-secondary inline-flex items-center rounded-full {{ $btnSize }} !px-3 !py-2">
                    {{ $page }}
                </a>
                @endif
            @endfor
            
            {{-- Last Page --}}
            @if($end < $lastPage)
            @if($end < $lastPage - 1)
            <span class="app-shell-body-copy px-2">...</span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}" 
               class="btn btn-secondary inline-flex items-center rounded-full {{ $btnSize }} !px-3 !py-2">
                {{ $lastPage }}
            </a>
            @endif
        </div>
        
        {{-- Current Page Info (Mobile) --}}
        <span class="app-shell-body-copy px-3 sm:hidden">
            <span class="app-shell-chip-strong font-semibold">{{ $currentPage }}</span>
            <span>/ {{ $lastPage }}</span>
        </span>
        
        {{-- Next Page --}}
        @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" 
           rel="next"
           class="btn btn-secondary inline-flex items-center rounded-full {{ $btnSize }} !px-3 !py-2">
            <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
        @else
        <span class="app-shell-body-copy inline-flex cursor-not-allowed items-center rounded-full {{ $btnSize }}">
            <x-icon name="arrow-right" class="w-4 h-4" />
        </span>
        @endif
    </div>
</nav>
@endif
