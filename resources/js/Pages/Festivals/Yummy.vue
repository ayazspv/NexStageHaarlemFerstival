<script lang="ts" setup>
import { Festival } from '../../../models';
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
        <h2>
            You may like one of the these amazing restaurants:
        </h2>
        <div class="filters">
            <p>
                Restaurants Filter:
            </p>
            <div class="filter-items">
                <button class="btn btn-primary">All</button>
                <button class="btn btn-outline-primary">French</button>
            </div>
        </div>
        <div class="restaurants">
            <div class="restaurant">
                <img src="../../../../storage/app/public/main/yummy/top-view-table-full-food.jpg" alt="">
                <h1>Cafe de Roemer</h1>
                <div class="stars">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                </div>
                <div class="line"></div>
                <div class="food-type">
                    <p class="bold-part">food type:</p>
                    <p class="normal-part">French</p>
                </div>
                <div class="seats">
                    <p class="bold-part">seats:</p>
                    <p class="normal-part">2</p>
                </div>
                <div class="line"></div>
                <div class="features">
                    <div class="feature-item">
                        <i class="fa-brands fa-accessible-icon"></i>
                        <p>Feature 1</p>
                    </div>
                    <div class="feature-item">
                        <i class="fa-brands fa-accessible-icon"></i>
                        <p>Feature 1</p>
                    </div>
                    <div class="feature-item">
                        <i class="fa-brands fa-accessible-icon"></i>
                        <p>Feature 1</p>
                    </div>
                </div>
                <div class="line"></div>
                <div class="extra-information">
                    <p>📍</p>
                    <p>Botermarkt 17, 2011 XL Haarlem</p>
                </div>
                <div class="extra-information">
                    <p>🕕</p>
                    <p>From 18:00</p>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="top-footer">
            <p>Join our newsletter to keep up to date with us!</p>
            <form action="" class="newsletter">
                <input type="text" placeholder="Enter Your Email" class="form-control">
                <button class="btn btn-primary">Submit!</button>
            </form>
        </div>
        <div class="footer-line"></div>
        <div class="middle-footer">
            <img src="../../../../storage/app/public/main/logo.png" alt="haarlem-logo">
            <div class="navigation">
                <div class="festivals">
                    <h4>Festivals</h4>
                    <a href="#">Yummy</a>
                    <a href="#">Jazz</a>
                    <a href="#">Magic@Tylers</a>
                    <a href="#">History</a>
                </div>
                <div class="links">
                    <h4>Useful Links</h4>
                    <div class="links-item">
                        <i class="fa-brands fa-square-instagram"></i>
                        <a href="#">Instagram</a>
                    </div>
                    <div class="links-item">
                        <i class="fa-brands fa-square-youtube"></i>
                        <a href="#">YouTube</a>
                    </div>
                    <div class="links-item">
                        <i class="fa-brands fa-square-facebook"></i>
                        <a href="#">Facebook</a>
                    </div>
                    <div class="links-item">
                        <i class="fa-brands fa-square-x-twitter"></i>
                        <a href="#">X (Twitter)</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-line"></div>
        <p class="bottom-footer">©️ 2024 VisitHaarlem | All rights reserved</p>
    </div>
</template>
<style scoped>
p,
h1,
h2,
h3,
h4,
h5,
h6 {
    margin: 0;
}

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
    margin-bottom: 1rem;
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

.restaurants-list {
    display: flex;
    padding: 50px 0px;
    flex-direction: column;
    align-items: center;
    gap: 25px;
    align-self: stretch;
    background: linear-gradient(0deg, rgba(233, 233, 233, 0.95) 0%, rgba(233, 233, 233, 0.95) 100%), url(../../../../storage/app/public/main/yummy/top-view-pasta-waffles-with-copy-space.jpg) lightgray 50% / cover no-repeat;
}

.restaurants-list h2 {
    color: #000;
    text-align: center;
    font-family: Bayon;
    font-size: 50px;
}

.filters {
    display: flex;
    width: 100%;
    justify-content: space-between;
    padding: 0 200px;
}

.filters p {
    color: #000;
    text-align: center;
    font-family: NotoSansTamil;
    font-size: 25px;
}

.filter-items {
    display: flex;
    align-items: center;
    gap: 15px;
}

.restaurants {
    display: flex;
    width: 100%;
    align-items: flex-start;
    align-content: flex-start;
    gap: 50px 50px;
    flex-wrap: wrap;
    justify-content: space-around;
}

.restaurant {
    display: flex;
    width: 375px;
    padding: 25px;
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
    border-radius: 15px;
    border: 1px solid #ABABAB;
    background: rgba(255, 255, 255, 0.38);
    box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(10px);
}

.restaurant img {
    max-height: 250px;
    border-radius: 15px;
    border: 1px solid #000;
}

.restaurant h1 {
    color: #F8A219;
    font-family: Bayon;
    font-size: 30px;
    margin: 0;
}

.stars {
    display: flex;
    align-items: flex-start;
}

.stars i {
    color: #F8A219;
    font-size: 20px;
}

.line {
    width: 100%;
    height: 2px;
    background: #F8A219;
}

.food-type,
.seats {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 5px;
}

.bold-part {
    color: #000;
    font-family: Bayon !important;
    font-size: 18px !important;
}

.normal-part {
    color: #000;
    font-family: NotoSansTamil;
    font-size: 14px;
}

.features {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.feature-item {
    display: flex;
    align-items: center;
    flex-direction: row;
    gap: 10px;
}

.feature-item i {
    font-size: 25px;
}

.restaurant p {
    color: #000;
    font-family: NotoSansTamil;
    font-size: 14px;
}

.extra-information {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 5px;
}

.footer {
    display: flex;
    width: 100%;
    padding: 25px 0px;
    flex-direction: column;
    align-items: center;
    gap: 25px;
    border-bottom: 10px solid #EC9A29;
    background: #EEE;
}

.top-footer {
    display: flex;
    width: 100%;
    padding: 0px 150px;
    justify-content: space-between;
    align-items: center;
}

.top-footer p {
    color: #000;
    font-family: NotoSansTamil;
    font-size: 21px;
}

.newsletter {
    display: flex;
    width: 400px;
    justify-content: space-between;
    align-items: center;
    gap: 5px;
}

.newsletter input {
    padding: 10px 20px;
    border-radius: 35px;
    border: 2px solid #EC9A29;
}

.newsletter button {
    padding: 10px 20px;
    box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.25);
    border-radius: 35px;
    background: #EC9A29;
    border: none;
}

.footer-line {
    width: 90%;
height: 2.5px;
background: #D0D0D0;
}

.middle-footer {
    display: flex;
    width: 100%;
    padding: 0px 150px;
    justify-content: space-between;
    align-items: center;
}

.navigation {
    display: flex;
justify-content: center;
align-items: center;
gap: 100px;
}

.festivals, .links {
    display: flex;
flex-direction: column;
justify-content: center;
align-items: flex-start;
gap: 10px;
}

.festivals h4, .links h4 {
    color: #EC9A29;
    font-family: Bayon;
    font-size: 30px;
}

.links-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.links-item i {
    color: #000;
    border-radius: 100%;
    font-size: 20px;
}

.links-item a, .festivals a {
    color: #000;
    font-family: NotoSansTamil;
    font-size: 16px !important;
}

.bottom-footer {
    color: #000;
font-family: NotoSansTamil;
font-size: 16px;
}
</style>