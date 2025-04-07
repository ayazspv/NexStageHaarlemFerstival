<script setup lang="ts">
import { Festival, Game } from "../../../../models";

defineProps<{
  festival: Festival,
  games: Game[],
}>();

const isSolved = (gameId: number): boolean => {
  return localStorage.getItem(`game_${gameId}`) === 'success';
};
</script>

<template>
  <div class="mobile-content">
    <div class="d-flex flex-row flex-wrap align-items-center justify-content-center gap-5 mt-5">
      <div class="stamp-box" v-for="game in games" :key="game.id">
        <div v-if="isSolved(game.id)" class="d-flex flex-column align-items-center">
          <img
              :src="`/storage/${game.stamp}`"
              alt="Stamp"
          />
          <h5>{{ game.title }}</h5>
        </div>
        <div v-else class="d-flex flex-column align-items-center">
          <img
              src="/storage/main/question.png"
              alt="Not solved"
          />
          <h5>???</h5>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.mobile-content {
  flex: 1;
  overflow-y: auto;
}

.stamp-box {
  width: 150px;
  height: 220px;
  background-color: #F9E593;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stamp-box img {
  max-width: 120px;
  max-height: 120px;
}
</style>
