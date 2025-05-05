<script setup lang="ts">
import AppLayout from "../Layouts/AppLayout.vue";
import Recaptcha from "../Components/Recaptcha.vue";
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

const page = usePage();
const csrfToken = page.props.csrf_token as string || "";
const successMessage = ref<string | null>(null);
const errorMessage = ref<string | null>(null);

const signupForm = useForm({
    firstName: "",
    lastName: "",
    username: "",
    email: "",
    phoneNumber: "",
    password: "",
    password_confirmation: "",
    role: "user",
    recaptcha: "",
});

function onRecaptchaVerified(token: string) {
    signupForm.recaptcha = token;
}

async function signup() {
    if (!signupForm.recaptcha) {
        errorMessage.value = "Please complete the reCAPTCHA.";
        return;
    }

    signupForm.username = signupForm.email.split("@")[0];

    signupForm.post("/signup", {
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
        onSuccess: () => {
            successMessage.value = "Registration successful!";
            errorMessage.value = null;
            signupForm.reset();
            (window as any).grecaptcha.reset();
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).flat().join(", ");
            successMessage.value = null;
            (window as any).grecaptcha.reset();
        },
    });
}
</script>

<template>
    <AppLayout title="Signup">
        <div class="container-fluid d-flex align-items-center justify-content-center mt-5">
            <div class="register-window d-flex flex-column align-content-center gap-3">

                <!-- Success and Error Messages -->
                <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
                <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

                <!-- Registration Form -->
                <form @submit.prevent="signup" class="d-flex flex-column">
                    <div class="form-group mb-3">
                        <a href="/login">> Login</a>
                    </div>

                    <div class="text-center mb-3">
                        <h1>Registration Form</h1>
                    </div>

                    <div class="d-flex flex-row gap-3 mb-3">
                        <div class="form-group">
                            <label for="firstName" class="form-label">First Name</label>
                            <input id="firstName" v-model="signupForm.firstName" type="text" class="form-control"
                                placeholder="John" required />
                        </div>

                        <div class="form-group">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input id="lastName" v-model="signupForm.lastName" type="text" class="form-control"
                                placeholder="Doe" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" v-model="signupForm.email" type="email" class="form-control"
                            placeholder="johndoe@example.com" required />
                    </div>

                    <div class="form-group mt-3">
                        <label for="phoneNumber" class="form-label">Phone Number</label>
                        <input id="phoneNumber" v-model="signupForm.phoneNumber" type="text" class="form-control"
                            placeholder="+31612345678" required />
                    </div>

                    <div class="form-group mt-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" v-model="signupForm.password" type="password" class="form-control"
                            placeholder="Enter your password" required />
                    </div>

                    <div class="form-group mt-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" v-model="signupForm.password_confirmation" type="password"
                            class="form-control" placeholder="Confirm your password" required />
                    </div>


                    <Recaptcha @verified="onRecaptchaVerified" />

                    <button type="submit" class="btn btn-primary mt-3">
                        Sign Up
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.register-window {
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
