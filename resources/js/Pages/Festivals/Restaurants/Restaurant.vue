<script lang="ts" setup>

import Head from './Components/Head.vue';
import Introduction from './Components/Introduction.vue';
import Timetables from './Components/Timetables.vue';
import Reservation from './Components/Reservation.vue';
import Map from './Components/Map.vue';
import Gallery from './Components/Gallery.vue';
import Footer from './Components/Footer.vue';
import { defineProps } from 'vue';

const props = defineProps({
    restaurant: Object
});

</script>

<template>
    <div class="d-flex flex-column">
        <Head :restaurant="props.restaurant" />
        <Introduction :restaurant="props.restaurant" />
        <Timetables :restaurant="props.restaurant" />
        <Reservation :restaurant="props.restaurant" />
        <Map :restaurant="props.restaurant" />
        <Gallery :restaurant="props.restaurant" />
        <Footer />
    </div>
</template>