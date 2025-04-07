<script setup lang="ts">

import {Festival, Game} from "../../../../models";

defineProps<{
    festival: Festival,
    games: Game[],
}>();

const emit = defineEmits<{ (e: 'selectGame', game: Game): void }>();
</script>

<template>
    <div class="mobile-content">
        <div class="d-flex flex-row flex-wrap">
            <div class="mobile-box blue m-5 d-flex flex-column gap-5" v-for="(game, index) in games" :key="game.id" @click="emit('selectGame', game)">
                <h1 class="mt-3">
                    {{ (index as number) += 1 }}
                </h1>
                <h3>
                    Play
                </h3>
            </div>
        </div>
    </div>
</template>

<style scoped>
h1,h3 {
    color: white;
}

.mobile-content {
    flex: 1;
    overflow-y: auto;
}

.mobile-box {
    border-radius: 10px;
    border: 1px solid black;
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
</style>
