<script lang="ts" setup>

</script>
<template>
    <div class="reservation d-flex flex-column align-items-center gap-2 align-self-center p-5 w-100">
        <h2 class="align-self-stretch">Reservation</h2>
        <div class="content container d-flex justify-content-end align-items-center gap-5 align-self-stretch">
            <div class="form w-50">
                <form class="d-flex flex-column align-items-start gap-2 w-100">
                    <div class="mb-3 w-100">
                        <label for="name" class="form-label">Your Name*</label>
                        <input type="text" class="form-control" id="name" required />
                    </div>
                    <div class="persons d-flex gap-3 w-100">
                        <div class="mb-3 w-100">
                            <label for="adults" class="form-label">Persons (Adults)*</label>
                            <input type="number" class="form-control" id="adults" min="1" required />
                        </div>
                        <div class="mb-3 w-100">
                            <label for="children" class="form-label">Persons (Children)*</label>
                            <input type="number" class="form-control" id="children" min="0" required />
                        </div>
                    </div>
                    <div class="date-sessions d-flex gap-3 w-100">
                        <div class="mb-3 w-100">
                            <label for="date" class="form-label">Select Date*</label>
                            <input type="date" class="form-control" id="date" required />
                        </div>
                        <div class="mb-3 w-100">
                            <label for="session" class="form-label">Select Sessions*</label>
                            <select class="form-control" id="session" required>
                                <option value="">Select a session</option>
                                <option value="lunch">Lunch</option>
                                <option value="dinner">Dinner</option>
                            </select>
                        </div>
                    </div>
                    <div class="contact d-flex gap-3 w-100">
                        <div class="mb-3 w-100">
                            <label for="phone" class="form-label">Phone*</label>
                            <input type="tel" class="form-control" id="phone" required />
                        </div>
                        <div class="mb-3 w-100">
                            <label for="email" class="form-label">Email (Optional)</label>
                            <input type="email" class="form-control" id="email" />
                        </div>
                    </div>
                    <div class="mb-3 w-100">
                        <label for="request" class="form-label">Special Request (Optional)</label>
                        <textarea class="form-control" id="request" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Make Reservation</button>
                </form>
            </div>
            <div class="picture">

            </div>
        </div>
    </div>
</template>
<style scoped>
.reservation {
    background: #292929;
}

.reservation>h2 {
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

button {
    background-color: #EC9A29;
    border: none;
    padding-top: 10px;
    padding-bottom: 10px;
}
</style>