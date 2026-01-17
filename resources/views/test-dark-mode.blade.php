<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dark Mode Test') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Dark Mode Toggle Test</h3>
                    <p class="mb-4">This page demonstrates the dark mode functionality. Use the toggle button in the navigation bar to switch between light and dark themes.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/50 dark:to-blue-800/50 p-6 rounded-xl border border-blue-200/50 dark:border-blue-700/50 shadow-sm">
                            <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Light Theme Features</h4>
                            <ul class="text-sm text-blue-600 dark:text-blue-300 space-y-1">
                                <li>• Clean white backgrounds</li>
                                <li>• Subtle gradients</li>
                                <li>• Soft shadows</li>
                                <li>• High contrast text</li>
                            </ul>
                        </div>
                        
                        <div class="bg-gradient-to-br from-gray-800 to-gray-900 dark:from-gray-900/80 dark:to-gray-800/80 p-6 rounded-xl border border-gray-700/50 dark:border-gray-600/50 shadow-sm">
                            <h4 class="font-semibold text-gray-100 dark:text-white mb-2">Dark Theme Features</h4>
                            <ul class="text-sm text-gray-300 dark:text-gray-400 space-y-1">
                                <li>• Dark backgrounds</li>
                                <li>• Enhanced contrast</li>
                                <li>• Reduced eye strain</li>
                                <li>• Modern aesthetic</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Theme Persistence</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Your theme preference is automatically saved to localStorage and will persist across browser sessions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
