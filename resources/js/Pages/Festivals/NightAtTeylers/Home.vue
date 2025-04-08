<script setup lang="ts">
import { defineEmits } from 'vue';
import { Festival } from "../../../../models";

const props = defineProps<{
    festival: Festival,
}>();

// Define an emit for the changeView event with a payload (the view name)
const emit = defineEmits<{ (e: 'changeView', view: string): void }>();
</script>

<template>
    <div class="mobile-content">
        <div class="d-flex flex-column mt-10 w-100">
            <div class="w-75 mx-auto">
                <p>{{ festival.description }}</p>
            </div>
        </div>
        <div class="d-flex flex-row justify-content-center gap-3 mt-10">
            <!-- When Play is clicked, emit changeView with 'games' -->
            <div class="mobile-box" @click="emit('changeView', 'games')">
                <img class="mt-2" src="/storage/main/games/play.png" alt="Play icon">
                <h3 class="mt-3">Play</h3>
            </div>
            <!-- When Map is clicked, emit changeView with 'map' -->
            <div class="mobile-box blue" @click="emit('changeView', 'map')">
                <img class="mt-2" src="/storage/main/games/map.png" alt="Map icon">
                <h3 class="mt-3">Map</h3>
            </div>
            <!-- When Stamps is clicked, emit changeView with 'stamps' -->
            <div class="mobile-box green" @click="emit('changeView', 'stamps')">
                <img class="mt-2" src="/storage/main/games/stamps.png" alt="Stamps icon">
                <h3 class="mt-3">Stamps</h3>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');

.mobile-content {
    flex: 1;
    overflow-y: auto;
}

.mobile-box {
    height: 200px;
    width: 110px;
    background-color: #F9E593;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
}

.mobile-box.blue {
    background-color: #A9D7ED;
}

.mobile-box.green {
    background-color: #CDDBC5;
}

.mobile-box img {
    width: 80%;
}

.mobile-box h3 {
    color: black;
}
</style>
