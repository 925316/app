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
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Login" />

        <h1>Impluse</h1>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="input-box">
                <input
                    id="email"
                    type="email"
                    placeholder="Email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <i class="bx bxs-user icon"></i>
                <InputError class="input-error" :message="form.errors.email" />
            </div>

            <div class="input-box">
                <input
                    id="password"
                    type="password"
                    placeholder="Password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <i class="bx bxs-lock-alt icon"></i>
                <InputError
                    class="input-error"
                    :message="form.errors.password"
                />
            </div>

            <div class="remember-forgot">
                <label>
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span>Remember me</span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')">
                    Forgot password?
                </Link>
            </div>

            <button type="submit" class="auth-btn" :disabled="form.processing">
                {{ form.processing ? "Logging in..." : "Login" }}
            </button>

            <div class="auth-link">
                <p>
                    Don't have an account?
                    <Link :href="route('register')">Register</Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
