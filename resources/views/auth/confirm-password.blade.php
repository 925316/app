<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Title -->
            <div class="text-center">
                <div
                    class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-br from-zinc-500 to-zinc-600 shadow-lg">
                    <x-application-logo class="h-10 w-10 text-white" />
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('Confirm Password') }}
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Please confirm your password to continue') }}
                </p>
            </div>

            <div
                class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm py-8 px-6 shadow-xl rounded-[2rem] border border-zinc-200/50 dark:border-zinc-700/50">
                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                    @csrf

                    <div class="text-center text-sm text-gray-600 dark:text-gray-300 mb-6">
                        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')"
                            class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <x-text-input id="password"
                                class="block w-full pl-10 pr-3 py-3 border border-zinc-300/50 dark:border-zinc-600/50 rounded-xl bg-white/50 dark:bg-zinc-700/50 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent transition-all duration-200"
                                type="password" name="password" required autocomplete="current-password"
                                placeholder="••••••••" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <x-primary-button class="w-full flex justify-center py-3 px-4">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Confirm Password') }}
                            </span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
