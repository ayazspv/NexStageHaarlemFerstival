<script setup lang="ts">

import {route} from "../../../../utils";
import {Festival} from "../../../../models";

defineProps<{
    festival: Festival,
}>();
</script>

<template>
    <div class="mobile-content">
        <div class="d-flex flex-row justify-content-between align-items-center mt-4">
            <img class="mobile-logo" src="/storage/main/arrow-left.png" alt="Arrow left">
            <h3 style="text-align: center">{{ festival.name }}</h3>
            <img class="mobile-logo" src="/storage/main/logo.png" alt="Logo">
        </div>
        <div class="d-flex flex-column mt-10 w-100">
            <div class="w-75 mx-auto" >
                <p>
                    {{ festival.description }}
                </p>
            </div>
        </div>
        <div class="d-flex flex-row justify-content-center gap-3 mt-10">
            <a :href="route(festival.name, '')">
                <div class="mobile-box">
                    <img class="mt-2" src="/storage/main/games/play.png" alt="Play icon">
                    <h3 class="mt-3">
                        Play
                    </h3>
                </div>
            </a>
            <a :href="route(festival.name, 'map')">
                <div class="mobile-box blue">
                    <img class="mt-2" src="/storage/main/games/map.png" alt="Map icon">
                    <h3 class="mt-3">
                        Map
                    </h3>
                </div>
            </a>
            <a :href="route(festival.name, 'stamps')">
                <div class="mobile-box green">
                    <img class="mt-2" src="/storage/main/games/stamps.png" alt="Stamps icon">
                    <h3 class="mt-3">
                        Stamps
                    </h3>
                </div>
            </a>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');

* {
    font-family: "Fredoka", sans-serif;
}

.mobile-logo {
    height: 50px;
    width: 50px;
}


.mobile-display p {
    font-size: 22px;
}

.mobile-content {
    flex: 1;
    overflow-y: auto;
}

.mobile-display footer {
    flex-shrink: 0;
    height: 60px;
    width: 100%;
}

.mobile-box {
    height: 200px;
    width: 110px;
    background-color: #F9E593;

    display: flex;
    flex-direction: column;
    align-items: center;
}

.mobile-box.blue {
    height: 200px;
    width: 110px;
    background-color: #A9D7ED;
}

.mobile-box.green {
    height: 200px;
    width: 110px;
    background-color: #CDDBC5; /* Light green */
}

.mobile-box img {
    width: 80%;
}

.mobile-box h3 {
    color: black;
}

</style>
