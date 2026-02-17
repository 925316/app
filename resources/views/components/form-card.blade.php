@props(['title' => null, 'description' => null]) <div class="card-form p-4 sm:p-8">
    <div class="max-w-xl">
        @if ($title)
            <header>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white"> {{ $title }} </h2>
                @if ($description)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300"> {{ $description }} </p>
                @endif
            </header>
        @endif {{ $slot }}
    </div>
</div>
