@props(['showStatus' => false]) <div class="card-glass py-8 px-6">
    @if ($showStatus)
        <x-auth-session-status class="mb-4" :status="session('status')" />
    @endif {{ $slot }}
</div>
