<script setup lang="ts">
import {Head} from "@inertiajs/vue3";
import Sidebar from "../Components/Sidebar.vue";
import Footer from "../Components/Footer.vue"
import Navbar from "../Components/Navbar.vue";

defineProps<{
    title?: string,
}>();
</script>

<template>
    <Head :title="title" />

    <div class="container-scroller">
        <div class="layout-container">
            <Sidebar/>

            <section class="main-content">
                <div class="content-wrapper">
                    <Navbar/>
                    <div class="page-content">
                        <slot/>
                    </div>
                    <Footer/>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.layout-container {
    display: flex;
    height: 100vh; /* Full viewport height */
}

.main-content {
    flex-grow: 1; /* Takes up remaining space */
    overflow-y: auto; /* Enables vertical scrolling if content overflows */
    background-color: #fff; /* Optional background color for main content */
    transition: margin-left 0.5s ease; /* Smooth transition for any layout changes */
    height: 100%;
}

.content-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

.page-content {
    flex: 1 0 auto;
    padding-bottom: 2rem; /* Space before footer */
}
</style>
