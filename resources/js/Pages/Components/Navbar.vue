<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { cart, fetchCartItems, updateCartItem } from '../../composables/cart'; 
import { wishlist, fetchWishlistItems, addToWishlist, removeFromWishlist } from '../../composables/wishlist';

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
            default:
                router.visit('/default-dashboard'); // Fallback dashboard
                break;
        }
    }
}

// Popup menu visibility
const isCartMenuVisible = ref(false);
const isWishlistMenuVisible = ref(false);

function toggleCartMenu() {
    isCartMenuVisible.value = !isCartMenuVisible.value;
    isWishlistMenuVisible.value = false;
}

function toggleWishlistMenu() {
    isWishlistMenuVisible.value = !isWishlistMenuVisible.value;
    isCartMenuVisible.value = false;
}

fetchCartItems(); 
fetchWishlistItems();

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
                    <template v-if="auth.user">
                        <span @click.prevent="navigateToDashboard" class="cursor-pointer underline">{{ auth.user.firstName }}</span>
                        <button type="button" class="btn btn-outline-danger logoutBtn" @click.prevent="logout">Log Out</button>
                    </template>
                    <template v-else>
                        <a href="/login">
                            <i class="bx bx-user"></i>
                        </a>
                    </template>
                    <a href="/cart" @click.prevent="toggleCartMenu">
                        <i class="bx bx-cart"></i>
                    </a>
                    <a href="/wishlist" @click.prevent="toggleWishlistMenu">
                        <i class="bx bx-heart"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Popup Menu -->
    <div v-if="isCartMenuVisible" class="cart-popup-menu">
        <div v-if="cart.length">
            <div v-for="item in cart" :key="item.festival_id" class="cart-item">
                <span>Festival ID: {{ item.festival_id }}</span>
                <span>Quantity: {{ item.quantity }}</span>
                <button @click="updateCartItem(item.festival_id, item.quantity - 1)">-</button>
                <button @click="updateCartItem(item.festival_id, item.quantity + 1)">+</button>
            </div>
        </div>
        <div v-else class="text-center">
            <p>No tickets in cart.</p>
        </div>
    </div>

    <!-- Wishlist Popup Menu -->
    <div v-if="isWishlistMenuVisible" class="wishlist-popup-menu">
        <div v-if="wishlist.length">
            <div v-for="item in wishlist" :key="item.festival_id" class="wishlist-item">
                <span>Festival ID: {{ item.festival_id }}</span>
                <button @click="removeFromWishlist(item.festival_id)">Remove</button>
            </div>
            <a href="/wishlist" class="view-wishlist-btn">View Wishlist</a>
        </div>
        <div v-else class="text-center">
            <p>No favorites in wishlist.</p>
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
    align-items: center;
    width: 100%;
    padding: 0 20px;
}

.navbar-option {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70px;
}

.navbar-option:last-child {
    justify-content: flex-end;
}

.navbar-option i {
    font-size: 24px;
    color: black !important;
}

.navbar-option a {
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.navbar-suboption {
    display: flex;
    flex-direction: row;
    gap: 25px;
    align-items: center;
}

.cart-popup-menu {
    position: absolute;
    top: 70px;  /* Adjust based on your navbar height */
    right: 20px;
    background-color: white;
    border: 1px solid #ccc;
    padding: 10px;
    width: 250px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}

.view-cart-btn {
    margin-top: 10px;
    padding: 8px 12px;
    background-color: #007bff;
    color: white;
    text-align: center;
    border-radius: 5px;
    text-decoration: none;
}

.wishlist-popup-menu {
    position: absolute;
    top: 70px;  /* Adjust based on your navbar height */
    right: 20px;
    background-color: white;
    border: 1px solid #ccc;
    padding: 10px;
    width: 250px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.wishlist-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.view-wishlist-btn {
    margin-top: 10px;
    padding: 8px 12px;
    background-color: #007bff;
    color: white;
    text-align: center;
    border-radius: 5px;
    text-decoration: none;
}
</style>
