<template>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Your Wishlist</h1>

        <!-- Display a message if the wishlist is empty -->
        <div v-if="wishlist.length === 0" class="alert alert-info text-center">
            Your wishlist is empty.
        </div>

        <!-- Display a table of wishlist items if available -->
        <div v-else class="mt-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Festival Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Price</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in wishlist" :key="`${item.festival_id}-${item.ticket_type}`">
                        <td>{{ item.artist_name || item.name }}</td>
                        <td>
                            <span v-if="item.ticket_type === 'jazz_event'" class="badge bg-primary">Jazz Event</span>
                            <span v-else-if="item.ticket_type === 'day_pass'" class="badge bg-warning text-dark">Day Pass</span>
                            <span v-else-if="item.ticket_type === 'full_pass'" class="badge bg-success">Full Pass</span>
                            <span v-else class="badge bg-secondary">Standard</span>
                        </td>
                        <td>
                            <span v-if="item.ticket_type === 'day_pass'">€35.00</span>
                            <span v-else-if="item.ticket_type === 'full_pass'">€80.00</span>
                            <span v-else-if="item.price">€{{ item.price.toFixed(2) }}</span>
                            <span v-else>Price unavailable</span>
                        </td>
                        <td>
                            <!-- Remove from Wishlist and Add to Cart buttons -->
                            <button @click="item.event_id ? removeFromWishlist(item.festival_id, item.event_id) : removeFromWishlist(item.festival_id)"
                                class="btn btn-danger btn-sm me-2">
                                Remove
                            </button>
                            <button @click="handleAddToCart(item)"
                                class="btn btn-primary btn-sm">
                                Add to Cart
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Add All to Cart and Clear Wishlist Buttons -->
            <div class="wishlist-actions mt-4 text-center">
                <button @click="handleAddAllToCart" class="btn btn-success me-3">
                    Add All to Cart
                </button>
                <button @click="clearWishlist" class="btn btn-danger">
                    Clear Wishlist
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { wishlist, fetchWishlistItems, removeFromWishlist, clearWishlist } from '../../composables/wishlist';
import { addToCart, addJazzEventToCart } from '../../composables/cart';

onMounted(() => {
    fetchWishlistItems();
});

function handleAddToCart(item) {
    if (item.ticket_type === 'jazz_event') {
        // For jazz events, pass all required parameters
        addJazzEventToCart(
            item.festival_id,
            item.event_id,
            item.artist_name,
            item.performance_day,
            item.performance_time,
            1
        );
        
        // Remove only this specific jazz event
        removeFromWishlist(item.festival_id, item.event_id);
    } else if (item.ticket_type === 'day_pass') {
        // For day passes
        addToCart(-1, item.name, 1, 'day_pass', item.details);
        removeFromWishlist(item.festival_id);
    } else if (item.ticket_type === 'full_pass') {
        // For full passes
        addToCart(-2, item.name, 1, 'full_pass');
        removeFromWishlist(item.festival_id);
    } else {
        // For regular events
        addToCart(item.festival_id, item.name, 1);
        
        // Remove only this specific festival
        removeFromWishlist(item.festival_id);
    }
}

function handleAddAllToCart() {
    // Make a copy of the wishlist to avoid modification during iteration
    const items = [...wishlist.value];
    items.forEach(item => {
        handleAddToCart(item);
    });
    // Individual items are removed in handleAddToCart, don't clear all
}
</script>

<style scoped>
.wishlist-actions button {
    padding: 10px 20px;
    font-size: 16px;
}
</style>