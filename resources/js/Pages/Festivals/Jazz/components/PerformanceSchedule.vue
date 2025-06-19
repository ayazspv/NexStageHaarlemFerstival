<script setup lang="ts">
import { addJazzEventToCart } from '@/composables/cart';

const props = defineProps<{
    festivalDays: number[];
    selectedDay: number;
    bands: any[];
}>();

const emit = defineEmits(['update:selectedDay', 'show-details']);

function updateSelectedDay(day: number) {
    emit('update:selectedDay', day);
}

// Format time only
const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Add this function to book jazz event from schedule
function bookJazzEvent(band) {
    addJazzEventToCart(
        band.festival_id,
        'Jazz Festival',
        band.id,
        band.band_name,
        band.performance_day,
        band.start_time || formatTime(band.performance_datetime),
        1
    );
}
</script>

<template>
    <!-- Schedule section -->
    <section class="jazz-schedule">
        <div class="container">
            <h2 class="text-center mb-4">Performance Schedule</h2>
            <div class="schedule-container p-3 border rounded">
                <div class="schedule-tabs mb-3">
                    <ul class="nav nav-tabs justify-content-center" id="scheduleTabs" role="tablist">
                        <li v-for="day in festivalDays" :key="day" class="nav-item" role="presentation">
                            <button class="nav-link" 
                                   :class="{'active': selectedDay === day}"
                                   @click="updateSelectedDay(day)">
                                July {{ day }}
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="schedule-grid">
                    <div v-if="bands.length > 0">
                        <div v-for="band in bands" :key="band.id"
                             class="schedule-event jazz-event">
                            <div class="event-time">
                                {{ band.start_time || formatTime(band.performance_datetime) }}
                                {{ band.end_time ? '- ' + band.end_time : '' }}
                            </div>
                            <div class="event-name">{{ band.band_name }}</div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-outline-primary me-2" 
                                        @click="emit('show-details', band)">
                                    Details
                                </button>
                                <button class="btn btn-sm btn-primary" 
                                        @click.prevent="bookJazzEvent(band)">
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
</template>