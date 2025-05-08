<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "../Components/Navbar.vue";
import Footer from "../Components/Footer.vue";

defineProps<{
    title?: string;
}>();
</script>

<template>
    <Head :title="title" />
    <div class="layout-container">
        <Navbar />
        <main class="main-content">
            <slot />
        </main>
        <Footer />
    </div>
</template>

<style scoped>
/* Root container for the layout */
.layout-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh; /* Ensure the layout spans the full viewport height */
    text-decoration: none;
}

/* Main content area that grows to fill remaining space */
.main-content {
    flex: 1; /* Allow this section to grow and fill available space */
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
}
</style>
