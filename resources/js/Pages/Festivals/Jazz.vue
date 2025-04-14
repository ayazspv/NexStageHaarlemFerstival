<script setup lang="ts">
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
import { Festival } from "../../../models";
import { addToCart } from '@/composables/cart';
import { addToWishlist } from '@/composables/wishlist';
import { ref, computed } from 'vue';

const props = defineProps<{
    festival: Festival;
    bands?: any[];
}>();

// Function to format dates nicely
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
    }).format(date);
};

// Format time only
const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Festival day tabs
const festivalDays = [24, 25, 26, 27]; 
const selectedDay = ref(24);

// Filter bands by the selected day
const bandsForSelectedDay = computed(() => {
    if (!props.bands) return [];
    return props.bands.filter(band => band.performance_day === selectedDay.value);
});

// Sort bands by performance time
const sortedBandsByTime = computed(() => {
    return [...bandsForSelectedDay.value].sort((a, b) => {
        return new Date(a.performance_datetime).getTime() - new Date(b.performance_datetime).getTime();
    });
});

// Modal for band details
const selectedBand = ref(null);
const showBandDetails = (band) => {
    selectedBand.value = band;
};
const closeBandDetails = () => {
    selectedBand.value = null;
};
</script>

<template>
    <AppLayout :title="festival?.name || 'Jazz Festival'">
        <!-- Festival Header Section -->
        <section class="festival-header">
            <div class="container text-center">
                <h1 class="festival-title display-4">{{ festival.name }}</h1>
            </div>
        </section>

        <!-- Festival Info Section -->
        <section class="festival-info py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <img 
                            :src="`/storage/${festival.image_path}`" 
                            :alt="festival.name" 
                            class="img-fluid rounded festival-image shadow"
                        />
                    </div>
                    <div class="col-lg-6">
                        <div class="festival-description" v-html="festival.description"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Schedule Section -->
        <section class="jazz-schedule">
            <div class="container">
                <h2 class="text-center mb-4">Performance Schedule</h2>
                <div class="schedule-container p-3 border rounded">
                    <div class="schedule-tabs mb-3">
                        <ul class="nav nav-tabs justify-content-center" id="scheduleTabs" role="tablist">
                            <li v-for="day in festivalDays" :key="day" class="nav-item" role="presentation">
                                <button class="nav-link" 
                                       :class="{'active': selectedDay === day}"
                                       @click="selectedDay = day">
                                    July {{ day }}
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="schedule-grid">
                        <div v-if="bandsForSelectedDay.length > 0">
                            <div v-for="band in sortedBandsByTime" :key="band.id"
                                 class="schedule-event jazz-event">
                                <div class="event-time">{{ formatTime(band.performance_datetime) }}</div>
                                <div class="event-name">{{ band.band_name }}</div>
                                <div class="event-actions">
                                    <button class="btn btn-sm btn-outline-primary me-2" @click="showBandDetails(band)">
                                        Details
                                    </button>
                                    <button class="btn btn-sm btn-primary" 
                                            @click.prevent="addToCart(band.id, band.band_name, band.ticket_price)">
                                        <i class="fas fa-ticket-alt"></i> Book
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="jazz-no-performances">
                            <i class="fas fa-guitar"></i>
                            <p>No performances scheduled for July {{ selectedDay }}.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Artists Section -->
        <section class="jazz-artists">
            <div class="container">
                <h2 class="text-center mb-4">Featured Artists</h2>
                
                <div v-if="bandsForSelectedDay.length > 0" class="festivals-container">
                    <!-- Loop through artists with alternating layout -->
                    <div v-for="(band, index) in bandsForSelectedDay" 
                         :key="band.id"
                         class="festival-row band-row"
                         :class="{ 'flex-row-reverse': index % 2 !== 0 }">
                        
                        <!-- Artist Image -->
                        <div class="festival-image-container">
                            <img :src="`/storage/${band.band_image}`"
                                 :alt="band.band_name"
                                 class="festival-image">
                        </div>
                        
                        <!-- Artist Content -->
                        <div class="festival-content">
                            <h3 class="festival-title">{{ band.band_name }}</h3>
                            <div class="band-description" v-html="band.band_description.length > 300 ? band.band_description.substring(0, 300) + '...' : band.band_description"></div>
                            
                            <!-- Performance Time -->
                            <div class="festival-time-slot mb-3">
                                <i class="far fa-clock me-2"></i>
                                <span>{{ formatTime(band.performance_datetime) }}</span>
                                <span class="ms-2 badge bg-primary">€{{ typeof band.ticket_price === 'number' ? band.ticket_price.toFixed(2) : Number(band.ticket_price).toFixed(2) }}</span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="festival-actions d-flex align-items-center">
                                <button @click="showBandDetails(band)" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-info-circle me-1"></i> View Details
                                </button>
                                <button class="btn btn-outline-secondary me-2" @click.prevent="addToWishlist(band.id, band.band_name)">
                                    <i class="fas fa-heart"></i> Add to Wishlist
                                </button>
                                <button class="btn btn-outline-success" @click.prevent="addToCart(band.id, band.band_name, band.ticket_price, band.performance_datetime)">
                                    <i class="fas fa-ticket-alt"></i> Add to Cart
                                </button>
                            </div>
                            <div class="price-badge">€{{ typeof band.ticket_price === 'number' ? band.ticket_price.toFixed(2) : Number(band.ticket_price).toFixed(2) }}</div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-5">
                    <div class="jazz-no-performances">
                        <i class="fas fa-guitar"></i>
                        <p>No artists scheduled for July {{ selectedDay }}.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Band Details Modal -->
        <div v-if="selectedBand" class="jazz-modal">
            <div class="jazz-modal-content">
                <span class="jazz-modal-close" @click="closeBandDetails">&times;</span>
                <div class="jazz-modal-header">
                    <h2>{{ selectedBand.band_name }}</h2>
                </div>
                <div class="jazz-modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="jazz-modal-image-container">
                                <img 
                                    :src="`/storage/${selectedBand.band_image}`" 
                                    :alt="selectedBand.band_name" 
                                    class="jazz-modal-image"
                                />
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="jazz-modal-details">
                                <div class="jazz-modal-datetime">
                                    <i class="far fa-calendar-alt me-2"></i>
                                    <span>{{ formatDate(selectedBand.performance_datetime) }}</span>
                                </div>
                                <div class="jazz-modal-price">
                                    <strong>Price:</strong> €{{ typeof selectedBand.ticket_price === 'number' ? selectedBand.ticket_price.toFixed(2) : Number(selectedBand.ticket_price).toFixed(2) }}
                                </div>
                                <div class="jazz-modal-description" v-html="selectedBand.band_description"></div>
                                <div class="jazz-modal-details-content" v-html="selectedBand.band_details"></div>
                                <div class="jazz-modal-actions">
                                    <button class="btn btn-primary" @click.prevent="addToCart(selectedBand.id)">
                                        <i class="fas fa-ticket-alt me-1"></i> Book Tickets
                                    </button>
                                    <button class="btn btn-outline-secondary" @click.prevent="addToWishlist(selectedBand.id)">
                                        <i class="fas fa-heart me-1"></i> Add to Wishlist
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.festival-header {
    background-color: #f8f9fa;
    padding: 2.5rem 0;
    margin-bottom: 0;
}

.festival-title {
    color: #2565c7;
    font-weight: 700;
    margin-bottom: 0;
}

.festival-info {
    background-color: white;
}

.festival-image {
    transition: transform 0.3s ease;
}

.festival-image:hover {
    transform: scale(1.02);
}

.festival-description {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #333;
}
</style>