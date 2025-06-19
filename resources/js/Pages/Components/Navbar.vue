<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Festival } from '../../../models';
import { cart, fetchCartItems, updateCartItem, clearCart } from '../../composables/cart';
import { wishlist, fetchWishlistItems, removeFromWishlist, clearWishlist } from '../../composables/wishlist';
import CartSidebar from './CartSidebar.vue';
import WishlistSidebar from './WishlistSidebar.vue';
import {parseToUrl} from "../../../utils";

// Use the usePage hook to get auth data directly
const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Keep props for backward compatibility
const props = defineProps({
    auth: Object,
});

// Debug auth status on mount
onMounted(() => {
    console.log('Auth from props:', props.auth);
    console.log('Auth from page:', page.props.auth);
});

// Choose most reliable auth source
const user = computed(() => authUser.value || props.auth?.user);

// URL friendly conversion function
function urlFriendly(str: string) {
    return str
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function logout() {
    // Get the CSRF token from Inertia page props
    const token = page.props.csrf_token;

    fetch('/logout', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token || '',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.ok || response.redirected) {
            window.location.href = '/'; // Redirect to home page after logout
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
    });
}

function navigateToDashboard() {
    const userRole = user.value?.role;

    switch(userRole) {
        case 'admin':
            router.visit('/admin/dashboard');
            break;
        case 'employee':
            router.visit('/qr-reader');
            break;
        case 'user':
            router.visit('/user/personal-program');
            break;
        default:
            router.visit('/');
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
            <!-- Left section - Logo -->
            <div class="navbar-section logo-section">
                <a href="/" class="logo-link">
                    <img class="navbar-logo" src="/storage/main/logo.png" width="64px" height="64px" alt="Logo">
                </a>
            </div>

            <!-- Middle section - Navigation -->
            <div class="navbar-section nav-section">
                <div class="navbar-links">
                    <div class="navbar-suboption">
                        <a href="/">Home</a>
                    </div>
                    <div class="navbar-suboption position-relative">
                        <span class="cursor-pointer" @click="toggleFestivalMenu">
                          Festival ⩒
                        </span>
                        <ul v-if="isFestivalMenuVisible" class="festival-dropdown">
                            <li v-for="festival in festivals" :key="festival.id">
                                <a :href="`/festivals/${parseToUrl(festival.name)}`">
                                    {{ festival.name }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right section - User/Auth and icons -->
            <div class="navbar-section user-section">
                <div class="navbar-suboption">
                    <template v-if="user">
                        <div class="auth-container">
                            <span @click="navigateToDashboard"
                                  class="cursor-pointer user-name">
                                {{ user.firstName }}
                            </span>
                            <button type="button" class="btn btn-outline-danger logoutBtn" @click="logout">
                                Log Out
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <button @click="() => router.visit('/login')" class="login-button">
                            <i class="bx bx-user"></i>
                            <span>Login</span>
                        </button>
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

    <!-- User Dropdown Menu -->
    <div class="dropdown-menu" aria-labelledby="userDropdown">
        <Link class="dropdown-item" href="/user/personal-program">My Program</Link>
        <!-- Existing menu items like profile, orders, etc. -->
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" @click.prevent="logout">Logout</a>
    </div>
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
    align-items: center;
    justify-content: space-between;
    height: 100%;
}

/* Three-section layout */
.navbar-section {
    display: flex;
    align-items: center;
    height: 100%;
}

.logo-section {
    width: 25%;
    justify-content: flex-start;
}

.nav-section {
    width: 50%;
    justify-content: center;
}

.navbar-links {
    display: flex;
    gap: 3rem;
    align-items: center;
}

.user-section {
    width: 25%;
    justify-content: flex-end;
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

/* New styles for auth container and login button */
.auth-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.login-button {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: white;
    color: #2565c7;
    padding: 8px 16px;
    border: 1.5px solid #2565c7;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.login-button:hover {
    background-color: #2565c7;
    color: white;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.login-button i {
    font-size: 16px;
}

/* Dropdown menu styles */
.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    left: auto;
    z-index: 1000;
    display: none;
    float: right;
    min-width: 160px;
    padding: 0.5rem 0;
    margin: 0;
    font-size: 14px;
    color: #333;
    text-align: left;
    list-style: none;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #333;
    text-align: inherit;
    white-space: nowrap;
    background: 0;
    border: 0;
    transition: background 0.2s;
}

.dropdown-item:hover {
    background: #f5f5f5;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid #e9ecef;
}
</style>
