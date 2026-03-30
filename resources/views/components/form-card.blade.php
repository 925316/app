@props(['title' => null, 'description' => null]) <div class="card-form p-4 sm:p-8">
    <div class="max-w-xl">
        @if ($title)
            <header>
                <h2 class="card-form-title text-lg font-medium"> {{ $title }} </h2>
                @if ($description)
                    <p class="card-form-copy mt-1 text-sm"> {{ $description }} </p>
                @endif
            </header>
        @endif {{ $slot }}
    </div>
</div>
