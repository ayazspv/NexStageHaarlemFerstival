<script lang="ts" setup>

</script>
<template>
    <div class="gallery d-flex flex-column align-items-center gap-2 align-self-center p-5 w-100">
        <h2 class="align-self-stretch">Gallery</h2>
        <div class="content container d-flex justify-content-end align-items-center gap-5 align-self-stretch">
            <div class="picture">

            </div>
            <div class="picture">

            </div>
            <div class="picture">

            </div>
        </div>
    </div>
</template>
<style scoped>
.gallery {
    background: #292929;
}

.gallery>h2 {
    color: #FFF;
    font-family: Bayon;
    font-size: 75px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

label {
    color: #FFF;
    font-family: NotoSansTamil;
    font-size: 16px;
    font-style: normal;
    font-weight: 700;
    line-height: normal;
}

.picture {
    width: 60%;
    height: 600px;
    border-radius: 5px;
    background: url(<path-to-image>) lightgray 50% / cover no-repeat;
}

input {
    width: 100%;
}
</style>