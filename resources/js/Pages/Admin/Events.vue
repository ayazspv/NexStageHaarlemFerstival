<script setup lang="ts">
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";
import {useForm, Link, router, usePage} from '@inertiajs/vue3';
import { ref } from "vue";
import {Festival} from "../../../models";

const props = defineProps<{
    festivals: Festival[];
}>();

const page = usePage();
const csrfToken = page.props.csrf_token as string || "";

// Remove showCreateForm ref since we won't need it anymore
const showEditForm = ref(false);
const editingFestival = ref<Festival | null>(null);

// Removed image field from form
const form = useForm({
    name: '',
    isGame: false,
    ticket_amount: 0,
    time_slot: '',
    _method: 'PUT' // For method spoofing
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    showEditForm.value = false;
    editingFestival.value = null;
};

const submit = () => {
    if (showEditForm.value && editingFestival.value) {
        // Edit mode
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', form.name);
        formData.append('isGame', form.isGame ? '1' : '0');
        formData.append('ticket_amount', form.ticket_amount.toString());
        formData.append('time_slot', form.time_slot || '');
        formData.append('_token', csrfToken);
        
        router.post(`/admin/festivals/${editingFestival.value.id}`, formData, {
            forceFormData: true,
            onSuccess: () => {
                resetForm();
                window.location.reload();
            },
            onError: (errors) => {
                Object.keys(errors).forEach(key => {
                    form.setError(key, errors[key]);
                });
            }
        });
    }
};

const editFestival = (festival: Festival) => {
    editingFestival.value = festival;
    form.name = festival.name;
    form.isGame = festival.isGame || false;
    form.ticket_amount = festival.ticket_amount || 0;
    form.time_slot = festival.time_slot || '';
    form.clearErrors();
    showEditForm.value = true;
};

const deleteFestival = (id: number) => {
    if (confirm('Are you sure you want to delete this festival?')) {
        router.delete(`/admin/festivals/${id}`, {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            }
        });
    }
};

const manageEvent = (festivalId: number) => {
    router.get(`/admin/festivals/cms/manage/${festivalId}`);
};
</script>

<template>
    <AdminAppLayout :title="'Manage Events'">
        <div class="container-fluid p-4">
            <!-- Header - Removed the Add New Festival button -->
            <div class="mb-4">
                <h2>Manage Events</h2>
            </div>

            <!-- Edit Form - Removed the condition for showCreateForm -->
            <div v-if="showEditForm" class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">Edit Festival</h3>

                    <form @submit.prevent="submit">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input v-model="form.name"
                                   type="text"
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.name }"
                                   id="name"
                                   required>
                            <div class="invalid-feedback">{{ form.errors.name }}</div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="isGame"
                                   v-model="form.isGame">
                            <label class="form-check-label" for="isGame">Is Game Festival</label>
                        </div>

                        <div class="mb-3">
                            <label for="ticket_amount" class="form-label">Available Tickets</label>
                            <input v-model.number="form.ticket_amount"
                                   type="number"
                                   min="0"
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.ticket_amount }"
                                   id="ticket_amount">
                            <div class="invalid-feedback">{{ form.errors.ticket_amount }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="time_slot" class="form-label">Time Slot</label>
                            <input v-model="form.time_slot"
                                   type="text"
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.time_slot }"
                                   id="time_slot"
                                   placeholder="e.g. 10:00 - 16:00">
                            <div class="invalid-feedback">{{ form.errors.time_slot }}</div>
                            <small class="form-text text-muted">Enter the time slot for this event (e.g. 10:00 - 16:00)</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit"
                                    class="btn btn-primary"
                                    :disabled="form.processing">
                                Update Festival
                            </button>
                            <button type="button"
                                    @click="resetForm"
                                    class="btn btn-secondary">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Festivals List -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Image</th>
                                    <th>Game</th>
                                    <th>Time Slot</th> 
                                    <th>Available Tickets</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="festival in festivals" :key="festival.id">
                                    <td>{{ festival.name }}</td>
                                    <td>
                                        <img v-if="festival.image_path" 
                                             :src="`/storage/${festival.image_path}`"
                                             :alt="festival.name"
                                             class="img-thumbnail"
                                             style="height: 50px">
                                        <span v-else class="badge bg-secondary">No image</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="festival.isGame ? 'bg-success' : 'bg-secondary'">
                                            {{ festival.isGame ? 'Game' : 'Standard' }}
                                        </span>
                                    </td>
                                    <td>{{ festival.time_slot || 'Not specified' }}</td>
                                    <td>{{ festival.ticket_amount }}</td>
                                    <td>
                                        <div class="btn-group gap-2">
                                            <button @click="manageEvent(festival.id)"
                                                    class="btn btn-sm btn-primary me-1">
                                                Manage
                                            </button>
                                            <button @click="editFestival(festival)"
                                                    class="btn btn-sm btn-info me-1">
                                                Edit
                                            </button>
                                            <button @click="deleteFestival(festival.id)"
                                                    class="btn btn-sm btn-danger">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
</style>
