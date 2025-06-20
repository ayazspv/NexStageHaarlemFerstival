<script lang="ts" setup>
import { ref } from 'vue';

const form = ref({
    name: '',
    adults: 1,
    children: 0,
    date: '',
    session: '',
    phone: '',
    email: '',
    special_request: '',
});

const submitting = ref(false);
const success = ref(false);
const error = ref('');

// Accept csrfToken as a prop
const props = defineProps<{ restaurantId: number, csrfToken: string }>();

async function submitReservation(e: Event) {
    e.preventDefault();
    submitting.value = true;
    error.value = '';
    success.value = false;

    try {
        const response = await fetch('/api/reservations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            credentials: 'same-origin', // <-- Add this line
            body: JSON.stringify({
                ...form.value,
                restaurant_id: props.restaurantId,
            }),
        });
        const data = await response.json();
        if (data.success) {
            success.value = true;
            Object.keys(form.value).forEach(key => form.value[key] = '');
        } else {
            error.value = 'Reservation failed. Please try again.';
        }
    } catch (err) {
        error.value = 'Reservation failed. Please try again.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="reservation d-flex flex-column align-items-center gap-2 align-self-center p-5 w-100">
        <h2 class="align-self-stretch">Reservation</h2>
        <div class="content container d-flex justify-content-end align-items-center gap-5 align-self-stretch">
            <div class="form w-50">
                <form class="d-flex flex-column align-items-start gap-2 w-100" @submit.prevent="submitReservation">
                    <div class="mb-3 w-100">
                        <label for="name" class="form-label">Your Name*</label>
                        <input type="text" class="form-control" id="name" v-model="form.name" required />
                    </div>
                    <div class="persons d-flex gap-3 w-100">
                        <div class="mb-3 w-100">
                            <label for="adults" class="form-label">Persons (Adults)*</label>
                            <input type="number" class="form-control" id="adults" min="1" v-model="form.adults" required />
                        </div>
                        <div class="mb-3 w-100">
                            <label for="children" class="form-label">Persons (Children)*</label>
                            <input type="number" class="form-control" id="children" min="0" v-model="form.children" required />
                        </div>
                    </div>
                    <div class="date-sessions d-flex gap-3 w-100">
                        <div class="mb-3 w-100">
                            <label for="date" class="form-label">Select Date*</label>
                            <input type="date" class="form-control" id="date" v-model="form.date" required />
                        </div>
                        <div class="mb-3 w-100">
                            <label for="session" class="form-label">Select Sessions*</label>
                            <select class="form-control" id="session" v-model="form.session" required>
                                <option value="">Select a session</option>
                                <option value="Session 1">Session 1</option>
                                <option value="Session 2">Session 2</option>
                                <option value="Session 3">Session 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="contact d-flex gap-3 w-100">
                        <div class="mb-3 w-100">
                            <label for="phone" class="form-label">Phone*</label>
                            <input type="tel" class="form-control" id="phone" v-model="form.phone" required />
                        </div>
                        <div class="mb-3 w-100">
                            <label for="email" class="form-label">Email (Optional)</label>
                            <input type="email" class="form-control" id="email" v-model="form.email" />
                        </div>
                    </div>
                    <div class="mb-3 w-100">
                        <label for="request" class="form-label">Special Request (Optional)</label>
                        <textarea class="form-control" id="request" rows="3" v-model="form.special_request"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
                        <span v-if="submitting">Submitting...</span>
                        <span v-else>Make Reservation</span>
                    </button>
                    <div v-if="success" class="alert alert-success mt-3">Reservation successful! Confirmation sent.</div>
                    <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
                </form>
            </div>
            <div class="picture"></div>
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
    background: url(../../../../../../public/storage/main/yummy/reservation.png) lightgray 50% / cover no-repeat;
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