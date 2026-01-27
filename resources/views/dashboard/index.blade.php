<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm rounded-xl shadow-sm border border-cool-200/50 dark:border-cool-700/50 p-6">
        @if($isAdmin ?? false)
            @include('dashboard.admin-panel')
        @else
            @include('dashboard.user-panel')
        @endif
    </div>
</x-app-sidebar-layout>
