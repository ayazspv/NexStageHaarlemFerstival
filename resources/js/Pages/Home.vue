<script setup lang="ts">
import AppLayout from "../Pages/Layouts/AppLayout.vue";
import { cart, fetchCartItems, addToCart } from '../composables/cart';
import { wishlist, fetchWishlistItems, addToWishlist } from '../composables/wishlist';
import { ref } from 'vue';

const props = defineProps({
    festivals: {
        type: Array,
        default: () => []
    }
});

const parseToUrl = (title: string) => {
    return title.trim().toLowerCase().replace(/\s+/g, '-');
};

// Helper function to find festival ID by name
const getEventId = (name: string) => {
    const festival = props.festivals.find((f: any) => f.name === name);
    return festival ? festival.id : null;
};

// Schedule data to avoid repetition
const days = ['24', '25', '26', '27'];
const scheduleEvents = [
    { 
        type: 'history-event',
        time: '10:00 - 16:00', 
        name: 'A Stroll Through History',
        eventId: getEventId('A Stroll Through History')
    },
    { 
        type: 'yummy-event',
        time: '16:00 - 18:00', 
        name: 'Yummy!',
        eventId: getEventId('Food Festival')
    },
    { 
        type: 'jazz-event',
        time: '18:00 - 22:00', 
        name: 'Haarlem Jazz',
        eventId: getEventId('Jazz Festival')
    },
    { 
        type: 'teylers-event',
        time: '10:00 - 17:00', 
        name: 'Magic@ Teyler',
        eventId: getEventId('Night@Teylers')
    }
];

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
                <div class="row g-4">
                    <div v-for="festival in festivals"
                         :key="festival.id"
                         class="col-12 col-md-6">
                        <div class="festival-wrapper">
                            <a :href="`/festivals/${parseToUrl(festival.name)}`">
                                <div class="card festival-card mb-3">
                                    <img :src="`/storage/${festival.image_path}`"
                                         :alt="festival.name"
                                         class="card-img-top">
                                </div>
                            </a>
                            <h5 class="text-center mb-3">{{ festival.name }}</h5>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-primary" @click.prevent="addToCart(festival.id)">
                                    <i class="fas fa-ticket-alt me-2"></i>Buy Ticket
                                </button>
                                <button class="btn btn-outline-primary" @click.prevent="addToWishlist(festival.id)">
                                    <i class="fas fa-heart"></i> Wishlist
                                </button>
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
