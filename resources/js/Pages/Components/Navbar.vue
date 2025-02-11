<script setup lang="ts">
</script>

<template>
    <div class="navbar-container">
        <div class="navbar-options">
            <a href="/">Home</a>
            <a href="/Gallery">Gallery</a>
            <a href="/Services">Services</a>
            <a href="/About">About</a>

            <div class="book">
                <a href="/Book">Book Now</a>
            </div>

            <a class="employee" href="/Login">
                <i class='bx bx-user bx-sm'></i>
            </a>
        </div>
    </div>
</template>

<style scoped>
.navbar-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    top: 0;
    width: 100%;
    height: 70px;
    background-color: #fff;

    border-bottom: solid 1px rgba(204,204,204,255);
    text-decoration: none;
}

.navbar-options {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    justify-content: center;
    width: 100%;
    gap: 10px;
}

.book {
    width: 100px;
    height: 25px;
    background: #2565c7;
    border-radius: 10px;
    text-align: center;
}

.book a {
    color: #fff !important;
}

.employee {
    position: absolute;
    margin-left: 90%;
}
</style>
