@props([
    'action' => null,
    'method' => 'GET',
    'cleanForm' => true,
    'defaultValues' => null,
    'totalCount' => null,
    'showTotal' => false,
    'title' => 'Filter',
])
<div
    class="mb-6 rounded-2xl border border-[rgb(var(--color-border-subtle))/0.9] bg-gradient-to-r from-[rgb(var(--color-surface-card))] to-[rgb(var(--color-surface-card-muted))/0.95] p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:bg-none">
    <form method="{{ $method }}" action="{{ $action }}"
        class="space-y-4"
        @if ($cleanForm) data-clean-form="true" @endif
        @if ($defaultValues) data-default-values="{{ $defaultValues }}" @endif>
        <div class="flex items-center justify-between gap-2 lg:max-xl:flex-wrap">
            <h4 class="flex items-center text-lg font-semibold text-[rgb(var(--color-text-primary))]">
                <x-icon name="filter" class="mr-2 h-5 w-5 text-[rgb(var(--color-text-secondary))] dark:text-zinc-400" /> {{ $title }}
            </h4>
            @if ($showTotal && $totalCount !== null)
                <div class="flex items-center space-x-2"><span class="rounded-full bg-[rgb(var(--color-surface-card-muted))/0.95] px-3 py-1 text-sm text-[rgb(var(--color-text-secondary))] dark:bg-zinc-800 dark:text-zinc-400">
                        {{ $totalCount }} total </span>
                </div>
            @endif
        </div> {{ $slot }}
    </form>
</div>
