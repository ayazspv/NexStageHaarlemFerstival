<template>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Your Cart</h1>

        <!-- Display a message if the cart is empty -->
        <div v-if="cart.length === 0" class="alert alert-info text-center">
            Your cart is empty.
        </div>

        <!-- Display a table of cart items if available -->
        <div v-else class="mt-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Festival ID</th>
                        <th scope="col">Festival Name</th>
                        <th scope="col">Cost</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in cart" :key="item.festival_id">
                        <!-- Log the item for debugging -->
                        <td>{{ item.festival_id }}</td>
                        <td>{{ item.festival_name }}</td>
                        <td>€{{ item.festival_cost }}</td>
                        <td>{{ item.quantity }}</td>
                        <td>
                            <!-- Decrease and Increase buttons -->
                            <button @click="updateItemQuantity(item.festival_id, item.quantity - 1)"
                                class="btn btn-danger btn-sm me-2" >
                                Decrease
                            </button>
                            <button @click="updateItemQuantity(item.festival_id, item.quantity + 1)"
                                class="btn btn-primary btn-sm">
                                Increase
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="d-flex justify mb-3">
                <strong>Total Items: </strong> <span> {{ cart.length }}</span>
            </div>
            <div class="d-flex justify mb-3">
                <strong>Total Quantity: </strong> <span> {{ totalQuantity }}</span>
            </div>
            <div class="d-flex justify mb-3">
                <strong>Total To Pay: </strong> <span> €{{ totalCost }}</span>
            </div>
            <div class="text-center">
                <button class="btn btn-success btn-lg" @click="">Proceed to Checkout</button>
            </div>
        </div>
    </div>




</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { cart, fetchCartItems, updateCartItem } from '../../composables/cart';
import { removeFromWishlist } from '../../composables/wishlist';

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

// const items = cart.value.map(item => ({
//         festivalID: item.festival_id,
//         festivalName: item.festival_name,
//         festivalQuantity: item.quantity,
//         festivalCost: item.festival_cost,
//     }));

const prepareDataForNextLayer = () => {
    const items = cart.value.map(item => ({
        festivalID: item.festival_id,
        festivalName: item.festival_name,
        festivalQuantity: item.quantity,
        festivalCost: item.festival_cost,
    }));

    return {
        totalAmount: totalCost.value, 
        items,
    };
};

const passToLayer2 = (data: any) => {
    console.log("Data passed to Layer 2:", data);
};
</script>



<style scoped>
/* Optional custom styles if needed */
</style>