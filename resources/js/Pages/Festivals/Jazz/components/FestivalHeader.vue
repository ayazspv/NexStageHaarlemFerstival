<script setup lang="ts">
import { Festival } from "../../../../../models";

defineProps<{
    festival: Festival;
}>();
</script>

<template>
    <section class="jazz-hero-banner" :style="`background-image: url('/storage/${festival.image_path}')`">
        <div class="overlay"></div>
        <div class="jazz-title-container">
            <h1 class="jazz-hero-title">{{ festival.name }}</h1>
        </div>
    </section>
</template>

<style scoped>
.jazz-hero-banner {
    position: relative;
    width: 100%;
    height: 500px;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
}

.jazz-title-container {
    position: relative;
    z-index: 2;
    padding: 20px 100px;
    background-color: rgba(255, 99, 87, 0.9);
    display: inline-block;
    transform: translateY(200px);
}

.jazz-hero-title {
    margin: 0;
    color: white;
    font-size: 3.5rem;
    font-weight: 700;
    text-align: center;
    letter-spacing: 2px;
}

@media (max-width: 768px) {
    .jazz-hero-banner {
        height: 300px;
    }
    
    .jazz-title-container {
        padding: 15px 50px;
    }
    
    .jazz-hero-title {
        font-size: 2.5rem;
    }
}
</style>