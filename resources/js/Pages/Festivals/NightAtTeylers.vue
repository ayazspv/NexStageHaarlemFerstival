<script setup lang="ts">
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
import { Festival, Game } from "../../../models";
import MobileFooter from "@/Pages/Components/Mobile/MobileFooter.vue";
import {route} from "../../../utils";

const props = defineProps<{
    festival: Festival,
    games?: Game[],
}>();
</script>

<template>
    <AppLayout :title="props.festival.name">
        <div class="mobile-wrapper">
            <div class="mobile-display">
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
                <MobileFooter :festival="props.festival"/>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');

* {
    font-family: "Fredoka", sans-serif;
}

.mobile-wrapper {
    display: flex;
    justify-content: center;
    height: 850px;
    width: 100%;
}

.mobile-logo {
    height: 50px;
    width: 50px;
}

.mobile-display {
    width: 420px;
    height: 100%;
    position: relative; /* Make room for the pseudo-element */
    display: flex;
    flex-direction: column;
    box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px, rgb(51, 51, 51) 0px 0px 0px 3px;
}

/* Use a pseudo-element for the background image with 50% opacity */
.mobile-display::before {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: url('/storage/main/background.png') no-repeat;
    background-position: 100% 0%;
    background-size: contain;
    opacity: 0.5;
    pointer-events: none; /* Allow clicks to pass through */
    z-index: -1; /* Place it behind the content */
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
