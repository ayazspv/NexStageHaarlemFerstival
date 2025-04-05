<script setup lang="ts">

import {Festival} from "../../../../models";
import {urlFriendly, route} from "../../../../utils"

const props = defineProps<{
    festival: Festival,
}>();

</script>

<template>
    <footer>
        <div class="d-flex flex-row mobile-footer">
            <a :href="route(festival.name, '')">
                <img src="/storage/main/home.png" alt="Home"/>
            </a>
            <a :href="route(festival.name, 'games')">
                <img src="/storage/main/game.png" alt="Home"/>
            </a>
            <a :href="route(festival.name, 'map')">
                <img src="/storage/main/map.png" alt="Home"/>
            </a>
            <a :href="route(festival.name, 'stamps')">
                <img src="/storage/main/image.png" alt="Home"/>
            </a>
        </div>
    </footer>
</template>

<style scoped>
footer {
    height: 60px;
    width: 100%;
}

.mobile-footer {
    display: flex;
    flex-direction: row;
    gap: 40px;
    align-items: center;
    justify-content: center;
    height: inherit;
    box-shadow: rgba(0, 0, 0, 0.02) 0px 1px 3px 0px, rgba(27, 31, 35, 0.15) 0px 0px 0px 1px;
}

.mobile-footer img {
    height: 40px;
    width: 40px;
}
</style>
