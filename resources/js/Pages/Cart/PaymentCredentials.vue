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

                            <!-- Stripe Card Element -->
                            <div class="mb-3">
                                <label class="form-label">Card Information *</label>
                                <div id="card-element" class="form-control" style="height: auto; padding: 12px;">
                                    <!-- Stripe Card Element will be mounted here -->
                                </div>
                                <div id="card-errors" role="alert" class="text-danger mt-2"></div>
                                <small class="form-text text-muted">
                                    Test card: 4242 4242 4242 4242, any future expiry date, any 3-digit CVC
                                </small>
                            </div>

                            <!-- Error Display -->
                            <div v-if="error" class="alert alert-danger">
                                {{ error }}
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-success btn-lg"
                                    :disabled="processing || !cardComplete"
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
                        Secure payment powered by Stripe. Test mode enabled.
                    </small>
                </div>

                <!-- Back to Cart -->
                <div class="text-center mt-3">
                    <a href="/cart" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { prepareCheckoutData } from '../../composables/cart';
import { loadStripe } from '@stripe/stripe-js';

const page = usePage();
const processing = ref(false);
const error = ref('');
const cardComplete = ref(false);
const stripe = ref(null);
const cardElement = ref(null);
const paymentIntentId = ref('');

// Get cart data from props or prepare it from localStorage
const cartData = ref(page.props.cartData || prepareCheckoutData());

const form = ref({
    customer_name: '',
    customer_email: '',
});

onMounted(async () => {
    // If no cart data from server, get it from localStorage
    if (!page.props.cartData) {
        cartData.value = prepareCheckoutData();
    }

    // Redirect to cart if no items
    if (!cartData.value.items || cartData.value.items.length === 0) {
        router.visit('/cart');
        return;
    }

    // Initialize Stripe
    await initializeStripe();

    // Create payment intent
    await createPaymentIntent();
});

const initializeStripe = async () => {
    try {
        // Get the Stripe public key from your Laravel config
        const stripeKey = document.querySelector('meta[name="stripe-key"]')?.getAttribute('content') || 'pk_test_your_key_here';

        stripe.value = await loadStripe(stripeKey);

        const elements = stripe.value.elements();
        cardElement.value = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
                invalid: {
                    color: '#9e2146',
                },
            },
        });

        cardElement.value.mount('#card-element');

        cardElement.value.on('change', (event) => {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
                cardComplete.value = false;
            } else {
                displayError.textContent = '';
                cardComplete.value = event.complete;
            }
        });
    } catch (err) {
        console.error('Error initializing Stripe:', err);
        error.value = 'Failed to initialize payment system.';
    }
};

const createPaymentIntent = async () => {
    try {
        console.log('Creating payment intent for amount:', cartData.value.totalAmount);

        const response = await fetch('/api/create-payment-intent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token as string,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                totalAmount: cartData.value.totalAmount,
                items: cartData.value.items,
            }),
        });

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        paymentIntentId.value = data.client_secret;
        console.log('Payment intent created successfully');

    } catch (err) {
        console.error('Error creating payment intent:', err);
        error.value = `Failed to initialize payment: ${err.message}`;
    }
};

const processPayment = async () => {
    if (!stripe.value || !cardElement.value) {
        error.value = 'Payment system not initialized.';
        return;
    }

    if (!paymentIntentId.value) {
        error.value = 'Payment not properly initialized. Please refresh and try again.';
        return;
    }

    error.value = '';
    processing.value = true;

    try {
        console.log('Confirming payment with client secret:', paymentIntentId.value);

        // Confirm the payment with Stripe using the client secret
        const { error: stripeError, paymentIntent } = await stripe.value.confirmCardPayment(
            paymentIntentId.value, // This should be the client secret
            {
                payment_method: {
                    card: cardElement.value,
                    billing_details: {
                        name: form.value.customer_name,
                        email: form.value.customer_email,
                    },
                },
            }
        );

        if (stripeError) {
            console.error('Stripe error:', stripeError);
            error.value = stripeError.message;
            processing.value = false;
            return;
        }

        console.log('Payment confirmed successfully:', paymentIntent);

        // Payment succeeded, now process the order on our server
        const response = await fetch('/api/process-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token as string,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                customer_name: form.value.customer_name,
                customer_email: form.value.customer_email,
                payment_intent_id: paymentIntent.id,
                totalAmount: cartData.value.totalAmount,
                items: cartData.value.items,
            }),
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = result.redirect_url;
        } else {
            error.value = result.message || 'Payment processing failed';
        }
    } catch (err) {
        console.error('Payment processing error:', err);
        error.value = `An error occurred during payment processing: ${err.message}`;
    } finally {
        processing.value = false;
    }
};
</script>

<style scoped>
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
