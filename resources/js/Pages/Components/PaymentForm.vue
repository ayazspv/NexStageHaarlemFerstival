<template>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="text-center mb-4">Complete Your Purchase</h2>

                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div v-for="item in cartData.items" :key="item.festivalID" class="d-flex justify-content-between mb-2">
                            <span>{{ item.festivalName }} (x{{ item.festivalQuantity }})</span>
                            <span>€{{ (item.festivalCost * item.festivalQuantity).toFixed(2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total: €{{ cartData.totalAmount.toFixed(2) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="card">
                    <div class="card-header">
                        <h5>Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="processPayment">
                            <!-- Customer Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="customer_name" class="form-label">Full Name *</label>
                                    <input
                                        type="text"
                                        v-model="form.customer_name"
                                        class="form-control"
                                        id="customer_name"
                                        required
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_email" class="form-label">Email Address *</label>
                                    <input
                                        type="email"
                                        v-model="form.customer_email"
                                        class="form-control"
                                        id="customer_email"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Card Information -->
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number *</label>
                                <input
                                    type="text"
                                    v-model="form.card_number"
                                    @input="formatCardNumber"
                                    class="form-control"
                                    id="card_number"
                                    placeholder="1234 5678 9012 3456"
                                    maxlength="19"
                                    required
                                >
                                <small class="form-text text-muted">
                                    Test cards: 4242424242424242 (success), 4000000000000002 (decline)
                                </small>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="expiry_date" class="form-label">Expiry Date *</label>
                                    <input
                                        type="text"
                                        v-model="form.expiry_date"
                                        @input="formatExpiryDate"
                                        class="form-control"
                                        id="expiry_date"
                                        placeholder="MM/YY"
                                        maxlength="5"
                                        required
                                    >
                                </div>
                                <div class="col-md-4">
                                    <label for="cvv" class="form-label">CVV *</label>
                                    <input
                                        type="text"
                                        v-model="form.cvv"
                                        class="form-control"
                                        id="cvv"
                                        placeholder="123"
                                        maxlength="3"
                                        required
                                    >
                                </div>
                                <div class="col-md-4">
                                    <label for="cardholder_name" class="form-label">Cardholder Name *</label>
                                    <input
                                        type="text"
                                        v-model="form.cardholder_name"
                                        class="form-control"
                                        id="cardholder_name"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-success btn-lg"
                                    :disabled="processing"
                                >
                                    <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ processing ? 'Processing...' : `Pay €${cartData.totalAmount.toFixed(2)}` }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="fas fa-lock me-1"></i>
                        This is a simulation. No real payment will be processed.
                    </small>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const cartData = page.props.cartData;

const processing = ref(false);

const form = ref({
    customer_name: '',
    customer_email: '',
    card_number: '',
    expiry_date: '',
    cvv: '',
    cardholder_name: '',
});

const formatCardNumber = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let value = target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    form.value.card_number = formattedValue;
};

const formatExpiryDate = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let value = target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    form.value.expiry_date = value;
};

const processPayment = () => {
    processing.value = true;

    const paymentData = {
        ...form.value,
        totalAmount: cartData.totalAmount,
        items: cartData.items,
    };

    router.post('/api/process-payment', paymentData, {
        onFinish: () => {
            processing.value = false;
        },
        onError: (errors) => {
            console.error('Payment errors:', errors);
            processing.value = false;
        }
    });
};
</script>

<style scoped>
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
