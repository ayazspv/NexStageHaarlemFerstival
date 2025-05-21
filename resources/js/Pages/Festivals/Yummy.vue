<script lang="ts" setup>
import { usePage } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue';
import { Festival } from '../../../models';
import { ref, computed } from 'vue';
import Navbar from '../Components/Navbar.vue';

const props = defineProps<{
    festival: Festival;
    bands?: any[];
}>();

</script>
<template>
    <div class="header">
        <Navbar class="yummy-navbar" />
        <div class="hero-content">
            <h1 class="element-1">YUMMY!</h1>
            <div class="hero-title">
                <h1 class="element-2-1">FOOD FESTIVAL</h1>
                <h1 class="element-2-2">HAARLEM 2025</h1>
            </div>
            <p>🗓️ Thursday 24 July - Sunday 27 July</p>
            <p>📍 Haarlem, The Netherlands</p>
            <button class="btn btn-primary">Check the restaurants</button>
        </div>
    </div>
    <div class="info-banner">
        <div class="info-content">
            <h2>How does it work?</h2>
            <p>
                Curious about the dining options available at Haarlem Culinary 2025? Explore the delectable offerings
                from a selection of renowned Haarlem restaurants this year. Indulge your palate in a culinary journey
                with these exquisite dining experiences.
            </p>
        </div>
        <div class="info-process">
            <img src="../../../../storage/app/public/main/yummy/process.png" alt="process" class="process-shapes">
            <div class="process-text">
                <div class="process-item">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <p>SEARCH RESTAURANT</p>
                </div>
                <div class="process-item">
                    <i class="fa-solid fa-calendar-check"></i>
                    <p>RESERVE YOUR TABLE</p>
                </div>
                <div class="process-item">
                    <i class="fa-solid fa-utensils"></i>
                    <p>ENJOY YOUR FOOD</p>
                </div>
            </div>
        </div>
    </div>
    <div class="restaurants-list">

    </div>
    <div class="footer">

    </div>
</template>
<style scoped>
.header {
    display: flex;
    padding: 25px 0px 150px 0px;
    flex-direction: column;
    align-items: center;
    gap: 100px;
    align-self: stretch;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.60) 0%, rgba(0, 0, 0, 0.60) 100%), url(../../../../storage/app/public/main/yummy/top-view-table-full-food.jpg) lightgray 50% / cover no-repeat;
}

:deep(.yummy-navbar) {
    background: transparent !important;
    border-bottom: none !important;
}

:deep(.yummy-navbar a),
:deep(.yummy-navbar span),
:deep(.yummy-navbar i) {
    color: white !important;
}

.hero-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0px;
}

.hero-title {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 15px;
}

.element-1,
.element-2-1 {
    color: #FFF;
    text-align: center;
    font-family: Bayon;
    font-size: 80px;
    font-style: normal;
    font-weight: 400;
    line-height: 100px;
    /* 125% */
}

.element-2-2 {
    color: #EC9A29;
    font-family: Bayon;
    font-size: 80px;
    font-style: normal;
    font-weight: 400;
    line-height: 100px;
}

.hero-content p {
    color: #FFF;
    text-align: center;
    font-family: NotoSansTamil;
    font-size: 16px;
    font-style: normal;
    font-weight: 500;
    line-height: normal;
}

.hero-content button {
    width: 250px;
    height: 50px;
    filter: drop-shadow(0px 0px 15px rgba(0, 0, 0, 0.50));
    background-color: #EC9A29;
    border: none;
}

.info-banner {
    display: flex;
    padding: 62px 50px;
    justify-content: center;
    align-items: flex-start;
    justify-content: space-evenly;
    align-self: stretch;
    background: #EEE;
}

.info-content {
    display: flex;
    width: 625px;
    flex-direction: column;
    align-items: flex-start;
}

.info-content h2 {
    color: #EC9A29;
    font-family: Bayon;
    font-size: 50px;
}

.info-content p {
    color: #000;
    font-family: NotoSansTamil;
    font-size: 20px;
    text-align: justify;
}

.info-process {
    display: flex;
    align-items: flex-start;
    gap: 35px;
}

.process-shapes {
    width: 40px;
    margin-top: 7px;
}

.process-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 40px;
}

.process-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.process-item i {
    color: #EC9A29;
    font-size: 40px;
}

.process-item p {
    color: #EC9A29;
    font-family: Bayon;
    font-size: 40px;
    margin: 0;
}
</style>