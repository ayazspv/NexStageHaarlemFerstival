<script lang="ts" setup>

import Navbar from '../../../Components/Navbar.vue';

</script>
<template>
    <div class="head d-flex pt-5 pb-5 flex-column align-items-center gap-5 align-self-stretch">
        <Navbar class="restaurant-navbar" />
        <div class="head-body d-flex flex-column align-items-center gap-5">
            <div class="label d-flex flex-column align-items-center">
                <h1 class="mb-0">Restaurant Name</h1>
                <div class="stars-section d-flex flex-row align-items-center gap-3 justify-content-center">
                    <div class="stars d-flex align-items-center gap-1">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    </div>
                    <p>Based on Google maps</p>
                </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg">
                Reserve Your Tickets Now!
            </button>

            <div class="info d-flex flex-column align-items-center gap-2 mb-5">
                <p>🗓️ 24 - 27 July 2025</p>
                <p>📍 Botermarkt 17, 2011 XL Haarlem</p>
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

.head {
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.76) 0%, rgba(0, 0, 0, 0.76) 100%), url(<path-to-image>) lightgray 50% / cover no-repeat;
}

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