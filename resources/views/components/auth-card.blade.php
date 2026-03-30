@props(['showStatus' => false])

<section class="card-glass auth-card-shell px-6 py-8 sm:px-7" aria-labelledby="auth-panel-title" data-auth-card>
    @if ($showStatus)
        <x-auth-session-status class="mb-4" :status="session('status')" />
    @endif

    {{ $slot }}
</section>
