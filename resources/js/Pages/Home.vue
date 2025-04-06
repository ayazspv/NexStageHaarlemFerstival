<script setup lang="ts">
import AppLayout from "../Pages/Layouts/AppLayout.vue";
import { cart, fetchCartItems, addToCart } from '../composables/cart';
import { wishlist, fetchWishlistItems, addToWishlist } from '../composables/wishlist';
import { ref, computed } from 'vue';
import { Festival } from "../../models";

const props = defineProps<{
    festivals: Festival[];
}>();

const parseToUrl = (title: string) => {
    return title.trim().toLowerCase().replace(/\s+/g, '-');
};

// Helper function to find festival by name
const findFestival = (name: string) => {
    return props.festivals.find((f: Festival) => f.name === name);
};

// Festival Type Classes by type ID instead of name
const festivalTypeClassMap = {
    0: 'jazz-event',     // Jazz
    1: 'yummy-event',    // Food
    2: 'history-event',  // History
    3: 'teylers-event'   // Night@Teylers
};

// Schedule data using dynamic festival information
const days = ['24', '25', '26', '27'];

const scheduleEvents = computed(() => {
    return props.festivals.map(festival => {
        const type = festivalTypeClassMap[festival.festivalType] || 'default-event';
        return {
            type,
            time: festival.time_slot || '(Time not specified)',
            name: festival.name,
            eventId: festival.id
        };
    });
});

fetchCartItems(); 
fetchWishlistItems();
</script>

<template>
    <AppLayout title="Home">
        <div class="container-fluid">
            <!-- Schedule Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Schedule</h2>
                <div class="schedule-container p-3 border rounded">
                    <div class="schedule-tabs mb-3">
                        <ul class="nav nav-tabs justify-content-center" id="scheduleTabs" role="tablist">
                            <li v-for="day in days" :key="day" class="nav-item" role="presentation">
                                <button class="nav-link" 
                                       :class="{'active': day === '24'}"
                                       :id="`day${day}-tab`" 
                                       data-bs-toggle="tab" 
                                       :data-bs-target="`#day${day}`" 
                                       type="button" 
                                       role="tab" 
                                       :aria-controls="`day${day}`" 
                                       :aria-selected="day === '24'">
                                    July {{ day }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="scheduleTabsContent">
                        <!-- Loop through each day -->
                        <div v-for="day in days" :key="day"
                             class="tab-pane fade" 
                             :class="{'show active': day === '24'}"
                             :id="`day${day}`" 
                             role="tabpanel" 
                             :aria-labelledby="`day${day}-tab`">
                            <div class="schedule-grid">
                                <!-- Loop through each event -->
                                <div v-for="(event, index) in scheduleEvents" :key="index"
                                     class="schedule-event"
                                     :class="event.type">
                                    <div class="event-time">{{ event.time }}</div>
                                    <div class="event-name">{{ event.name }}</div>
                                    <div class="event-actions">
                                        <button class="btn btn-sm btn-primary" 
                                                @click.prevent="addToCart(event.eventId)">
                                            <i class="fas fa-ticket-alt"></i> Book
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Festivals Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Festivals</h2>
                
                <div class="festivals-container">
                    <!-- Loop through festivals with alternating layout -->
                    <div v-for="(festival, index) in festivals" 
                         :key="festival.id"
                         class="festival-row"
                         :class="{ 'flex-row-reverse': index % 2 !== 0 }">
                        
                        <!-- Festival Image -->
                        <div class="festival-image-container">
                            <img :src="`/storage/${festival.image_path}`"
                                 :alt="festival.name"
                                 class="festival-image">
                        </div>
                        
                        <!-- Festival Content -->
                        <div class="festival-content">
                            <h3 class="festival-title">{{ festival.name }}</h3>
                            <p class="festival-description">{{ festival.description || 'Join us for this amazing festival experience!' }}</p>
                            
                            <!-- Time slot information -->
                            <div class="festival-time-slot mb-3">
                                <i class="far fa-clock me-2"></i>
                                <span>{{ festival.time_slot || 'Time not specified' }}</span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="festival-actions">
                                <a :href="`/festivals/${parseToUrl(festival.name)}`" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-info-circle me-1"></i> View Details
                                </a>
                                <button class="btn btn-primary me-2" @click.prevent="addToCart(festival.id)">
                                    <i class="fas fa-ticket-alt me-1"></i> Buy Ticket
                                </button>
                                <button class="btn btn-outline-secondary" @click.prevent="addToWishlist(festival.id)">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            
                            <!-- Availability Badge -->
                            <div class="ticket-badge" v-if="festival.ticket_amount">
                                {{ festival.ticket_amount }} tickets available
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Locations(Map) Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Locations</h2>
                <div class="map-container">
                    <div class="map-placeholder border rounded">
                        Map will be implemented here
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
