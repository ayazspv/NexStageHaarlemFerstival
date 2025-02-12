<script setup lang="ts">
</script>

<template>
    <div class="navbar-container">
        <div class="navbar-options">
            <div class="navbar-option">

            </div>
            <div class="navbar-option">
                <a href="/">Home</a>
            </div>
            <div class="navbar-option float-right">
                <a class="employee" href="/Login">
                    <i class="bx bx-user"></i>
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Importing Google Fonts and Boxicons */
@import "https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap";
@import "https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css";

.navbar-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center; /* Center content horizontally */
    top: 0;
    width: 100%;
    height: 70px;
    background-color: #fff;
    border-bottom: solid 1px rgba(204, 204, 204, 255);
    text-decoration: none;
}

.navbar-options {
    display: flex;
    flex-direction: row;
    justify-content: space-between; /* Ensures equal spacing */
    width: 100%;
    gap: 10px;
    height: 100%;
    align-items: center;
}

.navbar-option {
    flex: 1; /* Each item takes equal width */
    max-width: calc(100% / 3); /* 1/3 of the navbar width */
    text-align: center; /* Centers text inside */
    border: 1px solid red; /* Debugging */
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
