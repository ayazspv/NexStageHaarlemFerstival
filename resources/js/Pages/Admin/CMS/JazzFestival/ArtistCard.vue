<script setup lang="ts">
import { JazzFestival } from '../../../../../models';

const props = defineProps<{
    band: JazzFestival
}>();

const emit = defineEmits(['edit', 'delete']);

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
    return typeof price === 'number' ? price.toFixed(2) : Number(price).toFixed(2);
};
</script>

<template>
    <div class="card h-100 artist-card">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">{{ band.band_name }}</h5>
            <span class="badge bg-primary">
                {{ formatTime(band.performance_datetime) }}
            </span>
        </div>
        <div class="position-relative artist-image-container">
            <img 
                v-if="band.band_image" 
                :src="`/storage/${band.band_image}`" 
                :alt="band.band_name" 
                class="card-img-top artist-image"
            >
            <div v-else class="card-img-top artist-image-placeholder d-flex align-items-center justify-content-center">
                <i class="fas fa-music" style="font-size: 3rem;"></i>
            </div>
            <div class="price-badge">€{{ formatPrice(band.ticket_price) }}</div>
        </div>
        <div class="card-body">
            <div class="artist-description" v-html="band.band_description"></div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <button class="btn btn-sm btn-info" @click="$emit('edit', band)">
                <i class="fas fa-edit me-1"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger" @click="$emit('delete', band.id)">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
    </div>
</template>

<style scoped>
.artist-image-container {
    height: 200px;
    overflow: hidden;
}

.artist-image, .artist-image-placeholder {
    height: 100%;
    width: 100%;
    object-fit: cover;
}

.artist-image-placeholder {
    background-color: #f0f0f0;
    color: #aaa;
}

.price-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #28a745;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
}

.artist-description {
    max-height: 120px;
    overflow-y: auto;
}
</style>