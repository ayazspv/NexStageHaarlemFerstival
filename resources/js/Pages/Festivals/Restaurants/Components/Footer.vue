<script lang="ts" setup>

</script>
<template>
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
            <img src="../../../../../../storage/app/public/main/logo.png" alt="haarlem-logo">
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

.festivals,
.links {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    gap: 10px;
}

.festivals h4,
.links h4 {
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

.links-item a,
.festivals a {
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