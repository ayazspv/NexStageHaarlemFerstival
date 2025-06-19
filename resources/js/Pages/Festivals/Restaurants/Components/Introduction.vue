<script lang="ts" setup>

import { defineProps } from 'vue';

const props = defineProps({
    restaurant: Object
});

</script>
<template>
    <div class="introduction d-flex pt-5 pb-5 flex-column gap-1 align-items-center align-self-stretch">
        <h2>Welcome To Restaurant</h2>
        <p>{{ props.restaurant?.description }}</p>
        <div class="infos d-flex pt-1 pb-2 justify-content-center align-items-start align-content-start gap-3 flex-wrap mt-5">
            <div v-if="restaurant.accessibility" class="info d-flex p-4 align-items-center gap-3">
                <i class="fa-brands fa-accessible-icon"></i>
                <p>Disability Accessability</p>
            </div>
            <div v-if="restaurant.vegan" class="info d-flex p-4 align-items-center gap-3">
                <i class="fa-solid fa-seedling"></i>
                <p>Vegan food is available</p>
            </div>
            <div v-if="restaurant.gluten_free" class="info d-flex p-4 align-items-center gap-3">
                <i class="fa-solid fa-wheat-awn-circle-exclamation"></i>
                <p>Gluten free food is available</p>
            </div>
            <div v-if="restaurant.halal" class="info d-flex p-4 align-items-center gap-3">
                <i class="fa-solid fa-star-and-crescent"></i>
                <p>Halal food is available</p>
            </div>
        </div>
    </div>
</template>
<style scoped>
.introduction {
    background: #F0F0F0;
}

.introduction>h2 {
    color: #F8A219;
    text-align: center;
    text-shadow: 0px 0px 35px rgba(248, 162, 25, 0.35);
    font-family: Bayon;
    font-size: 75px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.introduction>p {
    color: #000;
    text-align: center;
    font-family: NotoSansTamil;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
    width:  70%;
}

.infos {
    width: 80%;
}

.info {
    border-radius: 20px;
    border: 2px solid #000;
    width: 40%;
}

.info>i {
    font-size: 30px;
    color: #000;
}   

.info>p {
    margin: 0 !important;
}
</style>