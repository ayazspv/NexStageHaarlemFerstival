<script setup lang="ts">
import { wishlist, removeFromWishlist, clearWishlist } from '../../composables/wishlist';
import { addToCart, addJazzEventToCart } from '../../composables/cart';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits(['close']);

function addItemToCart(item) {
  if (item.ticket_type === 'jazz_event') {
    // For jazz events, use proper method with all details
    addJazzEventToCart(
      item.festival_id,
      item.event_id,
      item.artist_name,
      item.performance_day,
      item.performance_time,
      1
    );
    
    // Remove this specific jazz event, using both festival_id and event_id
    removeFromWishlist(item.festival_id, item.event_id);
  } else {
    // For regular events
    addToCart(item.festival_id, item.name, 1);
    
    // Remove only this specific item
    removeFromWishlist(item.festival_id);
  }
}

function addAllToCart() {
  wishlist.value.forEach(item => {
    addItemToCart(item);
  });
  // Don't clear the wishlist here - each item is removed individually
}
</script>

<template>
  <div class="sidebar-overlay" v-show="isOpen" @click="emit('close')"></div>
  
  <aside class="sidebar wishlist-sidebar" :class="{ open: isOpen }">
    <div class="sidebar-header">
      <h3>Your Wishlist</h3>
      <button class="close-button" @click="emit('close')">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div class="sidebar-content">
      <div v-if="wishlist.length === 0" class="empty-message">
        <i class="fas fa-heart fa-3x mb-3"></i>
        <p>Your wishlist is empty</p>
      </div>
      
      <div v-else>
        <div v-for="item in wishlist" :key="item.festival_id" class="wishlist-item">
          <div class="item-details">
            <h4>{{ item.artist_name || item.name }}</h4>
          </div>
          
          <div class="item-actions">
            <button class="action-button add-cart" @click="addItemToCart(item)">
              <i class="fas fa-shopping-cart"></i>
            </button>
            <button class="action-button remove" @click="item.event_id ? removeFromWishlist(item.festival_id, item.event_id) : removeFromWishlist(item.festival_id)">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        
        <div class="wishlist-actions">
          <button class="btn-action add-all" @click="addAllToCart">
            Add All to Cart
          </button>
          <button class="btn-action clear-all" @click="clearWishlist">
            Clear Wishlist
          </button>
          <Link href="/wishlist" class="btn-action view-all">
            View Details
          </Link>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.sidebar {
  position: fixed;
  top: 0;
  height: 100vh;
  width: 350px;
  background-color: white;
  box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
  z-index: 1001;
  transition: transform 0.3s ease;
  display: flex;
  flex-direction: column;
}

.wishlist-sidebar {
  right: 0;
  transform: translateX(100%);
}

.sidebar.open {
  transform: translateX(0);
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
}

.sidebar-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #2565c7;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #666;
}

.close-button:hover {
  color: #333;
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.empty-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #666;
  text-align: center;
}

.wishlist-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #eee;
}

.item-details h4 {
  margin: 0;
  font-size: 1rem;
}

.item-actions {
  display: flex;
  gap: 0.5rem;
}

.action-button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: white;
}

.action-button.add-cart {
  background-color: #2565c7;
}

.action-button.remove {
  background-color: #dc3545;
}

.action-button:hover {
  opacity: 0.9;
}

.wishlist-actions {
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-action {
  padding: 0.75rem;
  border-radius: 4px;
  border: none;
  text-align: center;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
}

.btn-action.add-all {
  background-color: #2565c7;
  color: white;
}

.btn-action.clear-all {
  background-color: #f8f9fa;
  color: #dc3545;
  border: 1px solid #dc3545;
}

.btn-action.view-all {
  background-color: #f8f9fa;
  color: #2565c7;
  border: 1px solid #2565c7;
}

.btn-action:hover {
  opacity: 0.9;
}
</style>