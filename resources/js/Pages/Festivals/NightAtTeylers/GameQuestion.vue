<script setup lang="ts">
import { ref } from 'vue';
import { Festival, Game } from "../../../../models";

const props = defineProps<{
    festival: Festival,
    game: Game,
}>();

const hintVisible = ref(false);
const overlayMessage = ref('');

const answer = (selectedAnswer: number) => {
    if (props.game.correct_option === selectedAnswer) {
        localStorage.setItem(`game_${props.game.id}`, 'success');
        overlayMessage.value = "You have successfully answered the question!";
    } else {
        overlayMessage.value = "Sorry, that's incorrect. Try again!";
    }
    hintVisible.value = true;
};

const showHint = () => {
    overlayMessage.value = props.game.hint;
    hintVisible.value = true;
};
</script>

<template>
    <div class="mobile-content">
        <div class="d-flex flex-column gap-3 align-items-center">
            <div class="mt-3">
                <h3><b>{{ game.title }}</b></h3>
            </div>
            <div class="question-image">
                <div class="d-flex flex-column w-100 align-items-end" @click="showHint">
                    <img class="hint-icon" src="/storage/main/hint.png" alt="Hint">
                </div>
                <img :src="`/storage/${game.thumbnail}`" alt="Question image">
            </div>
            <div>
                <p>{{ game.question }}</p>
            </div>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex flex-row gap-5 options">
                    <button @click="answer(1)">{{ game.option1 }}</button>
                    <button @click="answer(2)">{{ game.option2 }}</button>
                </div>
                <div class="d-flex flex-row gap-5 options">
                    <button @click="answer(3)">{{ game.option3 }}</button>
                    <button @click="answer(4)">{{ game.option4 }}</button>
                </div>
            </div>
        </div>
        <!-- Overlay box for hints or success messages -->
        <div v-if="hintVisible" class="hint-overlay" @click="hintVisible = false">
            <div class="hint-box">
                {{ overlayMessage }}
            </div>
        </div>
    </div>
</template>

<style scoped>
.mobile-content {
    flex: 1;
    overflow-y: auto;
    position: relative;
}

.question-image {
    width: 250px;
    height: 250px;
}

.question-image img {
    border-radius: 10px;
    border: 5px solid #37A9E2;
}

.options button {
    background-color: #FFCC01;
    border-radius: 10px;
    color: black;
    width: 170px;
    height: 50px;
}

.options button:hover {
    transition-property: width;
    transition-duration: 2s;
    transition-timing-function: linear;
    transition-delay: 1s;
    background-color: #37A9E2;
}

.hint-icon {
    width: 40px;
    height: 40px;
    border: none !important;
}

.hint-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.hint-box {
    background-color: #FFCC01;
    padding: 20px;
    border-radius: 8px;
    font-size: 1.2em;
    text-align: center;
    border: 2px solid black;
    margin: 10px;
}
</style>
