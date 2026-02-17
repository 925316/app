@props(['title' => 'Welcome', 'subtitle' => null, 'logoClass' => null])
<div class="text-center">
    <div
        class="{{ $logoClass ?? 'mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg' }}">
        <x-application-logo class="h-10 w-10 text-white" />
    </div>
    <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white"> {{ $title }} </h2>
    @if ($subtitle)
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300"> {{ $subtitle }} </p>
    @endif
</div>
