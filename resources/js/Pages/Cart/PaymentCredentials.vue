<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import { prepareCheckoutData } from '../../composables/cart';
import AppLayout from "../Layouts/AppLayout.vue";

const page = usePage();
const processing = ref(false);
const initializing = ref(true);
const error = ref('');
const cardComplete = ref(false);
const stripe = ref(null);
const cardElement = ref(null);
const paymentIntentId = ref('');

// Cart data and server-verified data
const cartData = ref(prepareCheckoutData());
const serverVerifiedData = ref({
    calculated_total: 0,
    calculated_items: [],
});

const form = ref({
    customer_name: '',
    customer_email: '',
});

// Get user info if available
const user = computed(() => page.props.auth?.user);

onMounted(async () => {
    try {
        // Get cart data from localStorage
        cartData.value = prepareCheckoutData();

        // Redirect to cart if no items
        if (!cartData.value.items || cartData.value.items.length === 0) {
            router.visit('/cart');
            return;
        }

        // Pre-fill form with user data if available
        if (user.value) {
            form.value.customer_name = `${user.value.firstName || ''} ${user.value.lastName || ''}`.trim();
            form.value.customer_email = user.value.email || '';
        }

        // Wait for DOM to be ready, then initialize Stripe
        await new Promise(resolve => setTimeout(resolve, 100));
        await initializeStripe();

        // Create payment intent with server-side validation
        await createPaymentIntent();

    } catch (err) {
        console.error('Initialization error:', err);
        error.value = 'Failed to initialize payment system. Please refresh the page.';
    } finally {
        initializing.value = false;
    }
});

const initializeStripe = async () => {
    try {
        // Wait for the DOM element to be available
        let attempts = 0;
        while (!document.getElementById('card-element') && attempts < 10) {
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }

        if (!document.getElementById('card-element')) {
            throw new Error('Card element not found in DOM');
        }

        const stripeKey = document.querySelector('meta[name="stripe-key"]')?.getAttribute('content') || 'pk_test_51RZwCtIbeSxsd8TjkREnTSK2Hqv2FWjpw5B7SCJeO8CRrtRFCIUg6I8jPVxLVEyAiAWLyTb0N0Utqyq3kqRqSIZs00Dx8g8heY';

        stripe.value = await loadStripe(stripeKey);

        if (!stripe.value) {
            throw new Error('Failed to load Stripe');
        }

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

        console.log('Stripe initialized successfully');
    } catch (err) {
        console.error('Error initializing Stripe:', err);
        throw new Error('Failed to initialize payment system');
    }
};

