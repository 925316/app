<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

defineProps({
    title: String,
});

const page = usePage();
</script>

<template>
    <div class="auth-wrapper">
        <div class="auth-content">
            <slot />
        </div>
    </div>
</template>
