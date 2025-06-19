<script lang="ts" setup>

import Head from './Components/Head.vue';
import Introduction from './Components/Introduction.vue';
import Timetables from './Components/Timetables.vue';
import Reservation from './Components/Reservation.vue';
import Map from './Components/Map.vue';
import Gallery from './Components/Gallery.vue';
import Footer from './Components/Footer.vue';

</script>
<template>
    <div class="d-flex flex-column">
        <Head />
        <Introduction />
        <Timetables />
        <Reservation />
        <Map />
        <Gallery />
        <Footer />
    </div>
</template>