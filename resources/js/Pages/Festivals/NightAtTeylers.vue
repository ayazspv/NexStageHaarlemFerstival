<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
import { Festival, Game } from "../../../models";
import Home from "../Festivals/NightAtTeylers/Home.vue";
import Games from "../Festivals/NightAtTeylers/Games.vue";
import Map from "../Festivals/NightAtTeylers/Map.vue";
import Stamps from "../Festivals/NightAtTeylers/Stamps.vue";
import Navbar from "../Festivals/NightAtTeylers/Components/Navbar.vue";
import GameQuestion from "@/Pages/Festivals/NightAtTeylers/GameQuestion.vue";

const props = defineProps<{
    festival: Festival,
    games?: Game[],
}>();

const currentView = ref('home');

const changeView = (view: string) => {
    currentView.value = view;
};

const selectedGame = ref<Game | null>(null);

const handleGameSelect = (game: Game) => {
    selectedGame.value = game;
    currentView.value = 'gamequestion';
};
</script>

<template>
    <AppLayout :festival="props.festival" :title="props.festival.name">
        <div class="mobile-wrapper mt-10">
            <div class="mobile-display">
                <Navbar :festival="props.festival" />

                <Home v-if="currentView === 'home'"
                      :festival="props.festival"
                      @changeView="changeView" />
                <Games
                    v-else-if="currentView === 'games'"
                    :festival="props.festival"
                    :games="props.games as Game[]"
                    @selectGame="handleGameSelect" />
                <Map v-else-if="currentView === 'map'" :festival="props.festival" />
                <Stamps v-else-if="currentView === 'stamps'" :games="games as Game[]" :festival="props.festival" />
                <GameQuestion
                    v-else-if="currentView === 'gamequestion'"
                    :festival="props.festival"
                    :game="selectedGame" />

                <footer>
                    <div class="d-flex flex-row mobile-footer">
                        <img src="/storage/main/home.png" alt="Home" @click="changeView('home')" :class="{ active: currentView === 'home' }" />
                        <img src="/storage/main/game.png" alt="Games" @click="changeView('games')" :class="{ active: currentView === 'games' }" />
                        <img src="/storage/main/map.png" alt="Map" @click="changeView('map')" :class="{ active: currentView === 'map' }" />
                        <img src="/storage/main/image.png" alt="Stamps" @click="changeView('stamps')" :class="{ active: currentView === 'stamps' }" />
                    </div>
                </footer>
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

.mobile-display {
    width: 420px;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px, rgb(51, 51, 51) 0px 0px 0px 3px;
}

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
    pointer-events: none;
    z-index: -1;
}

.mobile-display p {
    font-size: 22px;
}

.mobile-display footer {
    flex-shrink: 0;
    height: 60px;
    width: 100%;
}

.mobile-footer {
    display: flex;
    flex-direction: row;
    gap: 40px;
    align-items: center;
    justify-content: center;
    height: inherit;
    box-shadow: rgba(0, 0, 0, 0.02) 0px 1px 3px 0px, rgba(27, 31, 35, 0.15) 0px 0px 0px 1px;
}

.mobile-footer img {
    height: 40px;
    width: 40px;
    cursor: pointer;
}
</style>
