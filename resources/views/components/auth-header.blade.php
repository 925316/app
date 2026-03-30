@props(['title' => 'Welcome', 'subtitle' => null, 'logoClass' => null, 'iconName' => null, 'iconClass' => null])

<div class="auth-header-shell text-center" data-auth-header>
    <div class="{{ $logoClass ?? 'auth-header-mark mx-auto flex h-16 w-16 items-center justify-center' }}">
        @if ($iconName)
            <x-icon :name="$iconName" :class="$iconClass ?? 'h-10 w-10 text-white'" />
        @else
            <x-application-logo class="h-10 w-10" />
        @endif
    </div>
    <h1 id="auth-panel-title" class="auth-header-title mt-6 text-3xl font-bold">{{ $title }}</h1>
    @if ($subtitle)
        <p class="auth-header-subtitle mt-2 text-sm">{{ $subtitle }}</p>
    @endif
</div>
