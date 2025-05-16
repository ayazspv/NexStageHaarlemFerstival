<script setup lang="ts">
interface Props {
    email: string
}

const props = defineProps<Props>()

import AppLayout from './Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Recaptcha from './Components/Recaptcha.vue';

const page = usePage();
const csrfToken = page.props.csrf_token as string || "";

const isForgotPassword = ref(false); // Toggle for forgot password flow
const step = ref(1); // Current step in forgot password flow
const successMessage = ref<string | null>(null);
const errorMessage = ref<string | null>(null);

const credentialForm = useForm({
    email: "",
    password: "",
    password_confirmation: "",
    recaptcha: "",
});

function onRecaptchaVerifiedFP(token: string) {
    credentialForm.recaptcha = token;
}

function redirectToLogin() {
    setTimeout(() => {
        router.visit("/login");
    }, 5000);
}

async function resetPassword() {
    // Reset messages
    successMessage.value = null;
    errorMessage.value = null;

    // Check if reCAPTCHA is completed
    if (!credentialForm.recaptcha) {
        errorMessage.value = "Please complete the reCAPTCHA.";
        return;
    }

    credentialForm.email = props.email;

    try {
        // Call the API to reset the password
        const response = await fetch("/api/reset-password", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json",
            },
            body: JSON.stringify({
                email: credentialForm.email,
                password: credentialForm.password,
                password_confirmation: credentialForm.password_confirmation,
            }),
        });

        const data = await response.json();

        if (response.ok) {
            successMessage.value = data.message + " Redirecting to login in 10 seconds...";
            credentialForm.reset();

            redirectToLogin()
        } else {
            errorMessage.value = data.message || "Failed to reset password.";
            credentialForm.reset();
        }
    } catch (error) {
        errorMessage.value = "A network error occurred. Please try again.";
    } finally {
        (window as any).grecaptcha.reset(); // Reset reCAPTCHA if used
    }
}

</script>

<template>
    <AppLayout title="Forgot Password">
        <div class="container-fluid d-flex align-items-center justify-content-center mt-5">
            <div class="login-window d-flex flex-column align-content-center gap-3">
                <h1>Reset Password</h1>

                <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
                <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

                <p v-if="email">Reset link sent to: {{ email }}</p>
                <form @submit.prevent="resetPassword" class="d-flex flex-column">
                    <div class="mb-3">
                        <label for="password" class="form-label mb-2">Password</label>
                        <input id="password" v-model="credentialForm.password" type="password" name="password"
                            class="form-control" placeholder="Enter your password" />
                    </div>
                    <div class="mb-3">
                        <label for="passwordRepeat" class="form-label mb-2">Repeat your Password</label>
                        <input id="passwordRepeat" v-model="credentialForm.password_confirmation" type="password"
                            name="password_confirmation" class="form-control" placeholder="Enter your password again" />
                    </div>
                    <Recaptcha @verified="onRecaptchaVerifiedFP" />
                    <button type="submit" class="btn btn-primary mt-3">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.login-window {
    min-width: 400px;
    padding: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    background-color: #ffffff;
}

.alert {
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
}
</style>