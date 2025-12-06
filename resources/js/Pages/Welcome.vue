<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById("screenshot-container")?.classList.add("!hidden");
    document.getElementById("docs-card")?.classList.add("!row-span-1");
    document.getElementById("docs-card-content")?.classList.add("!flex-row");
    document.getElementById("background")?.classList.add("!hidden");
}
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-900 via-purple-900 to-pink-800"
    >
        <div
            class="relative flex flex-col items-center justify-center min-h-screen"
        >
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full opacity-10 transform -translate-x-1/2 -translate-y-1/2"
                ></div>
                <div
                    class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full opacity-10 transform translate-x-1/3 translate-y-1/3"
                ></div>
            </div>

            <div class="relative z-10 text-center px-4">
                <ApplicationLogo class="w-32 h-32 mx-auto mb-8 text-white" />

                <h1 class="text-6xl font-bold text-white mb-4">
                    Welcome to Impluse
                </h1>

                <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    A modern web application built with Laravel
                    {{ laravelVersion }} and Vue.js
                </p>

                <div
                    class="flex flex-col sm:flex-row gap-4 justify-center mb-12"
                >
                    <template v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-8 py-3 bg-white text-gray-900 font-semibold rounded-full hover:bg-gray-100 transition duration-300 shadow-lg"
                        >
                            Go to Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-8 py-3 bg-white text-gray-900 font-semibold rounded-full hover:bg-gray-100 transition duration-300 shadow-lg"
                            >
                                Login
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-8 py-3 bg-transparent border-2 border-white text-white font-semibold rounded-full hover:bg-white hover:text-gray-900 transition duration-300"
                            >
                                Register
                            </Link>
                        </template>
                    </template>
                </div>

                <div
                    class="inline-flex items-center gap-6 text-gray-300 text-sm"
                >
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span>Laravel {{ laravelVersion }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span>PHP {{ phpVersion }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                        <span>Vue 3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
