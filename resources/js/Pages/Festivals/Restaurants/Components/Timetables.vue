<script lang="ts" setup>

import { defineProps, computed } from 'vue';

const props = defineProps({
    restaurant: Object
});

</script>
<template>
    <div class="container timetables d-flex justify-content-end align-items-center gap-5 align-self-stretch">
        <div class="picture me-5">

        </div>
        <div class="content d-flex flex-column align-items-start
        gap-3">
            <div class="openinghours d-flex flex-column align-items-start gap-3">
                <h2>⏰ Opening Hours</h2>
                <div class="schedule d-flex flex-row align-items-center gap-4 pt-1">
                    <p>24 - 27 July</p>
                    <div class="line"></div>
                    <div class="times">
                        <p v-if="restaurant.session_1_time">{{ restaurant.session_1_time }}</p>
                        <p v-if="restaurant.session_2_time">{{ restaurant.session_2_time }}</p>
                        <p v-if="restaurant.session_3_time">{{ restaurant.session_3_time }}</p>
                    </div>
                    <div class="line"></div>
                    <div class="sessions">
                        <p>session 1</p>
                        <p>session 2</p>
                        <p>session 3</p>
                    </div>
                </div>
            </div>
            <div class="foods d-flex flex-column align-items-start gap-1">
                <p>✅ Available foods</p>
                <ul class="available-foods d-flex flex-column align-itmes-center">
                    <li>Food item 1</li>
                    <li>Food item 2</li>
                    <li>Food item 3</li>
                    <li>Food item 4</li>
                </ul>
            </div>
            <div class="prices">
                <p>🏷️ prices</p>
                <ul class="price d-flex flex-column align-itmes-center">
                    <li>Adult: €{{ restaurant.adult_price }}</li>
                    <li>Childeren (under12): €{{ restaurant.children_price }}</li>
                </ul>
            </div>
            <p class="caution align-self-stretch">CAUTION: Reservation is mandatory. A reservation fee of 10,00 per
                person will be charged when a reservation is made on the Haarlem Festival site. This fee will be
                deducted from the final check on visiting the restaurant.</p>
        </div>
    </div>
</template>
<style scoped>
.timetables {
    padding: 100px 0;
}

.picture {
    width: 110%;
    height: 600px;
    background-color: #000;
    border-radius: 15px;
    background: url(<path-to-image>) lightgray 50% / cover no-repeat;
    box-shadow: 0px 0px 100px 12px rgba(0, 0, 0, 0.35);
}

.openinghours>h2,
.foods>p,
.prices>p {
    color: #F8A219;
    text-shadow: 0px 0px 35px rgba(248, 162, 25, 0.35);
    font-family: Bayon;
    font-size: 40px;
    font-style: normal;
    font-weight: 400;
}

.schedule>p {
    color: #000;
    font-family: Bayon;
    font-size: 40px;
    font-style: normal;
    font-weight: 400;
}

.line {
    width: 2.5px;
    height: 80px;
    background: #000;
}

.times>p,
.sessions>p,
.available-foods>li,
.price>li {
    color: #000;
    font-family: Bayon;
    font-size: 20px;
    font-style: normal;
    font-weight: 400;
    margin: 0;
}

.caution {
    color: #F00;
    text-shadow: 0px 0px 15px rgba(255, 0, 0, 0.15);
    font-family: NotoSansTamil;
    font-size: 16px;
    font-style: normal;
    font-weight: 900;
    line-height: normal;
}
</style>