<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import InputError from "@/Components/InputError.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: "",
    },
});

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Register" />

        <h1>Impluse</h1>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="input-box">
                <input
                    id="name"
                    type="text"
                    placeholder="Name"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <i class="bx bxs-user icon"></i>
                <InputError class="input-error" :message="form.errors.name" />
            </div>

            <div class="input-box">
                <input
                    id="email"
                    type="email"
                    placeholder="Email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <i class="bx bxs-envelope icon"></i>
                <InputError class="input-error" :message="form.errors.email" />
            </div>

            <div class="input-box">
                <input
                    id="password"
                    type="password"
                    placeholder="Password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <i class="bx bxs-lock-alt icon"></i>
                <InputError
                    class="input-error"
                    :message="form.errors.password"
                />
            </div>

            <div class="input-box">
                <input
                    id="password_confirmation"
                    type="password"
                    placeholder="Confirm Password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <i class="bx bxs-lock-alt icon"></i>
                <InputError
                    class="input-error"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <button type="submit" class="auth-btn" :disabled="form.processing">
                {{ form.processing ? "Registering..." : "Register" }}
            </button>

            <div class="auth-link">
                <p>
                    Already have an account?
                    <Link :href="route('login')">Login</Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
