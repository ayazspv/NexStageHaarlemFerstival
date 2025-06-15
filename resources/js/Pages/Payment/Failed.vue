<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card border-danger">
          <div class="card-header bg-danger text-white text-center">
            <h3><i class="fas fa-times-circle me-2"></i>Payment Failed</h3>
          </div>
          <div class="card-body">
            <div class="alert alert-danger">
              <strong>Payment could not be processed:</strong><br>
              {{ message }}
            </div>

            <div v-if="error_code" class="alert alert-warning">
              <strong>Error Code:</strong> {{ error_code }}
            </div>

            <div class="card">
              <div class="card-body">
                <h6>What to do next:</h6>
                <ul>
                  <li>Check your card details and try again</li>
                  <li>Try a different payment method</li>
                  <li>Contact your bank if the problem persists</li>
                  <li>Contact our support team for assistance</li>
                </ul>
              </div>
            </div>

            <div class="text-center mt-4">
              <a href="/cart" class="btn btn-primary me-2">
                <i class="fas fa-arrow-left me-1"></i>Back to Cart
              </a>
              <a href="/paymentCredentials" class="btn btn-outline-danger">
                <i class="fas fa-credit-card me-1"></i>Try Again
              </a>
            </div>

            <div class="mt-4 text-center">
              <small class="text-muted">
                Need help? Contact support at support@festivals.com
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const message = page.props.message;
const error_code = page.props.error_code;
</script>

<style scoped>
.card-header {
  border-bottom: 2px solid #dc3545;
}
</style>
