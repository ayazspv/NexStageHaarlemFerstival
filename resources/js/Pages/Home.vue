<script setup lang="ts">
import AppLayout from "../Pages/Layouts/AppLayout.vue";
import { cart, fetchCartItems, addToCart } from '../composables/cart';
import { wishlist, fetchWishlistItems, addToWishlist } from '../composables/wishlist';
import { ref, computed, onMounted } from 'vue';
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

// Map data for Haarlem festival locations
const festivalLocations = [
    {
        name: "Patronaat",
        description: "Music venue hosting the Jazz Festival",
        coordinates: [52.383109847936645, 4.628657355268647],
        festival: "Jazz Festival"
    },
    {
        name: "Cafe de Roemer",
        description: "Historic café participating in Food Festival",
        coordinates: [52.379988207137316, 4.631883226433229],
        festival: "Food Festival"
    },
    {
        name: "Grote Markt",
        description: "Central square and starting point for History Tours",
        coordinates: [52.381519831037366, 4.635889046442159],
        festival: "A Stroll Through History"
    },
    {
        name: "Teylers Museum",
        description: "Historic museum hosting the Night@Teylers events",
        coordinates: [52.380952434284666, 4.640226658831318],
        festival: "Night@Teylers"
    },
    {
        name: "Ratatouille Food & Wine",
        description: "Fine dining restaurant participating in Food Festival",
        coordinates: [52.378819758004354, 4.637523655268481],
        festival: "Food Festival"
    }
];

// Map initialization function
const initMap = () => {
    // Check if we're in the browser environment
    if (typeof window === 'undefined' || !document.getElementById('festival-map')) return;
    
    // Create map centered on Haarlem
    const map = L.map('festival-map').setView([52.3810, 4.6375], 15);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Create festival-specific markers
    festivalLocations.forEach(location => {
        const festival = props.festivals.find(f => f.name === location.festival);
        
        // Default icon color if festival not found
        let iconColor = 'blue';
        
        // Determine color based on festival type
        if (festival) {
            switch(festival.festivalType) {
                case 0: iconColor = '#2196f3'; break; // Jazz
                case 1: iconColor = '#ff9800'; break; // Food
                case 2: iconColor = '#4caf50'; break; // History
                case 3: iconColor = '#9c27b0'; break; // Night@Teylers
                default: iconColor = '#2196f3';
            }
        }
        
        // Create custom icon
        const icon = L.divIcon({
            className: 'custom-map-marker',
            html: `<div style="background-color: ${iconColor}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        
        // Create marker with popup
        const marker = L.marker(location.coordinates, { icon }).addTo(map);
        marker.bindPopup(`
            <strong>${location.name}</strong>
            <p>${location.description}</p>
            <p>Festival: ${location.festival}</p>
        `);
    });
};

// Call the map initialization after component is mounted
onMounted(() => {
    // Timeout to ensure the DOM is ready
    setTimeout(initMap, 100);
});

fetchCartItems(); 
fetchWishlistItems();

// Add this function to strip HTML tags from descriptions
const stripHtmlTags = (html) => {
  if (!html) return '';
  return html.replace(/<\/?[^>]+(>|$)/g, "");
};
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
                            <!-- Apply the stripHtmlTags function to remove HTML tags -->
                            <p class="festival-description">{{ stripHtmlTags(festival.description) || 'Join us for this amazing festival experience!' }}</p>
                            
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
                                <button class="btn btn-outline-secondary" @click.prevent="addToCart(festival.id)">
                                    <i class="fas fa-heart"></i> Add to Cart
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
                <h2 class="text-center mb-4">Festival Locations in Haarlem</h2>
                <div class="map-container">
                    <div id="festival-map" class="border rounded"></div>
                    <div class="festival-map-legend mt-3">
                        <div class="row">
                            <div v-for="(festival, index) in festivals" :key="festival.id" class="col-md-3 col-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="legend-marker" 
                                         :class="`marker-type-${festival.festivalType}`"></div>
                                    <span class="ms-2">{{ festival.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Map styles */
#festival-map {
    height: 500px;
    width: 100%;
}

.festival-map-legend {
    background-color: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.legend-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    border: 2px solid white;
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
}

.marker-type-0 {
    background-color: #2196f3; /* Jazz */
}

.marker-type-1 {
    background-color: #ff9800; /* Food */
}

.marker-type-2 {
    background-color: #4caf50; /* History */
}

.marker-type-3 {
    background-color: #9c27b0; /* Night@Teylers */
}
</style>
