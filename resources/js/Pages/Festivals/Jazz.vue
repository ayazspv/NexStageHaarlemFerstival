<script setup lang="ts">
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
import { Festival } from "../../../models";
import { ref, computed } from 'vue';
import '../../../css/jazz.css';
import FestivalHeader from "./Jazz/components/FestivalHeader.vue";
import FestivalInfo from "./Jazz/components/FestivalInfo.vue";
import PerformanceSchedule from "./Jazz/components/PerformanceSchedule.vue";
import ArtistList from "./Jazz/components/ArtistList.vue";
import ArtistDetailsModal from "./Jazz/components/ArtistDetailsModal.vue";
import { useBandDetails } from "./Jazz/composables/useBandDetails";

const props = defineProps<{
    festival: Festival;
    bands?: any[];
}>();

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

const { selectedBand, showBandDetails, closeBandDetails } = useBandDetails();
</script>

<template>
    <AppLayout :title="festival?.name || 'Jazz Festival'">
        <!-- Festival Header Section -->
        <FestivalHeader :festival="festival" />

        <!-- Festival Info Section -->
        <FestivalInfo :festival="festival" />

        <!-- Schedule Section -->
        <PerformanceSchedule 
            :festivalDays="festivalDays"
            :selectedDay="selectedDay"
            :bands="sortedBandsByTime"
            @update:selectedDay="selectedDay = $event"
            @show-details="showBandDetails"
        />

        <!-- Artists Section -->
        <ArtistList 
            :bands="bandsForSelectedDay"
            :selectedDay="selectedDay"
            @show-details="showBandDetails"
        />

        <!-- Band Details Modal -->
        <ArtistDetailsModal
            v-if="selectedBand"
            :band="selectedBand"
            @close="closeBandDetails"
        />
    </AppLayout>
</template>