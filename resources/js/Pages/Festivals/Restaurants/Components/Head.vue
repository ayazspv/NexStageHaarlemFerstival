<script lang="ts" setup>

import Navbar from '../../../Components/Navbar.vue';
import { defineProps, computed } from 'vue';

const props = defineProps({
    restaurant: Object
});

const backgroundStyle = computed(() => {
    const imageUrl = props.restaurant?.picture_1
        ? `/storage/main/yummy/${props.restaurant.picture_1}`
        : '/storage/main/yummy/top-view-table-full-food.jpg';
    return {
        background: `linear-gradient(0deg, rgba(0, 0, 0, 0.60) 0%, rgba(0, 0, 0, 0.60) 100%), url('${imageUrl}') lightgray 50% / cover no-repeat`
    };
});

function getStarArray(rate?: number) {
    const rounded = Math.round(rate ?? 0);
    return Array.from({ length: 5 }, (_, i) => i < rounded);
}



</script>
<template>
    <div class="head d-flex pt-5 pb-5 flex-column align-items-center gap-5 align-self-stretch" :style="backgroundStyle">
        <Navbar class="restaurant-navbar" />
        <div class="head-body d-flex flex-column align-items-center gap-5">
            <div class="label d-flex flex-column align-items-center">
                <h1 class="mb-0">{{ restaurant.name }}</h1>
                <div class="stars-section d-flex flex-row align-items-center gap-3 justify-content-center">
                    <div class="stars d-flex align-items-center gap-1">
                        <i v-for="(solid, idx) in getStarArray(restaurant.rate)" :key="idx"
                        :class="solid ? 'fa-solid fa-star' : 'fa-regular fa-star'"></i>
                    </div>
                    <p>Based on Google maps</p>
                </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg">
                {{ restaurant.cta_text || 'Make a reservation' }}
            </button>

            <div class="info d-flex flex-column align-items-center gap-2 mb-5">
                <p>{{ restaurant.subheader_1 }}</p>
                <p>{{ restaurant.subheader_2 }}</p>
            </div>
        </div>
    </div>
</template>
<style scoped>
:deep(.restaurant-navbar) {
    background: transparent !important;
    border-bottom: none !important;
    box-shadow: none !important;
}

:deep(.restaurant-navbar a),
:deep(.restaurant-navbar span),
:deep(.restaurant-navbar i) {
    color: white;
}

:deep(.restaurant-navbar .festival-dropdown a) {
    color: black !important;
}

:deep(.restaurant-navbar .login-button) {
    border: 2px solid #fff;
}

/* .head {
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.76) 0%, rgba(0, 0, 0, 0.76) 100%), url(<path-to-image>) lightgray 50% / cover no-repeat;
} */

.label>h1 {
    color: #FFF;
    text-align: center;
    font-family: Bayon;
    font-size: 100px;
    font-style: normal;
    font-weight: 400;
    /* 83.333% */
}

.stars>i {
    color: #fff;
    font-size: 35px;
}

.stars-section>p {
    color: rgba(255, 255, 255, 0.65);
    text-align: center;
    font-family: NotoSansTamil;
    font-size: 16px;
    font-style: italic;
    font-weight: 400;
    margin: 0 !important;
}

.info>p {
    color: #FFF;
    text-align: center;
    font-family: Bayon;
    font-size: 25px;
    font-style: normal;
    font-weight: 400;
}

.head-body button {
    background-color: #EC9A29;
    border: none;
    font-size: 16px;
    padding: 15px 30px;
}
</style>