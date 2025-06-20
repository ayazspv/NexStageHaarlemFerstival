<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

const emit = defineEmits(['close', 'saved']);
const props = defineProps<{
    show: boolean,
    restaurant?: any,
    csrfToken: string
}>();

const form = ref({
    name: '',
    rate: 0,
    cta_text: '',
    subheader_1: '',
    subheader_2: '',
    description: '',
    seats: 0,
    accessibility: false,
    vegan: false,
    gluten_free: false,
    halal: false,
    adult_price: 0,
    children_price: 0,
    location: '',
    contact_number: '',
    picture_1: '',
    picture_2: '',
    picture_3: '',
    picture_4: '',
    session_1_time: '',
    session_2_time: '',
    session_3_time: '',
    food_types: '', // This will be a comma-separated string
});

watch(() => props.restaurant, (val) => {
    if (val) {
        // Copy all fields except food_types and ensure seats is a number
        form.value = { ...val };
        form.value.seats = Number(val.seats) || 0;
        form.value.food_types = Array.isArray(val.food_types)
            ? val.food_types.map(ft => ft.name).join(', ')
            : '';
    } else {
        form.value = {
            name: '',
            rate: 0,
            cta_text: '',
            subheader_1: '',
            subheader_2: '',
            description: '',
            seats: 0,
            accessibility: false,
            vegan: false,
            gluten_free: false,
            halal: false,
            adult_price: 0,
            children_price: 0,
            location: '',
            contact_number: '',
            picture_1: '',
            picture_2: '',
            picture_3: '',
            picture_4: '',
            session_1_time: '',
            session_2_time: '',
            session_3_time: '',
            food_types: '',
        };
    }
}, { immediate: true });

onMounted(() => {
    if (props.restaurant) {
        form.value.food_types = Array.isArray(props.restaurant.food_types)
            ? props.restaurant.food_types.map(ft => ft.name).join(', ')
            : '';
    }
});

const saving = ref(false);
const error = ref('');

async function save() {
    // Frontend validation for required fields
    if (
        !form.value.name ||
        !form.value.cta_text ||
        !form.value.description ||
        !form.value.location ||
        !form.value.contact_number ||
        !form.value.picture_1 ||
        !form.value.session_1_time
    ) {
        error.value = 'Please fill in all required fields.';
        return;
    }
    saving.value = true;
    error.value = '';
    try {
        const method = props.restaurant && props.restaurant.id ? 'PUT' : 'POST';
        const url = props.restaurant && props.restaurant.id
            ? `/api/restaurants/${props.restaurant.id}`
            : '/api/restaurants';
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            body: JSON.stringify(form.value),
        });
        if (!res.ok) throw new Error('Failed to save restaurant');
        emit('saved');
    } catch (e: any) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
}

// When editing, convert foodTypes array to string
onMounted(() => {
    if (props.restaurant) {
        // ...set other fields...
        form.value.food_types = props.restaurant.food_types
            ? props.restaurant.food_types.map(ft => ft.name).join(', ')
            : '';
    }
});
</script>

<template>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.3)">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form @submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ props.restaurant ? 'Edit Restaurant' : 'Add Restaurant' }}</h5>
                        <button type="button" class="btn-close" @click="$emit('close')"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input v-model="form.name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rate</label>
                            <input v-model.number="form.rate" type="number" step="0.1" min="0" max="5" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Seats</label>
                            <input v-model.number="form.seats" type="number" min="0" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">CTA Text</label>
                            <input v-model="form.cta_text" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subheader 1</label>
                            <input v-model="form.subheader_1" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subheader 2</label>
                            <input v-model="form.subheader_2" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea v-model="form.description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Accessibility</label>
                            <input type="checkbox" v-model="form.accessibility" class="form-check-input ms-2">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vegan</label>
                            <input type="checkbox" v-model="form.vegan" class="form-check-input ms-2">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gluten Free</label>
                            <input type="checkbox" v-model="form.gluten_free" class="form-check-input ms-2">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Halal</label>
                            <input type="checkbox" v-model="form.halal" class="form-check-input ms-2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adult Price</label>
                            <input v-model.number="form.adult_price" type="number" step="0.01" min="0" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Children Price</label>
                            <input v-model.number="form.children_price" type="number" step="0.01" min="0" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Location</label>
                            <input v-model="form.location" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Number</label>
                            <input v-model="form.contact_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Picture 1</label>
                            <input v-model="form.picture_1" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Picture 2</label>
                            <input v-model="form.picture_2" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Picture 3</label>
                            <input v-model="form.picture_3" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Picture 4</label>
                            <input v-model="form.picture_4" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Session 1 Time</label>
                            <input v-model="form.session_1_time" type="time" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Session 2 Time</label>
                            <input v-model="form.session_2_time" type="time" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Session 3 Time</label>
                            <input v-model="form.session_3_time" type="time" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="food-types" class="form-label">Food Types</label>
                            <input
                                id="food-types"
                                v-model="form.food_types"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Italian, Vegan, Grill"
                            />
                            <small class="form-text text-muted">Separate types with a comma.</small>
                        </div>
                        <div v-if="error" class="col-12">
                            <div class="alert alert-danger">{{ error }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : (props.restaurant ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>