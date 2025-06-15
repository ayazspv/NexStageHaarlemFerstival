<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { cart, fetchCartItems, updateCartItem } from '../../composables/cart';
import { Inertia } from '@inertiajs/inertia';
import { useForm, router, usePage } from "@inertiajs/vue3";
import AppLayout from "../Layouts/AppLayout.vue";

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

const page = usePage();

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



</script>

<template>
    <AppLayout title="Cart">
        <div class="container mt-5">
            <h1 class="text-center mb-4">Your Cart</h1>

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
                    <button class="btn btn-success btn-lg" @click="goToNextPage()">Proceed to Checkout</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


<style scoped>
</style>