const createPaymentIntent = async () => {
    try {
        console.log('Creating secure payment intent...');
        console.log('Cart data:', cartData.value);

        // Send cart items to server for price validation and calculation
        const cartItemsForValidation = cartData.value.items.map(item => ({
            festival_id: item.festival_id || item.festivalID, // Support both formats
            quantity: item.quantity || item.festivalQuantity,
        }));

        console.log('Sending items for server validation:', cartItemsForValidation);

        const response = await fetch('/api/create-payment-intent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token as string,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                items: cartItemsForValidation,
            }),
        });

        console.log('Payment intent response status:', response.status);

        if (!response.ok) {
            const errorData = await response.json();
            console.error('Payment intent request failed:', errorData);
            throw new Error(errorData.error || `HTTP ${response.status}`);
        }

        const data = await response.json();
        console.log('Payment intent response data:', data);

        if (data.error) {
            throw new Error(data.error);
        }

        if (!data.client_secret) {
            throw new Error('No client secret received from server');
        }

        // Store server-verified data
        paymentIntentId.value = data.client_secret;
        serverVerifiedData.value = {
            calculated_total: data.calculated_total,
            calculated_items: data.calculated_items,
        };

        console.log('Secure payment intent created successfully');
        console.log('Server calculated total:', data.calculated_total);

    } catch (err) {
        console.error('Error creating payment intent:', err);
        throw new Error(`Failed to initialize payment: ${err.message}`);
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

    if (!form.value.customer_name.trim() || !form.value.customer_email.trim()) {
        error.value = 'Please fill in all required fields.';
        return;
    }

    error.value = '';
    processing.value = true;

    try {
        console.log('Confirming payment with client secret:', paymentIntentId.value);

        // Confirm the payment with Stripe
        const { error: stripeError, paymentIntent } = await stripe.value.confirmCardPayment(
            paymentIntentId.value,
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
            return;
        }

        console.log('Payment confirmed successfully:', paymentIntent);

        // Payment succeeded, process the order on our server
        // Only send customer info and payment intent ID - server has all the validation data
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
            }),
        });

        const result = await response.json();

        if (result.success) {
            // Clear cart since payment was successful
            localStorage.removeItem('cart');
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

const retryInitialization = async () => {
    initializing.value = true;
    error.value = '';
    paymentIntentId.value = '';
    serverVerifiedData.value = { calculated_total: 0, calculated_items: [] };

    try {
        await createPaymentIntent();
    } catch (err) {
        error.value = err.message;
    } finally {
        initializing.value = false;
    }
};
</script>

<template>
    <AppLayout title="Payment Credentials">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="/cart">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    Shopping Cart
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <i class="fas fa-credit-card me-1"></i>
                                Payment
                            </li>
                        </ol>
                    </nav>

                    <h2 class="text-center mb-4">
                        <i class="fas fa-lock me-2 text-success"></i>
                        Secure Payment
                    </h2>

                    <!-- Loading State -->
                    <div v-if="initializing" class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Verifying prices and initializing secure payment...</p>
                        <small class="text-muted">Please wait while we validate your cart</small>
                    </div>

                    <!-- Initialization Error -->
                    <div v-else-if="error && !paymentIntentId" class="alert alert-danger">
                        <h5>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Payment System Error
                        </h5>
                        <p>{{ error }}</p>
                        <button @click="retryInitialization" class="btn btn-outline-danger">
                            <span v-if="initializing" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="fas fa-redo me-2"></i>
                            Try Again
                        </button>
                    </div>

                    <!-- Payment Form -->
                    <div v-show="!initializing">
                        <!-- Order Summary (Server-Verified) -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Order Summary
                                </h5>
                                <span class="badge bg-success">
                  <i class="fas fa-shield-alt me-1"></i>
                  Server Verified
                </span>
                            </div>
                            <div class="card-body">
                                <div v-if="serverVerifiedData.calculated_items.length > 0">
                                    <div v-for="item in serverVerifiedData.calculated_items"
                                         :key="item.festival_id"
                                         class="d-flex justify-content-between mb-2">
                    <span>
                      {{ item.festival_name }}
                      <span class="text-muted">(x{{ item.quantity }})</span>
                    </span>
                                        <span>€{{ item.item_total.toFixed(2) }}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total: €{{ serverVerifiedData.calculated_total.toFixed(2) }}</strong>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        All prices verified against our database for security
                                    </small>
                                </div>
                                <div v-else class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    Calculating prices...
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Payment Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="processPayment">
                                    <!-- Customer Information -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="customer_name" class="form-label">
                                                <i class="fas fa-user me-1"></i>
                                                Full Name *
                                            </label>
                                            <input
                                                type="text"
                                                v-model="form.customer_name"
                                                class="form-control"
                                                id="customer_name"
                                                required
                                                placeholder="Enter your full name"
                                            >
                                        </div>
                                        <div class="col-md-6">
                                            <label for="customer_email" class="form-label">
                                                <i class="fas fa-envelope me-1"></i>
                                                Email Address *
                                            </label>
                                            <input
                                                type="email"
                                                v-model="form.customer_email"
                                                class="form-control"
                                                id="customer_email"
                                                required
                                                placeholder="Enter your email address"
                                            >
                                        </div>
                                    </div>

                                    <!-- Stripe Card Element -->
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-credit-card me-1"></i>
                                            Card Information *
                                        </label>
                                        <div id="card-element" class="form-control" style="height: auto; padding: 12px;">
                                            <!-- Stripe Card Element will be mounted here -->
                                        </div>
                                        <div id="card-errors" role="alert" class="text-danger mt-2"></div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Test card:</strong> 4242 4242 4242 4242, any future expiry date, any 3-digit CVC
                                        </small>
                                    </div>

                                    <!-- Error Display -->
                                    <div v-if="error && paymentIntentId" class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ error }}
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2">
                                        <button
                                            type="submit"
                                            class="btn btn-success btn-lg"
                                            :disabled="processing || !cardComplete || !paymentIntentId || !form.customer_name.trim() || !form.customer_email.trim()"
                                        >
                                            <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                                            <i v-else class="fas fa-lock me-2"></i>
                                            {{ processing ? 'Processing Payment...' : `Pay €${serverVerifiedData.calculated_total.toFixed(2)}` }}
                                        </button>
                                    </div>

                                    <!-- Processing Notice -->
                                    <div v-if="processing" class="alert alert-info mt-3">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            <div>
                                                <strong>Processing your payment...</strong><br>
                                                <small>Please do not close this window or press the back button.</small>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-info mt-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-shield-alt fa-2x text-primary"></i>
                                </div>
                                <div class="col">
                                    <h6 class="mb-1">Secure Payment</h6>
                                    <small class="mb-0">
                                        Your payment is secured by Stripe with 256-bit SSL encryption.
                                        All prices are verified server-side for your protection.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Back to Cart -->
                        <div class="text-center mt-3">
                            <a href="/cart" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.card-element-container {
    height: auto;
    padding: 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.card-element-container:focus-within {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.alert {
    border-radius: 0.5rem;
    border: none;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.btn-outline-secondary {
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    font-size: 1rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.visually-hidden {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}

.text-muted {
    color: #6c757d !important;
}

.breadcrumb {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
}

.breadcrumb-item a {
    text-decoration: none;
    color: #007bff;
}

.breadcrumb-item.active {
    color: #6c757d;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }

    .card-header {
        padding: 1rem;
    }

    .card-body {
        padding: 1rem;
    }
}

/* Loading animation for spinner */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.spinner-border {
    animation: spin 0.75s linear infinite;
}
</style>
