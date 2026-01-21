<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-cool-700 border border-gray-300 dark:border-cool-600 rounded-md font-semibold text-xs text-gray-700 dark:text-cool-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-cool-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
