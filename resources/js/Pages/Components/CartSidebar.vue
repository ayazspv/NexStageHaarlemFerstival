<script setup lang="ts">
import { computed } from 'vue';
import { cart, updateCartItem, clearCart } from '../../composables/cart';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits(['close']);

const page = usePage();
const user = computed(() => page.props.auth?.user || null);

const totalCost = computed(() => {
    return cart.value.reduce((total, item) => total + (item.festival_cost * item.quantity), 0);
});

const goToCheckout = () => {
    if (!user.value) {
        // Close sidebar and redirect to login
        emit('close');
        router.visit('/login');
        return;
    }

    // Close sidebar and go to cart
    emit('close');
    router.visit('/cart');
};

const goToLogin = () => {
    emit('close');
    router.visit('/login');
};
</script>

<template>
    <div class="sidebar-overlay" v-show="isOpen" @click="emit('close')"></div>

    <aside class="sidebar cart-sidebar" :class="{ open: isOpen }">
        <div class="sidebar-header">
            <h3>Shopping Cart</h3>
            <button class="close-button" @click="emit('close')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="sidebar-content">
            <div v-if="cart.length === 0" class="empty-message">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <p>Your cart is empty</p>
            </div>

            <div v-else>
                <div v-for="item in cart" :key="item.festival_id" class="cart-item">
                    <div class="item-details">
                        <h4>{{ item.festival_name }}</h4>
                        <p class="item-price">€{{ item.festival_cost }} × {{ item.quantity }}</p>
                    </div>

                    <div class="item-controls">
                        <button class="quantity-button" @click="updateCartItem(item.festival_id, item.quantity - 1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity">{{ item.quantity }}</span>
                        <button class="quantity-button" @click="updateCartItem(item.festival_id, item.quantity + 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Total:</span>
                        <span>€{{ totalCost.toFixed(2) }}</span>
                    </div>

                    <!-- User Authentication Status -->
                    <div v-if="user" class="user-status">
                        <p class="logged-in-text">
                            <i class="fas fa-user-check me-1"></i>
                            {{ user.firstName }} {{ user.lastName }}
                        </p>
                    </div>
                    <div v-else class="auth-required">
                        <p class="login-required-text">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Login required for checkout
                        </p>
                    </div>

                    <div class="cart-actions">
                        <button class="btn-clear" @click="clearCart()">
                            Clear Cart
                        </button>

                        <!-- Conditional Checkout Button -->
                        <button v-if="user" class="btn-checkout" @click="goToCheckout()">
                            Checkout
                        </button>
                        <button v-else class="btn-login" @click="goToLogin()">
                            Login to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    backdrop-filter: blur(2px);
}

.sidebar {
    position: fixed;
    top: 0;
    height: 100vh;
    width: 350px;
    background-color: white;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1001;
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
}

.cart-sidebar {
    right: 0;
    transform: translateX(100%);
}

.sidebar.open {
    transform: translateX(0);
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #eee;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #2565c7;
}

.close-button {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #666;
}

.close-button:hover {
    color: #333;
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}

.empty-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #666;
    text-align: center;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.item-details h4 {
    margin: 0 0 0.5rem;
    font-size: 1rem;
}

.item-price {
    color: #666;
    margin: 0;
}

.item-controls {
    display: flex;
    align-items: center;
}

.quantity-button {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid #ddd;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.quantity-button:hover {
    background: #f5f5f5;
}

.quantity {
    margin: 0 0.5rem;
    width: 20px;
    text-align: center;
}

.cart-summary {
    margin-top: 2rem;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.user-status {
    margin-bottom: 1rem;
}

.logged-in-text {
    color: #28a745;
    font-size: 0.9rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.auth-required {
    margin-bottom: 1rem;
}

.login-required-text {
    color: #ffc107;
    font-size: 0.9rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.cart-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-clear, .btn-checkout, .btn-login {
    flex: 1;
    padding: 0.75rem;
    border-radius: 4px;
    border: none;
    text-align: center;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
}

.btn-clear {
    background-color: #f8f9fa;
    color: #dc3545;
    border: 1px solid #dc3545;
}

.btn-checkout {
    background-color: #2565c7;
    color: white;
}

.btn-login {
    background-color: #28a745;
    color: white;
}

.btn-clear:hover {
    background-color: #f8d7da;
}

.btn-checkout:hover {
    background-color: #1a5bb8;
}

.btn-login:hover {
    background-color: #218838;
}
</style>
