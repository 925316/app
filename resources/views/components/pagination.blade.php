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
<nav class="flex flex-col sm:flex-row px-4 py-3 justify-between items-center text-sm gap-3" 
     role="navigation" 
     aria-label="{{ __('Pagination Navigation') }}">
    
    {{-- Total Records Info --}}
    @if($showTotal && $total > 0)
    <div class="text-zinc-600 dark:text-zinc-400">
        {!! str_replace(':c', '<span class="font-semibold text-zinc-900 dark:text-zinc-100">'.$total.'</span>',
            str_replace(':b', '<span class="font-semibold text-zinc-900 dark:text-zinc-100">'.$to.'</span>',
            str_replace(':a', '<span class="font-semibold text-zinc-900 dark:text-zinc-100">'.$from.'</span>', $label))) !!}
    </div>
    @endif
    
    {{-- Pagination Controls --}}
    <div class="flex items-center gap-1">
        {{-- Previous Page --}}
        @if($paginator->onFirstPage())
        <span class="inline-flex items-center {{ $btnSize }} text-zinc-400 dark:text-zinc-600 cursor-not-allowed rounded-lg">
            <x-icon name="arrow-left" class="w-4 h-4" />
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" 
           rel="prev"
           class="inline-flex items-center {{ $btnSize }} text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-white/30 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 transition-colors">
            <x-icon name="arrow-left" class="w-4 h-4" />
        </a>
        @endif
        
        {{-- Page Numbers --}}
        <div class="hidden sm:flex items-center gap-1">
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
               class="inline-flex items-center {{ $btnSize }} text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors">
                1
            </a>
            @if($start > 2)
            <span class="px-2 text-zinc-400 dark:text-zinc-600">...</span>
            @endif
            @endif
            
            {{-- Page Links --}}
            @for($page = $start; $page <= $end; $page++)
                @if($page == $currentPage)
                <span class="inline-flex items-center {{ $btnSize }} font-semibold text-zinc-900 dark:text-zinc-100 bg-zinc-200 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-lg">
                    {{ $page }}
                </span>
                @else
                <a href="{{ $paginator->url($page) }}" 
                   class="inline-flex items-center {{ $btnSize }} text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors">
                    {{ $page }}
                </a>
                @endif
            @endfor
            
            {{-- Last Page --}}
            @if($end < $lastPage)
            @if($end < $lastPage - 1)
            <span class="px-2 text-zinc-400 dark:text-zinc-600">...</span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}" 
               class="inline-flex items-center {{ $btnSize }} text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors">
                {{ $lastPage }}
            </a>
            @endif
        </div>
        
        {{-- Current Page Info (Mobile) --}}
        <span class="sm:hidden px-3 text-zinc-600 dark:text-zinc-400">
            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $currentPage }}</span>
            <span>/ {{ $lastPage }}</span>
        </span>
        
        {{-- Next Page --}}
        @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" 
           rel="next"
           class="inline-flex items-center {{ $btnSize }} text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-white/30 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 transition-colors">
            <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
        @else
        <span class="inline-flex items-center {{ $btnSize }} text-zinc-400 dark:text-zinc-600 cursor-not-allowed rounded-lg">
            <x-icon name="arrow-right" class="w-4 h-4" />
        </span>
        @endif
    </div>
</nav>
@endif
