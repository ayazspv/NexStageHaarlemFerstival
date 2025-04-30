<script setup lang="ts">
import AppLayout from "../Pages/Layouts/AppLayout.vue";
import { cart, fetchCartItems, addToCart } from '../composables/cart';
import { wishlist, fetchWishlistItems, addToWishlist } from '../composables/wishlist';
import { ref, computed, onMounted } from 'vue';
import { Festival } from "../../models";

const props = defineProps<{
    festivals: Festival[];
    heroUrl: string | null,
    homepageContent: string | null,
}>();

// URL-friendly
const parseToUrl = (title: string) => {
    return title.trim().toLowerCase().replace(/\s+/g, '-');
};

// Helper function to find festival by name (not in use)
const findFestival = (name: string) => {
    return props.festivals.find((f: Festival) => f.name === name);
};

// Festival Type Classes by type ID
const festivalTypeClassMap = {
    0: 'jazz-event',     // Jazz
    1: 'yummy-event',    // Food
    2: 'history-event',  // History
    3: 'teylers-event'   // Night@Teylers
};

// Schedule data
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
    // Check if the map container exists
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
            html: `<div style="background-color: ${iconColor}; width: 21px; height: 17px; border-radius: 50%; border: 2px solid white;"></div>`,
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
    // Timeout to ensure its ready
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
        <div class="container-fluid p-0">
            <!-- Hero Section -->
            <section class="hero-section" :style="`background: url(${heroUrl}) center/cover no-repeat`">
                <div class="overlay"></div>
                <div class="hero-content d-flex flex-column align-items-center gap-1">
                    <h1 class="hero-title-haarlem">
                        <span style="color: #e40303;">H</span>
                        <span style="color: #ff8c00;">A</span>
                        <span style="color: #ff8c00;">A</span>
                        <span style="color: #ffed00;">R</span>
                        <span style="color: #008026;">L</span>
                        <span style="color: #4c82ff;">E</span>
                        <span style="color: #732982;">M</span>
                    </h1>
                    <h1 class="hero-title-festival mb-5">FESTIVAL</h1>
                    <div style="text-align: center;">
                        <h1 class="hero-title-info">
                            24 - 27 JULY
                        </h1>
                        <h1 class="hero-title-info">
                            HAARLEM, THE NETHERLANDS
                        </h1>
                    </div>
                </div>
            </section>
            <!-- Hero Descript Section -->
            <section class="hero-description-section d-flex flex-column">
                <div class="hero-description" v-html="homepageContent"></div>
            </section>



            <div class="p-0 d-flex flex-column align-items-center">
                <!-- Festivals Section -->
                <section class="mb-5 mt-5 w-75">
                    <div class="d-flex flex-row align-items-center justify-content-center gap-5">
                        <div class="section-title-bars"></div>
                        <h2 class="text-center section-title">THE FESTIVALS</h2>
                        <div class="section-title-bars"></div>
                    </div>

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
                                    <button class="btn btn-outline-secondary" @click.prevent="addToCart(festival.id, festival.name, 20)">
                                        <i class="fas fa-heart"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-outline-warning" @click.prevent="addToWishlist(festival.id, festival.name)">
                                        <i class="fas fa-heart"></i> Add to Wishlist
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

                <!-- Schedule Section -->
                <section class="schedule-section py-5">
                    <div class="container">
                        <div class="d-flex flex-column align-items-center mb-5">
                            <h2 class="section-title text-center">Festival Schedule</h2>
                            <div class="section-title-bars"></div>
                        </div>
                        
                        <div class="custom-schedule">
                            <div v-for="day in ['24', '25', '26', '27']" :key="day" class="schedule-item">
                                <div class="schedule-day">
                                    <div class="day-number">
                                        {{ day }}<span class="month-display">JULY</span>
                                    </div>
                                    <div class="vertical-line"></div>
                                    <div class="schedule-content">
                                        <div class="time-column">
                                            <div v-for="festival in festivals" :key="`${day}-${festival.id}-time`"
                                                 class="time-item" 
                                                 :class="festivalTypeClassMap[festival.festivalType]">
                                                {{ festival.time_slot }}
                                            </div>
                                        </div>
                                        <div class="vertical-separator"></div>
                                        <div class="name-column">
                                            <div v-for="festival in festivals" :key="`${day}-${festival.id}-name`"
                                                 class="name-item" 
                                                 :class="festivalTypeClassMap[festival.festivalType]">
                                                {{ festival.name.toUpperCase() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="day !== '27'" class="day-separator"></div>
                            </div>
                        </div>
                    </div>
                </section>


                <!-- Locations(Map) Section -->
                <section class="mb-5 mt-5 w-75">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <h2 class="text-left section-title">Where are the festivals located?</h2>
                        <p>Here below is a simple map of all the festival locations</p>
                    </div>
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
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bayon&display=swap');

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

.section-title {
    font-family: "Bayon", sans-serif;
    font-weight: 400;
    font-style: normal;
    font-size: 70px;
    color: #2A5CAE;
}

.section-title-close {
    font-family: "Bayon", sans-serif;
    font-weight: 400;
    font-style: normal;
    font-size: 70px;
    color: #2A5CAE;
    margin-bottom: -10px;
}

.section-title-bars {
    flex-grow: 1;
    height: 5px;
    background-color: #2A5CAE;

}

.section-color-switch {
    background-color: #F0F0F0;
    padding: 0px!important;
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

.hero-section {
    margin: 0;
    padding: 0;
    position: relative;
    height: 700px;
    overflow: hidden;
    width: 100%;
}

.hero-section .overlay {
    position: absolute;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.6);
}

.hero-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.hero-title-haarlem,
.hero-title-festival {
    margin: 0;
    font-size: 90px;
    color: #fff;
    text-align: center;
    font-family: "Bayon", sans-serif;
    font-weight: 400;
    font-style: normal;
}

.hero-title-info {
    font-family: "Bayon", sans-serif;
    font-weight: 400;
    font-style: normal;
    font-size: 50px;
    color: #fff;
}

.hero-title-festival {
    /* inherits white color from above */
}

.hero-description-section {
    height: 400px;
    background-color: #F0F0F0;

    justify-content: center;
    align-items: center;
}

.hero-description {
    width: 50%;
    text-align: center;
}

.hero-description :deep(h1) {
    font-family: "Bayon", sans-serif;
    font-weight: 400;
    font-style: normal;
    font-size: 60px;
    color: #2A5CAE;
    margin-bottom: 20px;
}

.hero-description :deep(p) {
    margin: 10px;
    font-size: 16px;
    line-height: 1.5;
}
</style>
