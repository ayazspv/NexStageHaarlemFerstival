<script lang="ts" setup>
import { Festival } from '../../../models';
import Navbar from '../Components/Navbar.vue';
import Footer from '../Festivals/Restaurants/Components/Footer.vue';
import { ref, onMounted, computed } from 'vue';

interface YummyData {
    header_text_1: string;
    header_text_2: string;
    line_1: string;
    line_2: string;
    button_text: string;
    header_image: string;
    description: string;
}

const yummyData = ref<YummyData | null>(null);
const error = ref<string | null>(null);
const foodTypes = ref<{ id: number; name: string }[]>([]);
const filter = ref<'all' | number>('all'); // 'all' or foodType id
const restaurants = ref<any[]>([]);

onMounted(async () => {
    try {
        const response = await fetch('/api/yummy-homepage');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data: YummyData = await response.json();
        yummyData.value = data;
        if (!yummyData.value) {
            throw new Error('No yummy data found');
        }
        console.log('Yummy data fetched successfully:', yummyData.value);
    } catch (err: unknown) {
        if (err instanceof Error) {
            error.value = err.message;
            console.error('Error fetching yummy data:', err);
        } else {
            error.value = String(err);
        }
    }

    try {
        const response = await fetch('/api/food-types');
        if (response.ok) {
            const types = await response.json();
            foodTypes.value = types;
        }
    } catch (err) {
        if (err instanceof Error) {
            error.value = err.message;
            console.error('Error fetching data:', err);
        } else {
            error.value = String(err);
        }
    }

    try {
        const response = await fetch('/api/restaurants');
        if (response.ok) {
            const data = await response.json();
            restaurants.value = data;
        } else {
            throw new Error(`Failed to fetch restaurants: ${response.status}`);
        }
    } catch (err) {
        if (err instanceof Error) {
            error.value = err.message;
            console.error('Error fetching restaurants:', err);
        } else {
            error.value = String(err);
        }
    }
});

function splitHeaderText(text?: string) {
    if (!text) return { first: '', rest: '' };
    const words = text.split(' ');
    return {
        first: words.slice(0, 2).join(' '),
        rest: words.slice(2).join(' ')
    };
}

const backgroundStyle = computed(() => {
    const imageUrl = yummyData.value?.header_image
        ? `/storage/main/yummy/${yummyData.value.header_image}`
        : '/storage/main/yummy/top-view-table-full-food.jpg';
    return {
        background: `linear-gradient(0deg, rgba(0, 0, 0, 0.60) 0%, rgba(0, 0, 0, 0.60) 100%), url('${imageUrl}') lightgray 50% / cover no-repeat`
    };
});

function selectFilter(value: 'all' | number) {
    filter.value = value;
}

function getStarArray(rate?: number) {
    const rounded = Math.round(rate ?? 0);
    return Array.from({ length: 5 }, (_, i) => i < rounded);
}

</script>
<template>
    <div class="header" :style="backgroundStyle">
        <Navbar class="yummy-navbar" />
        <div class="hero-content">
            <h1 class="element-1">{{ yummyData?.header_text_1 }}</h1>
            <div class="hero-title">
                <h1 class="element-2-1">{{ splitHeaderText(yummyData?.header_text_2).first }}</h1>
                <h1 class="element-2-2">{{ splitHeaderText(yummyData?.header_text_2).rest }}</h1>
            </div>
            <p>{{ yummyData?.line_1 }}</p>
            <p>{{ yummyData?.line_2 }}</p>
            <button class="btn btn-primary">{{ yummyData?.button_text }}</button>
        </div>
    </div>
    <div class="info-banner">
        <div class="info-content">
            <h2>How does it work?</h2>
            <p>{{ yummyData?.description }}</p>
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
                <button class="btn" :class="filter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="selectFilter('all')">
                    All
                </button>
                <button v-for="type in foodTypes" :key="type.id" class="btn"
                    :class="filter === type.id ? 'btn-primary' : 'btn-outline-primary'" @click="selectFilter(type.id)">
                    {{ type.name }}
                </button>
            </div>
        </div>
        <div class="restaurants">
            <div v-for="restaurant in (filter === 'all' ? restaurants : restaurants.filter(r => r.food_types?.some(ft => ft.id === filter)))"
                :key="restaurant.id" class="restaurant">
                <img :src="restaurant.picture_1 ? `/storage/main/yummy/${restaurant.picture_1}` : '/storage/main/yummy/top-view-table-full-food.jpg'"
                    :alt="restaurant.name">
                <h1><a :href="`/restaurants/${restaurant.id}`" style="color: inherit; text-decoration: none;">
            {{ restaurant.name }}
        </a></h1>
                <div class="stars">
                    <i v-for="(solid, idx) in getStarArray(restaurant.rate)" :key="idx"
                        :class="solid ? 'fa-solid fa-star' : 'fa-regular fa-star'"></i>
                </div>
                <div class="line"></div>
                <div class="food-type">
                    <p class="bold-part">food type:</p>
                    <p class="normal-part">{{restaurant.food_types?.map(ft => ft.name).join(', ') || '-'}}</p>
                </div>
                <div class="seats">
                    <p class="bold-part">seats:</p>
                    <p class="normal-part">{{ restaurant.seats }}</p>
                </div>
                <div class="line"></div>
                <div class="features">
                    <div v-if="restaurant.accessibility" class="feature-item">
                        <i class="fa-brands fa-accessible-icon"></i>
                        <p>Disability Accessible</p>
                    </div>
                    <div v-if="restaurant.vegan" class="feature-item">
                        <i class="fa-solid fa-seedling"></i>
                        <p>Vegan food available</p>
                    </div>
                    <div v-if="restaurant.gluten_free" class="feature-item">
                        <i class="fa-solid fa-wheat-awn-circle-exclamation"></i>
                        <p>Gluten Free food available</p>
                    </div>
                    <div v-if="restaurant.halal" class="feature-item">
                        <i class="fa-solid fa-star-and-crescent"></i>
                        <p>Halal food available</p>
                    </div>
                </div>
                <div class="line"></div>
                <div class="extra-information">
                    <p>📍</p>
                    <p>{{ restaurant.location }}</p>
                </div>
                <div class="extra-information">
                    <p>🕕</p>
                    <p>From {{ restaurant.session_1_time }}</p>
                </div>
            </div>
        </div>
    </div>
    <Footer />
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
    /*background: linear-gradient(0deg, rgba(0, 0, 0, 0.60) 0%, rgba(0, 0, 0, 0.60) 100%), url(../../../../storage/app/public/main/yummy/top-view-table-full-food.jpg) lightgray 50% / cover no-repeat;*/
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

:deep(.yummy-navbar .festival-dropdown a) {
    color: black !important;
}

:deep(.yummy-navbar .login-button) {
    border: 2px solid #fff;
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
    min-height: 175px;
    width: 100%;
    border-radius: 15px;
    border: 1px solid #000;
}

.restaurant h1 {
    color: #F8A219;
    font-family: Bayon;
    font-size: 30px;
    margin: 0;
}

.restaurant h1 a {
    color: inherit;
    text-decoration: none;
    font-size: 30px !important;
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
</style>