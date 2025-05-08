<script setup lang="ts">
import { addToWishlist } from '@/composables/wishlist';

const props = defineProps<{
    band: any;
    isReversed: boolean;
}>();

const emit = defineEmits(['show-details']);

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
    <div class="festival-row band-row"
         :class="{ 'flex-row-reverse': isReversed }">
        
        <!-- Artist Image - Fixed size -->
        <div class="festival-image-container">
            <img v-if="band.band_image"
                 :src="`/storage/${band.band_image}`"
                 :alt="band.band_name"
                 class="festival-image">
            <div v-else class="festival-image-placeholder d-flex align-items-center justify-content-center bg-light h-100">
                <i class="fas fa-music fa-3x text-muted"></i>
            </div>
        </div>
        
        <!-- Artist Content - Adjustable height -->
        <div class="festival-content">
            <h3 class="festival-title">{{ band.band_name }}</h3>
            <div class="band-description" v-html="band.band_description.length > 300 ? band.band_description.substring(0, 300) + '...' : band.band_description"></div>
            
            <!-- Performance Time -->
            <div class="festival-time-slot mb-3">
                <i class="far fa-clock me-2"></i>
                <span>{{ band.start_time || formatTime(band.performance_datetime) }} 
                {{ band.end_time ? '- ' + band.end_time : '' }}</span>
                <span class="ms-2 badge bg-primary">€{{ formatPrice(band.ticket_price) }}</span>
            </div>
            
            <!-- Action Buttons -->
            <div class="festival-actions">
                <button @click="emit('show-details', band)" class="btn btn-outline-primary me-2">
                    <i class="fas fa-info-circle me-1"></i> View Details
                </button>
                <button class="btn btn-outline-secondary" @click.prevent="addToWishlist(band.id)">
                    <i class="fas fa-heart"></i> Add to Wishlist
                </button>
            </div>
        </div>
    </div>
</template>