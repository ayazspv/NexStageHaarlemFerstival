<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { clearCart } from '../../composables/cart';
import AppLayout from "../Layouts/AppLayout.vue";

const page = usePage();
const order = page.props.order;
const message = page.props.message;

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString();
};

onMounted(() => {
  clearCart();
});
</script>

<template>
  <AppLayout title="Payment Success">
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card border-success">
            <div class="card-header bg-success text-white text-center">
              <h3><i class="fas fa-check-circle me-2"></i>Payment Successful!</h3>
            </div>
            <div class="card-body">
              <div class="alert alert-success">
                {{ message }}
              </div>

              <h5>Order Details</h5>
              <p><strong>Order ID:</strong> #{{ order.id }}</p>
              <p><strong>Customer:</strong> {{ order.payment_details.customer_name }}</p>
              <p><strong>Email:</strong> {{ order.payment_details.customer_email }}</p>
              <p><strong>Transaction ID:</strong> {{ order.payment_details.transaction_id }}</p>
              <p><strong>Order Date:</strong> {{ formatDate(order.ordered_at) }}</p>

              <h5 class="mt-4">Your Tickets</h5>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                  <tr>
                    <th>Festival</th>
                    <th>Ticket Code</th>
                    <th>Price</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr v-for="ticket in order.tickets" :key="ticket.id">
                    <td>{{ ticket.festival.name }}</td>
                    <td><code>{{ ticket.qr_code }}</code></td>
                    <td>€{{ parseFloat(ticket.price_per_ticket).toFixed(2) }}</td>
                  </tr>
                  </tbody>
                </table>
              </div>

              <div class="alert alert-info mt-4">
                <h6><i class="fas fa-envelope me-2"></i>Email Confirmation</h6>
                <p class="mb-0">A confirmation email with your QR codes has been sent to {{ order.payment_details.customer_email }}. Please check your inbox and spam folder.</p>
              </div>

              <div class="text-center mt-4">
                <p class="text-muted">Total Paid: <strong>€{{ parseFloat(order.total_price).toFixed(2) }}</strong></p>
                <a href="/" class="btn btn-primary me-2">Back to Home</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.card-header {
  border-bottom: 2px solid #28a745;
}
</style>
