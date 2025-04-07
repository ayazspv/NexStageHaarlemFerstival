<script setup lang="ts">
import ArtistCard from './ArtistCard.vue';

const props = defineProps<{
    bands: any[];
    selectedDay: number;
}>();

const emit = defineEmits(['show-details']);
</script>

<template>
    <section class="jazz-artists">
        <div class="container">
            <h2 class="text-center mb-4">Featured Artists</h2>
            
            <div v-if="bands.length > 0" class="festivals-container">
                <!-- Loop through artists with alternating layout -->
                <ArtistCard 
                    v-for="(band, index) in bands" 
                    :key="band.id"
                    :band="band"
                    :isReversed="index % 2 !== 0"
                    @show-details="band => emit('show-details', band)"
                />
            </div>
            
            <div v-else class="text-center py-5">
                <div class="jazz-no-performances">
                    <i class="fas fa-guitar"></i>
                    <p>No artists scheduled for July {{ selectedDay }}.</p>
                </div>
            </div>
        </div>
    </section>
</template>