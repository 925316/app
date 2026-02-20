@props([
    'paginator' => null,
    'showTotal' => true,
    'label' => 'Showing :a to :b of :c results',
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
     aria-label="Pagination Navigation">
    
    {{-- Total Records Info --}}
    @if($showTotal && $total > 0)
    <div class="text-gray-600 dark:text-gray-400">
        {!! str_replace(':c', '<span class="font-semibold text-gray-900 dark:text-white">'.$total.'</span>',
            str_replace(':b', '<span class="font-semibold text-gray-900 dark:text-white">'.$to.'</span>',
            str_replace(':a', '<span class="font-semibold text-gray-900 dark:text-white">'.$from.'</span>', $label))) !!}
    </div>
    @endif
    
    {{-- Pagination Controls --}}
    <div class="flex items-center gap-1">
        {{-- Previous Page --}}
        @if($paginator->onFirstPage())
        <span class="inline-flex items-center {{ $btnSize }} text-gray-400 dark:text-gray-600 cursor-not-allowed rounded-md">
            <x-icon name="arrow-left" class="w-4 h-4" />
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" 
           rel="prev"
           class="inline-flex items-center {{ $btnSize }} text-gray-600 dark:text-gray-400 
                  bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                  rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 
                  hover:text-gray-900 dark:hover:text-gray-200
                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 
                  dark:focus:ring-offset-gray-800 transition-colors">
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
               class="inline-flex items-center {{ $btnSize }} text-gray-600 dark:text-gray-400 
                      bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                      rounded-md hover:bg-gray-50 dark:hover:bg-gray-700
                      hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                1
            </a>
            @if($start > 2)
            <span class="px-2 text-gray-400 dark:text-gray-600">...</span>
            @endif
            @endif
            
            {{-- Page Links --}}
            @for($page = $start; $page <= $end; $page++)
                @if($page == $currentPage)
                <span class="inline-flex items-center {{ $btnSize }} font-semibold 
                            text-white bg-primary-600 dark:bg-primary-500 
                            border border-primary-600 dark:border-primary-500 rounded-md">
                    {{ $page }}
                </span>
                @else
                <a href="{{ $paginator->url($page) }}" 
                   class="inline-flex items-center {{ $btnSize }} text-gray-600 dark:text-gray-400 
                          bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                          rounded-md hover:bg-gray-50 dark:hover:bg-gray-700
                          hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                    {{ $page }}
                </a>
                @endif
            @endfor
            
            {{-- Last Page --}}
            @if($end < $lastPage)
            @if($end < $lastPage - 1)
            <span class="px-2 text-gray-400 dark:text-gray-600">...</span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}" 
               class="inline-flex items-center {{ $btnSize }} text-gray-600 dark:text-gray-400 
                      bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                      rounded-md hover:bg-gray-50 dark:hover:bg-gray-700
                      hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                {{ $lastPage }}
            </a>
            @endif
        </div>
        
        {{-- Current Page Info (Mobile) --}}
        <span class="sm:hidden px-3 text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-gray-900 dark:text-white">{{ $currentPage }}</span>
            <span>/ {{ $lastPage }}</span>
        </span>
        
        {{-- Next Page --}}
        @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" 
           rel="next"
           class="inline-flex items-center {{ $btnSize }} text-gray-600 dark:text-gray-400 
                  bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                  rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 
                  hover:text-gray-900 dark:hover:text-gray-200
                  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 
                  dark:focus:ring-offset-gray-800 transition-colors">
            <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
        @else
        <span class="inline-flex items-center {{ $btnSize }} text-gray-400 dark:text-gray-600 cursor-not-allowed rounded-md">
            <x-icon name="arrow-right" class="w-4 h-4" />
        </span>
        @endif
    </div>
</nav>
@endif
