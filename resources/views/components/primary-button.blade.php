<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-cool-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-cool-600 focus:bg-gray-700 dark:focus:bg-cool-600 active:bg-gray-900 dark:active:bg-cool-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
