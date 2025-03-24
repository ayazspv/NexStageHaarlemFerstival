<script setup lang="ts">
import AppLayout from "../Pages/Layouts/AppLayout.vue";
import { cart, fetchCartItems, addToCart, updateCartItem } from '../composables/cart';
import { wishlist, fetchWishlistItems, addToWishlist, removeFromWishlist } from '../composables/wishlist';

defineProps({
    festivals: {
        type: Array,
        default: () => []
    }
});

const parseToUrl = (title: string) => {
    return title.trim().toLowerCase().replace(/\s+/g, '-');
};

fetchCartItems(); 
fetchWishlistItems();
</script>

<template>
    <AppLayout title="Home">
        <div class="container-fluid">
            <!-- Schedule Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Schedule</h2>
                <div class="schedule-container p-3 border rounded">
                    <!-- Placeholder for the schedule -->
                    <div class="schedule-grid">
                        <!-- This will be replaced with actual schedule implementation -->
                        <div class="time-slot">Schedule placeholder</div>
                    </div>
                </div>
            </section>

            <!-- Festivals Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Festivals</h2>
                <div class="row g-4">
                    <div v-for="festival in festivals"
                         :key="festival.id"
                         class="col-12 col-md-6">
                        <div class="festival-wrapper">
                            <a :href="`/festivals/${parseToUrl(festival.name)}`">
                                <div class="card festival-card mb-3">
                                    <img :src="`/storage/${festival.image_path}`"
                                         :alt="festival.name"
                                         class="card-img-top">
                                </div>
                            </a>
                            <h5 class="text-center mb-3">{{ festival.name }}</h5>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-primary" @click.prevent="addToCart(festival.id)">
                                    <i class="fas fa-ticket-alt me-2"></i>Buy Ticket
                                </button>
                                <button class="btn btn-outline-primary" @click.prevent="addToWishlist(festival.id)">
                                    <i class="fas fa-heart"></i> Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Cart Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Cart</h2>
                <div v-if="cart.length" class="cart-container p-3 border rounded">
                    <div v-for="item in cart" :key="item.festival_id" class="cart-item d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5>{{ item.festival.name }}</h5>
                            <p>Tickets: {{ item.quantity }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary" @click="updateCartItem(item.festival_id, item.quantity - 1)">-</button>
                            <button class="btn btn-outline-secondary" @click="updateCartItem(item.festival_id, item.quantity + 1)">+</button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center">
                    <p>Your cart is empty.</p>
                </div>
            </section>

            <!-- Wishlist Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Wishlist</h2>
                <div v-if="wishlist.length" class="wishlist-container p-3 border rounded">
                    <div v-for="item in wishlist" :key="item.festival_id" class="wishlist-item d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5>{{ item.festival.name }}</h5>
                        </div>
                        <button class="btn btn-outline-danger" @click="removeFromWishlist(item.festival_id)">Remove</button>
                    </div>
                </div>
                <div v-else class="text-center">
                    <p>Your wishlist is empty.</p>
                </div>
            </section>

            <!-- Locations(Map) Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Locations</h2>
                <div class="map-container">
                    <!-- Placeholder for the map -->
                    <div class="map-placeholder border rounded">
                        Map will be implemented here
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.festival-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.festival-card {
    transition: transform 0.3s ease;
    width: 100%;
}

.festival-card:hover {
    transform: translateY(-5px);
}

.schedule-container {
    min-height: 200px;
    background: #f8f9fa;
}

.map-container {
    min-height: 400px;
}

.map-placeholder {
    width: 100%;
    height: 400px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-container {
    background: #f8f9fa;
}

.cart-item {
    background: #fff;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}

.wishlist-container {
    background: #f8f9fa;
}

.wishlist-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}
</style>
