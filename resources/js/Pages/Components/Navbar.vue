<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Festival } from '../../../models';
import { cart, fetchCartItems, updateCartItem, clearCart } from '../../composables/cart';
import { wishlist, fetchWishlistItems, removeFromWishlist, clearWishlist } from '../../composables/wishlist';
import CartSidebar from './CartSidebar.vue';
import WishlistSidebar from './WishlistSidebar.vue';

const props = defineProps({
    auth: Object,
});

// URL friendly conversion function
function urlFriendly(str: string) {
    return str
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function logout() {
    router.post('/logout');
}

function navigateToDashboard() {
    const userRole = props.auth?.user?.role;
    
    switch(userRole) {
        case 'admin':
            router.visit('/admin/dashboard');
            break;
        case 'user':
            router.visit('/home');
            break;
        default:
            router.visit('/');
            break;
    }
}

// Sidebar visibility states
const isCartSidebarOpen = ref(false);
const isWishlistSidebarOpen = ref(false);
const isFestivalMenuVisible = ref(false);
const festivals = ref<Festival[]>([]);

async function fetchFestivals() {
    try {
        const { data } = await axios.get<Festival[]>('/api/festivals');
        festivals.value = data;
    } catch (e) {
        console.error('Could not load festivals', e);
    }
}

function toggleCartSidebar() {
    isCartSidebarOpen.value = !isCartSidebarOpen.value;
    if (isCartSidebarOpen.value) {
        isWishlistSidebarOpen.value = false;
    }
}

function toggleWishlistSidebar() {
    isWishlistSidebarOpen.value = !isWishlistSidebarOpen.value;
    if (isWishlistSidebarOpen.value) {
        isCartSidebarOpen.value = false;
    }
}

function toggleFestivalMenu() {
    isFestivalMenuVisible.value = !isFestivalMenuVisible.value;
}

// Initialize data
fetchCartItems();
fetchWishlistItems();
fetchFestivals();
</script>

<template>
    <div class="navbar-container">
        <div class="navbar-options">
            <div class="navbar-option">
                <!-- Logo here -->
                <a href="/" class="logo-link">
                    <img class="navbar-logo" src="/storage/main/logo.png" width="64px" height="64px" alt="Logo">
                </a>
            </div>
            <div class="navbar-option d-flex flex-row gap-5">
                <div class="navbar-suboption">
                    <a href="/">Home</a>
                </div>
                <div class="navbar-suboption position-relative">
                    <span class="cursor-pointer" @click="toggleFestivalMenu">
                      Festival ⩒
                    </span>
                    <ul v-if="isFestivalMenuVisible" class="festival-dropdown">
                        <li v-for="festival in festivals" :key="festival.id">
                            <a :href="`/festivals/${urlFriendly(festival.name)}`">
                                {{ festival.name }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="navbar-option">
                <div class="navbar-suboption">
                    <template v-if="auth?.user">
                        <span @click="navigateToDashboard"
                              class="cursor-pointer user-name">
                            {{ auth.user.firstName }}
                        </span>
                        <button type="button" class="btn btn-outline-danger logoutBtn" @click="logout">
                            Log Out
                        </button>
                    </template>
                    <template v-else>
                        <a href="/login">
                            <i class="bx bx-user"></i>
                        </a>
                    </template>
                    
                    <!-- Cart and Wishlist Icons -->
                    <div class="icon-group">
                        <button @click="toggleWishlistSidebar" class="icon-button" :class="{ active: isWishlistSidebarOpen }">
                            <i class="bx bx-heart"></i>
                            <span v-if="wishlist.length > 0" class="icon-badge">{{ wishlist.length }}</span>
                        </button>
                        
                        <button @click="toggleCartSidebar" class="icon-button" :class="{ active: isCartSidebarOpen }">
                            <i class="bx bx-cart"></i>
                            <span v-if="cart.length > 0" class="icon-badge">{{ cart.length }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Sidebar Components -->
    <CartSidebar :is-open="isCartSidebarOpen" @close="isCartSidebarOpen = false" />
    <WishlistSidebar :is-open="isWishlistSidebarOpen" @close="isWishlistSidebarOpen = false" />
</template>

<style scoped>
/* Importing Google Fonts and Boxicons */
@import "https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap";

.navbar-container {
    height: 70px;
    width: 100%;
    padding: 0 2rem;
    background-color: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.navbar-options {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    height: 100%;
}

.navbar-option {
    display: flex;
    align-items: center;
    height: 100%;
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

.navbar-suboption a {
    text-decoration: none!important;
    color: black;
    font-size: 15px!important;
}

/* Added styles for user name and logout button */
.user-name {
    text-decoration: underline;
    cursor: pointer;
    font-weight: 500;
    color: #2565c7;
}

.logoutBtn {
    min-width: 100px;
    padding: 6px 15px;
    font-weight: 500;
}

.navbar-suboption.position-relative {
    position: relative;
}

.festival-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 0.25rem;
    padding: 0.5rem 0;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    list-style: none;
    min-width: 150px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    z-index: 1000;
}

.festival-dropdown li {
    margin: 0;
    padding: 0;
}

.festival-dropdown li a {
    display: block;
    padding: 0.5rem 1rem;
    color: #333;
    font-size: 14px !important;
    text-decoration: none;
    transition: background-color 0.2s;
}

.festival-dropdown li a:hover {
    background-color: #f5f5f5;
}

.cursor-pointer {
    cursor: pointer;
}

/* Icon group for cart and wishlist */
.icon-group {
    display: flex;
    gap: 1rem;
    margin-left: 1rem;
}

.icon-button {
    position: relative;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #555;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem;
    transition: color 0.2s;
}

.icon-button:hover, .icon-button.active {
    color: #2565c7;
}

.icon-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #dc3545;
    color: white;
    font-size: 0.7rem;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
