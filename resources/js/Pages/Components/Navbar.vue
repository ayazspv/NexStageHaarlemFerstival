<script setup lang="ts">

import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Define TypeScript interface for User
interface User {
    id: number;
    firstName: string;
    lastName: string;
    email: string;
    role: string;
}

// Get auth data from Inertia
const page = usePage();
const auth = computed(() => page.props.auth as { user: User | null });

const csrfToken = page.props.csrf_token as string || "";
function logout() {
    router.post("/logout", {}, { headers: { "X-CSRF-TOKEN": csrfToken } });
}

function navigateToDashboard() {
    if (auth.value.user) {
        switch (auth.value.user.role) {
            case 'admin':
                router.visit('/dashboard'); // Redirect to the admin dashboard
                break;
            case 'user':
                router.visit('/home'); // Redirect to the user dashboard
                break;
            // Add more roles as needed
            default:
                router.visit('/default-dashboard'); // Fallback dashboard
                break;
        }
    }
}


</script>

<template>
    <div class="navbar-container">
        <div class="navbar-options">
            <div class="navbar-option">
                <!-- Logo here -->
                <a href="/" class="logo-link">
                    <img src="/path-to-logo.png" alt="Logo" class="navbar-logo">
                </a>
            </div>
            <div class="navbar-option">
            </div>
            <div class="navbar-option">
                <div class="navbar-suboption">
                    <!-- <a href="/login">
                        <i class="bx bx-user"></i>
                    </a>
                    Check if user is logged in -->
                    <template v-if="auth.user">
                        <span @click.prevent="navigateToDashboard" class="cursor-pointer underline">{{ auth.user.firstName }}</span>
                        <a href="/cart">
                            <i class="bx bx-cart"></i>
                        </a>
                        <a href="/favorites">
                            <i class="bx bx-heart"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger logoutBtn" @click.prevent="logout">Log Out</button>
                    </template>
                    <template v-else>
                        <a href="/login">
                            <i class="bx bx-user"></i>
                        </a>
                        <a href="/cart">
                            <i class="bx bx-cart"></i>
                        </a>
                        <a href="/favorites">
                            <i class="bx bx-heart"></i>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Importing Google Fonts and Boxicons */
@import "https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap";
@import "https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css";

.navbar-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    /* Center content horizontally */
    top: 0;
    width: 100%;
    height: 70px;
    background-color: #fff;
    border-bottom: solid 1px rgba(204, 204, 204, 255);
    text-decoration: none;
}

.navbar-options {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    /* Spread items evenly */
    align-items: center;
    /* Align items vertically */
    width: 100%;
    padding: 0 20px;
    /* Add padding for spacing */
}

.navbar-option {
    flex: 1;
    /* Each item takes equal width */
    text-align: center;
    /* Centers text inside */
    display: flex;
    justify-content: center;
    /* Ensures content is centered */
    align-items: center;
    /* Align items vertically */
    min-height: 70px;
    /* Matches navbar height */
}

/* Ensure last item is aligned to the right */
.navbar-option:last-child {
    justify-content: flex-end;
    /* Push the icon to the right */
}

/* Fix icon scaling */
.navbar-option i {
    font-size: 24px;
    /* Set a fixed size */
    line-height: 1;
    display: inline-block;
    /* Prevents stretching */
    color: black !important;
}

/* Fix the link styling */
.navbar-option a {
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    /* Ensures the link doesn't stretch */
    height: 100%;
}

.navbar-suboption {
    display: flex;
    flex-direction: row;
    gap: 25px;
    /* margin-left: 90%; */
    align-items: center;
}

/* Styles for the logo */
.logo-link {
    height: 100%;
    display: flex;
    align-items: center;
    /*border: solid 1px rgba(204, 204, 204, 255);*/
    /* Delete when image of the logo is uploaded */
}

.navbar-logo {
    height: 50px;
    /* Adjust this value based on logo size */
    width: auto;
    object-fit: contain;
}

.navbar-option:first-child {
    justify-content: flex-start;
    /* Aligns the logo to the left */
}
.logoutBtn {
    white-space: nowrap;
}
</style>
