<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { cart, fetchCartItems, updateCartItem } from '../../composables/cart';
import { Inertia } from '@inertiajs/inertia';
import { useForm, router, usePage } from "@inertiajs/vue3";
import AppLayout from "../Layouts/AppLayout.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user || null);

onMounted(() => {
    fetchCartItems();
    console.log(cart.value);

    const dataToSend = prepareDataForNextLayer();
    passToLayer2(dataToSend);
});

const updateItemQuantity = (festivalId: number, newQuantity: number) => {
    updateCartItem(festivalId, newQuantity);
};

const totalQuantity = computed(() => {
    return cart.value.reduce((total, item) => total + item.quantity, 0);
});

const totalCost = computed(() => {
    return cart.value.reduce((total, item) => total + (item.festival_cost * item.quantity), 0);
});

const prepareDataForNextLayer = () => {
    const items = cart.value.map(item => ({
        festivalID: item.festival_id,
        festivalName: item.festival_name,
        festivalQuantity: item.quantity,
        festivalCost: item.festival_cost,
        qrCode: `${item.festival_id}-${Math.floor(100000 + Math.random() * 900000)}`,
    }));

    return {
        totalAmount: totalCost.value,
        items,
    };
};

const passToLayer2 = (data: any) => {
    console.log("Data passed to Layer 2:", data);
};

const csrfToken = page.props.csrf_token as string || "";

const dataToSend = useForm(prepareDataForNextLayer());

function passToNextLayer() {
    Inertia.visit
    dataToSend.get("/paymentCredentials", {
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        }
    });
}

function goToNextPage() {
    // Check if user is logged in
    if (!user.value) {
        // Redirect to login page
        router.visit('/login');
        return;
    }

    const data = prepareDataForNextLayer();

    if (Object.keys(data).length === 0) {
        console.error('No valid data to send');
        return;
    }

    router.post('/paymentCredentials', data, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        }
    });
}

function goToLogin() {
    router.visit('/login');
}
</script>

