<script setup lang="ts">

import {Festival} from "../../../../../models";

defineProps<{
    festival: Festival,
}>();

</script>

<template>
    <div class="d-flex flex-row justify-content-between align-items-center mt-4">
        <img class="mobile-logo" src="/storage/main/arrow-left.png" alt="Arrow left">
        <h3 style="text-align: center">{{ festival.name }}</h3>
        <img class="mobile-logo" src="/storage/main/logo.png" alt="Logo">
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');

* {
    font-family: "Fredoka", sans-serif;
}

.mobile-logo {
    height: 50px;
    width: 50px;
}

.mobile-display p {
    font-size: 22px;
}

.mobile-display footer {
    flex-shrink: 0;
    height: 60px;
    width: 100%;
}

.mobile-box img {
    width: 80%;
}

.mobile-box h3 {
    color: black;
}

</style>
