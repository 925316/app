@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-cool-600 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-md shadow-sm bg-white dark:bg-cool-700 text-gray-900 dark:text-white']) }}>
