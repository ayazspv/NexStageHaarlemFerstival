<script setup lang="ts">
import { addToWishlist } from '@/composables/wishlist';

const props = defineProps<{
    band: any;
}>();

const emit = defineEmits(['close']);

// Format time only
const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Format price safely
const formatPrice = (price) => {
    if (price === undefined || price === null) return '0.00';
    return typeof price === 'number' ? price.toFixed(2) : Number(price).toFixed(2);
};
</script>

<template>
    <div class="artist-details-modal">
        <div class="artist-details-overlay" @click="emit('close')"></div>
        <div class="artist-details-content">
            <button class="artist-details-close" @click="emit('close')">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="artist-details-header">
                <h2>{{ band.band_name }}</h2>
            </div>
            
            <div class="artist-details-body">
                <div class="row g-4">
                    <!-- Artist Image -->
                    <div class="col-lg-5">
                        <div class="artist-details-image-container">
                            <img 
                                v-if="band.band_image"
                                :src="`/storage/${band.band_image}`" 
                                :alt="band.band_name" 
                                class="artist-details-image"
                            />
                            <div v-else class="artist-details-image-placeholder">
                                <i class="fas fa-music"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Artist Info -->
                    <div class="col-lg-7">
                        <div class="artist-details-info">
                            <div class="artist-details-meta">
                                <div class="artist-details-time">
                                    <i class="far fa-calendar-alt me-2"></i>
                                    <span>July {{ band.performance_day }}, </span>
                                    <span>
                                        {{ band.start_time || formatTime(band.performance_datetime) }}
                                        {{ band.end_time ? '- ' + band.end_time : '' }}
                                    </span>
                                </div>
                                
                                <div class="artist-details-price">
                                    <i class="fas fa-ticket-alt me-2"></i>
                                    <span>€{{ formatPrice(band.ticket_price) }}</span>
                                </div>
                            </div>
                            
                            <div class="artist-details-description" v-html="band.band_description"></div>
                            
                            <div class="artist-details-full-info" v-html="band.band_details"></div>
                            
                            <div class="artist-details-actions">
                                <!-- Wishlist button atm -->
                                <button class="btn btn-primary" @click.prevent="addToWishlist(band.id)">
                                    <i class="fas fa-heart me-1"></i> Add to Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>