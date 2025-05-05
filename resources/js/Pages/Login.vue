<script setup lang="ts">
import AppLayout from "../Pages/Layouts/AppLayout.vue";
import { ref } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
import Recaptcha from "./Components/Recaptcha.vue";

const page = usePage();
const csrfToken = page.props.csrf_token as string || "";

const isForgotPassword = ref(false); // Toggle for forgot password flow
const step = ref(1); // Current step in forgot password flow
const successMessage = ref<string | null>(null);
const errorMessage = ref<string | null>(null);

const loginForm = useForm({
    email: "",
    password: "",
    recaptcha: "",
});

const forgotPasswordForm = useForm({
    email: "",
    token: "",
    password: "",
    password_confirmation: "",
    recaptcha: "",
});


function login() {
    if (!loginForm.recaptcha) {
        errorMessage.value = "Please complete the reCAPTCHA.";
        return;
    }

    loginForm.post("/login", {
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).flat().join(", ");
            successMessage.value = null;
            (window as any).grecaptcha.reset();
        }
    });
}

function onRecaptchaVerifiedLogin(token: string) {
    loginForm.recaptcha = token;
}

function onRecaptchaVerifiedFP(token: string) {
    forgotPasswordForm.recaptcha = token;
}

function redirectToSignup() {
    router.visit("/signup");
}

function handleForgotPassword() {
    successMessage.value = null;
    errorMessage.value = null;

    if (step.value === 1) {
        // Step 1: Request reset email
        try {
             forgotPasswordForm.post("/admin/password/reset/request", {
                onSuccess: () => {
                    step.value = 2;
                    successMessage.value = "A 6-digit PIN has been sent to your email.";
                },
                onError: (errors) => {
                    errorMessage.value = errors.email || "Failed to send reset email.";
                },
            });
        } catch (err) {
            console.error(err);
            errorMessage.value = "An error occurred. Please try again.";
        }
    } else if (step.value === 2) {
        // Step 2: Verify PIN
        try {
            forgotPasswordForm.post("/admin/password/reset/verify", {
                onSuccess: () => {
                    step.value = 3;
                    successMessage.value = "PIN verified successfully. Set your new password.";
                },
                onError: (errors) => {
                    errorMessage.value = errors.pin || "Invalid or expired PIN.";
                },
            });
        } catch (err) {
            console.error(err);
            errorMessage.value = "An error occurred. Please try again.";
        }
    } else if (step.value === 3) {
        // Step 3: Reset password
        try {
             forgotPasswordForm.post("/admin/password/reset", {
                onSuccess: () => {
                    successMessage.value = "Password reset successfully. Redirecting to login...";
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                },
                onError: (errors) => {
                    errorMessage.value = errors.password || "Failed to reset password. Please try again.";
                },
            });
        } catch (err) {
            console.error(err);
            errorMessage.value = "An error occurred. Please try again.";
        }
    }

}

</script>

<template>

    <AppLayout title="Login">
        <div class="container-fluid d-flex align-items-center justify-content-center mt-5">
            <div class="login-window d-flex flex-column align-content-center gap-3">
                <div class="text-center mt-3">
                    <h1>{{ isForgotPassword ? "Forgot Password" : "Login" }}</h1>
                </div>

                <!-- Success and Error Messages -->
                <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
                <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

                <!-- Login Form -->
                <div v-if="!isForgotPassword" class="d-flex flex-column">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        v-model="loginForm.email"
                        type="text"
                        class="form-control"
                        placeholder="Enter your email"
                    />

                    <label for="password" class="form-label mt-3">Password</label>
                    <input
                        id="password"
                        v-model="loginForm.password"
                        type="password"
                        class="form-control"
                        placeholder="Enter your password"
                    />

                    <Recaptcha @verified="onRecaptchaVerifiedLogin" />

                    <button @click="login" class="btn btn-primary mt-3">
                        Login
                    </button>

                    <button class="btn btn-outline-secondary mt-3" @click="redirectToSignup">
                            Register
                        </button>

                    <small class="mt-2">
                        <a href="#" @click.prevent="isForgotPassword = true">Forgot your password?</a>
                    </small>
                </div>

                <!-- Forgot Password Steps -->
                <div v-if="isForgotPassword">
                    <div v-if="step === 1" class="d-flex flex-column">
                        <label for="forgotEmail" class="form-label">Email</label>
                        <input
                            id="forgotEmail"
                            v-model="forgotPasswordForm.email"
                            type="email"
                            class="form-control"
                            placeholder="Enter your email"
                        />
                        <button @click="handleForgotPassword" class="btn btn-primary mt-3">
                            Send Reset Email
                        </button>
                    </div>

                    <div v-if="step === 2" class="d-flex flex-column">
                        <label for="token" class="form-label">6-Digit PIN</label>
                        <input
                            id="token"
                            v-model="forgotPasswordForm.token"
                            type="text"
                            class="form-control"
                            maxlength="6"
                            placeholder="Enter PIN sent to your email"
                        />
                        <button @click="handleForgotPassword" class="btn btn-primary mt-3">
                            Verify PIN
                        </button>
                    </div>

                    <div v-if="step === 3" class="d-flex flex-column">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input
                            id="newPassword"
                            v-model="forgotPasswordForm.password"
                            type="password"
                            class="form-control"
                            placeholder="Enter new password"
                        />

                        <label for="confirmPassword" class="form-label mt-3">Confirm Password</label>
                        <input
                            id="confirmPassword"
                            v-model="forgotPasswordForm.password_confirmation"
                            type="password"
                            class="form-control"
                            placeholder="Confirm new password"
                        />

                        <button @click="handleForgotPassword" class="btn btn-primary mt-3">
                            Reset Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.login-window {
    width: 400px;
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
