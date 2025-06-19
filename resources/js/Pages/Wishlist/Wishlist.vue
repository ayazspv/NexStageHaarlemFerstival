<template>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Your Wishlist</h1>

        <!-- Display a message if the wishlist is empty -->
        <div v-if="wishlist.length === 0" class="alert alert-info text-center">
            Your wishlist is empty.
        </div>

        <!-- Display a table of wishlist items if available -->
        <div v-else class="mt-4">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Festival Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Price</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in wishlist" :key="`${item.festival_id}-${item.ticket_type}`" class="wishlist-row">
                        <td>
                            <div class="festival-info">
                                <div class="festival-icon" :class="getItemClass(item.ticket_type)">
                                    <i v-if="item.ticket_type === 'jazz_event'" class="fas fa-music"></i>
                                    <i v-else-if="item.ticket_type === 'day_pass'" class="fas fa-calendar-day"></i>
                                    <i v-else-if="item.ticket_type === 'full_pass'" class="fas fa-ticket-alt"></i>
                                    <i v-else class="fas fa-ticket-alt"></i>
                                </div>
                                <span class="festival-name">{{ item.artist_name || item.name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge" :class="getTypeClass(item.ticket_type)">
                                {{ getTypeName(item.ticket_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="price-tag">
                                <span v-if="item.ticket_type === 'day_pass'">€35.00</span>
                                <span v-else-if="item.ticket_type === 'full_pass'">€80.00</span>
                                <span v-else-if="item.price">€{{ typeof item.price === 'number' ? item.price.toFixed(2) : item.price }}</span>
                                <span v-else>—</span>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button @click="handleAddToCart(item)" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                </button>
                                <button @click="item.event_id ? removeFromWishlist(item.festival_id, item.event_id) : removeFromWishlist(item.festival_id)" 
                                        class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </button>
                            </div>
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

// Helper functions for styling
function getItemClass(type) {
    switch(type) {
        case 'jazz_event': return 'jazz';
        case 'day_pass': return 'day-pass';
        case 'full_pass': return 'full-pass';
        default: return 'standard';
    }
}

function getTypeClass(type) {
    switch(type) {
        case 'jazz_event': return 'bg-primary';
        case 'day_pass': return 'bg-warning text-dark';
        case 'full_pass': return 'bg-success';
        default: return 'bg-secondary';
    }
}

function getTypeName(type) {
    switch(type) {
        case 'jazz_event': return 'Jazz Event';
        case 'day_pass': return 'Day Pass';
        case 'full_pass': return 'Full Pass';
        default: return 'Standard';
    }
}
</script>

<style scoped>
.wishlist-actions button {
    padding: 10px 20px;
    font-size: 16px;
}

.festival-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.festival-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.festival-icon.jazz {
    background: linear-gradient(135deg, #2196f3 0%, #0d6efd 100%);
}

.festival-icon.day-pass {
    background: linear-gradient(135deg, #ff9800 0%, #fd7e14 100%);
}

.festival-icon.full-pass {
    background: linear-gradient(135deg, #4caf50 0%, #198754 100%);
}

.festival-icon.standard {
    background: linear-gradient(135deg, #607d8b 0%, #495057 100%);
}

.festival-name {
    font-weight: 500;
}

.price-tag {
    font-weight: 600;
    color: #198754;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.wishlist-row:hover {
    background-color: #f8f9fa;
}
</style>