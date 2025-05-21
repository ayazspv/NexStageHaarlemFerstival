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
</style>