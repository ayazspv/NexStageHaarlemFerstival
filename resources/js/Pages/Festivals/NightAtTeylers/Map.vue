<script setup lang="ts">

</script>

<template>
    <div class="mobile-content">
        <div class="map-container">
            <img src="/storage/main/teyler_map.png" alt="Map">
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');

* {
    font-family: "Fredoka", sans-serif;
}

.mobile-display p {
    font-size: 22px;
}

.mobile-content {
    flex: 1;
    overflow-y: auto;
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

.map-container {
    margin: 20px;
    border: 2px solid #A9D7ED;
}
</style>
