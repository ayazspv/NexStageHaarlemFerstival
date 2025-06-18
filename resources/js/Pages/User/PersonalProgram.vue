<script setup lang="ts">
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
import { ref, computed } from 'vue';
import { addToCart } from '@/composables/cart';
import { addToWishlist } from '@/composables/wishlist';
import { useToast } from '@/composables/toast';

const props = defineProps<{
    user: any;
    festivals: any[];
    festivalsByDay: Record<number, any[]>;
}>();

const { showToast } = useToast();
const selectedDay = ref(null);
const festivalDays = [24, 25, 26, 27]; // July festival days

// Compute ticket prices
const dayTicketPrice = 50; // Price for a single day ticket
const fullTicketPrice = 150; // Price for a full festival ticket

// Add day ticket to cart
async function addDayTicketToCart(day) {
    const ticketName = `Day Pass - July ${day}`;
    await addToCart(-1, ticketName, 1, 'day_pass', { day });
    showToast('Day ticket added to cart', 'success');
}

// Add day ticket to wishlist
function addDayTicketToWishlist(day) {
    const ticketName = `Day Pass - July ${day}`;
    addToWishlist(-1, ticketName, 'day_pass', { day });
    showToast('Day ticket added to wishlist', 'success');
}

// Add full festival ticket to cart
async function addFullTicketToCart() {
    await addToCart(-2, 'Full Festival Pass', 1, 'full_pass');
    showToast('Full festival ticket added to cart', 'success');
}

// Add full festival ticket to wishlist
function addFullTicketToWishlist() {
    addToWishlist(-2, 'Full Festival Pass', 'full_pass');
    showToast('Full festival ticket added to wishlist', 'success');
}

// Helper function to get the appropriate class for each festival type
function getFestivalTypeClass(type) {
    switch(type) {
        case 0: return 'jazz-festival';
        case 1: return 'food-festival';
        case 2: return 'history-festival';
        case 3: return 'teylers-festival';
        default: return '';
    }
}
</script>

<template>
    <AppLayout title="Personal Program">
        <div class="container my-5">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">Your Personal Program</h1>
                    <p class="lead">Welcome, {{ user.firstName }}! Customize your festival experience by selecting the ticket pass that best suits your plans.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Festival Info:</strong> All four festivals (Jazz, Food, History, and Night@Teylers) take place on each day of the Haarlem Festival (July 24-27, 2025).
                    </div>
                </div>
            </div>

            <!-- Ticket Selection -->
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0">Day Pass</h3>
                        </div>
                        <div class="card-body">
                            <h4 class="price mb-3">€{{ dayTicketPrice }}</h4>
                            <p>Access <strong>all four festivals</strong> on a single day of your choice.</p>
                            
                            <div class="form-group mb-3">
                                <label for="daySelect">Select Day:</label>
                                <select id="daySelect" v-model="selectedDay" class="form-select">
                                    <option value="" disabled>Choose a day</option>
                                    <option v-for="day in festivalDays" :key="day" :value="day">
                                        July {{ day }}, 2025
                                    </option>
                                </select>
                            </div>
                            
                            <div v-if="selectedDay" class="day-festivals mb-3">
                                <h5>All these festivals included on July {{ selectedDay }}:</h5>
                                <ul class="festival-list">
                                    <li v-for="festival in festivals" :key="festival.id" 
                                        :class="getFestivalTypeClass(festival.festivalType)">
                                        {{ festival.name }}
                                        <span class="time-slot">{{ festival.time_slot }}</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="day-pass-benefits mb-3">
                                <h5>Includes:</h5>
                                <ul>
                                    <li>Access to all four festival venues on your selected day</li>
                                    <li>All performances and events happening on that day</li>
                                    <li>Food and drink discounts at festival venues</li>
                                    <li>Festival program booklet</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" 
                                        :disabled="!selectedDay" 
                                        @click="addDayTicketToCart(selectedDay)">
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline-primary" 
                                        :disabled="!selectedDay"
                                        @click="addDayTicketToWishlist(selectedDay)">
                                    Add to Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title mb-0">Full Festival Pass</h3>
                        </div>
                        <div class="card-body">
                            <h4 class="price mb-3">€{{ fullTicketPrice }}</h4>
                            <p>Get complete access to <strong>all four festivals for all four days</strong> of the Haarlem Festival (July 24-27).</p>
                            
                            <div class="all-festivals mb-3">
                                <h5>All Festivals Included:</h5>
                                <ul class="festival-list">
                                    <li v-for="festival in festivals" :key="festival.id"
                                        :class="getFestivalTypeClass(festival.festivalType)">
                                        {{ festival.name }}
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="full-pass-benefits mb-3">
                                <h5>Includes:</h5>
                                <ul>
                                    <li>Unlimited access to all four festivals on all days (July 24-27)</li>
                                    <li>All performances and events across the entire festival</li>
                                    <li>VIP seating where available</li>
                                    <li>Exclusive festival merchandise</li>
                                    <li>Priority entry at all venues</li>
                                    <li>25% discount on food and drinks</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" @click="addFullTicketToCart">
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline-success" @click="addFullTicketToWishlist">
                                    Add to Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Festival Schedule -->
            <div class="row mt-5">
                <div class="col-12">
                    <h2 class="mb-4">Festival Schedule</h2>
                    <p class="text-muted mb-3">All festivals run from July 24-27, 2025. Each festival has its own venue and schedule.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Festival</th>
                                    <th>Daily Time Slot</th>
                                    <th>Festival Days</th>
                                    <th>Individual Ticket Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="festival in festivals" :key="festival.id" 
                                    :class="getFestivalTypeClass(festival.festivalType)">
                                    <td>{{ festival.name }}</td>
                                    <td>{{ festival.time_slot || 'TBA' }}</td>
                                    <td>July 24-27, 2025</td>
                                    <td>€{{ festival.ticket_amount || '-' }}</td>
                                </tr>
                                <tr v-if="!festivals.length">
                                    <td colspan="4" class="text-center py-3">No festivals found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.price {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.day-pass-benefits, .full-pass-benefits, .day-festivals, .all-festivals {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
}

.card-header {
    font-weight: bold;
}

.festival-list {
    list-style-type: none;
    padding-left: 0.5rem;
}

.festival-list li {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border-left: 4px solid #ddd;
    background-color: white;
    border-radius: 0 4px 4px 0;
}

.jazz-festival {
    border-left-color: #2196f3 !important;
}

.food-festival {
    border-left-color: #4caf50 !important;
}

.history-festival {
    border-left-color: #ff9800 !important;
}

.teylers-festival {
    border-left-color: #9c27b0 !important;
}

.time-slot {
    font-size: 0.85rem;
    color: #666;
    margin-left: 0.5rem;
}
</style>