<template>
    <AppLayout title="Shopping Cart">
        <div class="h-100 m-0 mb-5">
            <section class="hero-section" :style="`background: url('/storage/festivals/haarlem-panorama.webp') center/cover no-repeat`">
                <div class="overlay"></div>
                <div class="hero-content w-100 d-flex flex-column align-items-center gap-1">
                    <div class="d-flex h-40 w-100 flex-row justify-content-center align-items-center" style="background-color: rgba(0, 0, 0, 0.5);">
                        <h1 class="hero-title-haarlem text-white">
                            Buy Tickets
                        </h1>
                    </div>
                </div>
            </section>
            <div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <!-- Cart Header -->
                        <div class="cart-header">
                            <h1 class="cart-title">
                                <i class="fas fa-shopping-cart me-3"></i>
                                Shopping Cart
                            </h1>
                            <div v-if="cart.length > 0" class="cart-badge">
                                {{ totalQuantity }} item{{ totalQuantity !== 1 ? 's' : '' }}
                            </div>
                        </div>

                        <!-- Empty Cart State -->
                        <div v-if="cart.length === 0" class="empty-cart">
                            <div class="empty-cart-content">
                                <i class="fas fa-shopping-cart empty-icon"></i>
                                <h3>Your cart is empty</h3>
                                <p>Start adding some festival tickets to get started!</p>
                                <a href="/" class="btn btn-primary btn-lg">
                                    <i class="fas fa-music me-2"></i>
                                    Browse Festivals
                                </a>
                            </div>
                        </div>

                        <!-- Cart Content -->
                        <div v-else class="cart-content">
                            <div class="row">
                                <!-- Cart Items -->
                                <div class="col-lg-8">
                                    <div class="cart-items-card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Festival Tickets</h5>
                                        </div>
                                        <div class="cart-items-list">
                                            <div v-for="item in cart" :key="item.festival_id" class="cart-item">
                                                <div class="item-info">
                                                    <div class="festival-icon">
                                                        <i class="fas fa-ticket-alt"></i>
                                                    </div>
                                                    <div class="festival-details">
                                                        <h6 class="festival-name">{{ item.festival_name }}</h6>
                                                        <div class="festival-meta">
                                                            <span class="festival-price">€{{ item.festival_cost.toFixed(2) }} each</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="item-controls">
                                                    <div class="quantity-section">
                                                        <label class="quantity-label">Quantity</label>
                                                        <div class="quantity-controls">
                                                            <button
                                                                @click="updateItemQuantity(item.festival_id, item.quantity - 1)"
                                                                class="btn btn-outline-secondary btn-sm quantity-btn"
                                                                :disabled="item.quantity <= 1">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                            <span class="quantity-display">{{ item.quantity }}</span>
                                                            <button
                                                                @click="updateItemQuantity(item.festival_id, item.quantity + 1)"
                                                                class="btn btn-outline-secondary btn-sm quantity-btn">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="item-total">
                                                        <span class="total-label">Total</span>
                                                        <span class="total-amount">€{{ (item.festival_cost * item.quantity).toFixed(2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Summary -->
                                <div class="col-lg-4">
                                    <div class="order-summary">
                                        <h5 class="summary-title">Order Summary</h5>

                                        <div class="summary-content">
                                            <div class="summary-row">
                                                <span>Festival Types:</span>
                                                <span>{{ cart.length }}</span>
                                            </div>
                                            <div class="summary-row">
                                                <span>Total Tickets:</span>
                                                <span>{{ totalQuantity }}</span>
                                            </div>
                                            <div class="summary-row summary-total">
                                                <span>Total Amount:</span>
                                                <span class="total-price">€{{ totalCost.toFixed(2) }}</span>
                                            </div>
                                        </div>

                                        <!-- Checkout Section -->
                                        <div class="checkout-section">
                                            <div v-if="user" class="user-authenticated">
                                                <div class="user-info">
                                                    <i class="fas fa-user-check text-success me-2"></i>
                                                    <span>{{ user.firstName }} {{ user.lastName }}</span>
                                                </div>
                                                <button @click="goToNextPage" class="btn btn-success btn-lg w-100 checkout-btn">
                                                    <i class="fas fa-credit-card me-2"></i>
                                                    Proceed to Checkout
                                                </button>
                                            </div>

                                            <div v-else class="auth-required">
                                                <div class="auth-notice">
                                                    <i class="fas fa-lock me-2"></i>
                                                    <span>Please login to continue</span>
                                                </div>
                                                <div class="auth-actions">
                                                    <button @click="goToLogin" class="btn btn-primary w-100 mb-2">
                                                        <i class="fas fa-sign-in-alt me-2"></i>
                                                        Login
                                                    </button>
                                                    <a href="/signup" class="btn btn-outline-primary w-100">
                                                        <i class="fas fa-user-plus me-2"></i>
                                                        Create Account
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Cart Header */
.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #e9ecef;
}

.cart-title {
    color: #2c3e50;
    font-weight: 700;
    margin: 0;
    font-size: 2rem;
}

.cart-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1.2rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* Empty Cart */
.empty-cart {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.empty-cart-content {
    text-align: center;
    max-width: 400px;
    padding: 3rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.empty-icon {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.empty-cart-content h3 {
    color: #495057;
    margin-bottom: 1rem;
    font-weight: 600;
}

.empty-cart-content p {
    color: #6c757d;
    margin-bottom: 2rem;
}

/* Cart Items Card */
.cart-items-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.cart-items-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.cart-items-card .card-header h5 {
    color: #495057;
    font-weight: 600;
}

.cart-items-list {
    padding: 0;
}

/* Individual Cart Item */
.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.cart-item:hover {
    background: #f8f9fa;
    transform: translateY(-1px);
}

.cart-item:last-child {
    border-bottom: none;
}

.item-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.festival-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 1rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.festival-details {
    flex: 1;
}

.festival-name {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.festival-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
}

.festival-id {
    color: #6c757d;
}

.festival-price {
    color: #28a745;
    font-weight: 500;
}

/* Item Controls */
.item-controls {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.quantity-section {
    text-align: center;
}

.quantity-label {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #dee2e6;
}

.quantity-btn:hover:not(:disabled) {
    border-color: #007bff;
    transform: scale(1.1);
}

.quantity-display {
    font-weight: 600;
    font-size: 1.1rem;
    min-width: 30px;
    text-align: center;
}

.item-total {
    text-align: right;
}

.total-label {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.total-amount {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
}

/* Order Summary */
.order-summary {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    position: sticky;
    top: 20px;
}

.summary-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.summary-content {
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-total {
    border-top: 2px solid #dee2e6;
    margin-top: 1rem;
    padding-top: 1rem;
    font-weight: 600;
    font-size: 1.1rem;
}

.total-price {
    color: #28a745;
    font-size: 1.4rem;
    font-weight: 700;
}

/* Checkout Section */
.checkout-section {
    border-top: 2px solid #dee2e6;
    padding-top: 1.5rem;
}

.user-authenticated {
    text-align: center;
}

.user-info {
    background: #d4edda;
    padding: 0.75rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    color: #155724;
    font-weight: 500;
}

.checkout-btn {
    padding: 1rem 1.5rem;
    font-weight: 600;
    border-radius: 10px;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.checkout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.auth-required {
    text-align: center;
}

.auth-notice {
    background: #fff3cd;
    padding: 0.75rem;
    border-radius: 10px;
    color: #856404;
    margin-bottom: 1rem;
    font-weight: 500;
}

.auth-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.auth-actions .btn {
    padding: 0.75rem;
    font-weight: 500;
    border-radius: 8px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .cart-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .cart-item {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .item-info {
        flex-direction: column;
        text-align: center;
    }

    .item-controls {
        justify-content: center;
        gap: 1rem;
    }

    .festival-meta {
        justify-content: center;
    }
}

/* Animations */
.cart-item {
    animation: slideIn 0.4s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-section {
    margin: 0;
    padding: 0;
    position: relative;
    height: 600px;
    overflow: hidden;
    width: 100%;
}

.hero-section .overlay {
    position: absolute;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.6);
}

.hero-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.hero-title-haarlem,
.hero-title-festival {
    margin: 0;
    font-size: 72px;
    color: #fff;
    text-align: center;
    font-family: "Bayon", sans-serif;
    font-weight: 200;
    font-style: normal;
}

.hero-title-info {
    font-family: "Bayon", sans-serif;
    font-weight: 400;
    font-style: normal;
    font-size: 50px;
    color: #fff;
}

.hero-title-festival {
    /* inherits white color from above */
}

.hero-description-section {
    height: 400px;
    background-color: #F0F0F0;

    justify-content: center;
    align-items: center;
}

.hero-description {
    width: 50%;
    text-align: center;
}

.hero-description :deep(h1) {
    font-family: "Bayon", sans-serif;
    font-weight: 400;
    font-style: normal;
    font-size: 60px;
    color: #2A5CAE;
    margin-bottom: 20px;
}

.hero-description :deep(p) {
    margin: 10px;
    font-size: 16px;
    line-height: 1.5;
}
</style>
