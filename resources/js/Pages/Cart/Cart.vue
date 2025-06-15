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
    <AppLayout title="Cart">
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
                        <td>{{ item.festival_id }}</td>
                        <td>{{ item.festival_name }}</td>
                        <td>€{{ item.festival_cost }}</td>
                        <td>{{ item.quantity }}</td>
                        <td>
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

                <!-- Authentication Check for Checkout -->
                <div class="text-center">
                    <div v-if="user" class="mb-3">
                        <p class="text-success mb-2">
                            <i class="fas fa-user-check me-2"></i>
                            Logged in as {{ user.firstName }} {{ user.lastName }}
                        </p>
                        <button class="btn btn-success btn-lg" @click="goToNextPage()">
                            Proceed to Checkout
                        </button>
                    </div>

                    <div v-else class="mb-3">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Login Required</h5>
                            <p class="mb-3">You need to be logged in to proceed with checkout.</p>
                            <button class="btn btn-primary me-2" @click="goToLogin()">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </button>
                            <a href="/signup" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-1"></i>Create Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.alert {
    border-radius: 8px;
}

.btn {
    border-radius: 6px;
}

.text-success {
    font-weight: 500;
}
</style>
