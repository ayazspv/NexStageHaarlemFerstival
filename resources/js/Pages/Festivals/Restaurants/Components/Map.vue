<script lang="ts" setup>

</script>
<template>
    <div class="map-section d-flex p-5 flex-column align-items-center gap align-self-stretch">
        <h2>Where are we located?</h2>
        <p>Here below is a simple map of our location</p>
        <div class="map-content d-flex flex-column align-items-start gap-2 w-75 mt-5">
            <div class="map mb-3">

            </div>
            <div class="location d-flex align-items-center gap-3">
                <i class="fa fa-location"></i>
                <p>Botermarkt 17 2011XL Haarlem</p>
            </div>
            <div class="phone d-flex align-items-center gap-3">
                <i class="fa fa-phone"></i>
                <p>+31 6 12345678</p>
            </div>
        </div>
    </div>
</template>
<style scoped>
p {
    margin: 0;
}

.map-section {
    background: #FAFAFA;
}

.map-section>h2 {
    color: #F8A219;
    text-align: center;
    text-shadow: 0px 0px 35px rgba(248, 162, 25, 0.35);
    font-family: Bayon;
    font-size: 70px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.map-section>p {
    color: #000;
    font-family: NotoSansTamil;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.map {
    width: 100%;
    height: 600px;
    background-color: #000;
    border-radius: 15px;
}

.map-content p {
    color: #000;
    font-family: Bayon;
    font-size: 24px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.map-content i {
    color: #000;
    font-size: 24px;
}
</style>