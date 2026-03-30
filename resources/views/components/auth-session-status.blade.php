@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'card-shell-muted flex items-start gap-3 text-sm']) }} data-auth-session-status>
        <span class="card-icon-container icon-green h-10 w-10 shrink-0">
            <x-icon name="check" class="h-5 w-5" />
        </span>

        <div class="min-w-0 space-y-1">
            <p class="section-kicker">{{ __('Status') }}</p>
            <p class="card-inline-copy font-medium">{{ $status }}</p>
        </div>
    </div>
@endif
