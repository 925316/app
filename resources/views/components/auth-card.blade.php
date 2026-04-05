@props(['showStatus' => false])

<section class="card-glass auth-card-shell" aria-labelledby="auth-panel-title" data-auth-card>
    <div class="auth-card-shell-body">
        @if ($showStatus)
            <x-auth-session-status :status="session('status')" />
        @endif

        {{ $slot }}
    </div>
</section>
