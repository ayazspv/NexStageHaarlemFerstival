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
console.log("CSRF Token:", csrfToken); // Debuging line

const showCreateForm = ref(false);
const showEditForm = ref(false);
const editingFestival = ref<Festival | null>(null);

const form = useForm({
    name: '',
    image: null as File | null,
    isGame: false,
    festivalType: 0,
    ticket_amount: 0
});

const resetForm = () => {
    form.reset();
    showCreateForm.value = false;
    showEditForm.value = false;
    editingFestival.value = null;
};

const submit = () => {
    if (showEditForm.value && editingFestival.value) {
        form.put(`/admin/festivals/${editingFestival.value.id}`, {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            preserveScroll: true,
            onSuccess: () => resetForm()
        });
    } else {
        form.post('/admin/festivals', {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            preserveScroll: true,
            onSuccess: () => resetForm()
        });
    }
};

const editFestival = (festival: Festival) => {
    editingFestival.value = festival;
    form.name = festival.name;
    form.isGame = festival.isGame || false;
    form.ticket_amount = festival.ticket_amount || 0;
    showEditForm.value = true;
    showCreateForm.value = false;
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
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manage Events</h2>
                <button v-if="!showCreateForm"
                        @click="showCreateForm = true"
                        class="btn btn-primary">
                    Add New Festival
                </button>
            </div>

            <!-- Create/Edit Form -->
            <div v-if="showCreateForm || showEditForm" class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">
                        {{ showEditForm ? 'Edit Festival' : 'Create New Festival' }}
                    </h3>

                    <form @submit.prevent="submit">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input v-model="form.name"
                                   type="text"
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.name }"
                                   id="name">
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
                            <input v-model="form.ticket_amount"
                                   type="number"
                                   min="0"
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.ticket_amount }"
                                   id="ticket_amount">
                            <div class="invalid-feedback">{{ form.errors.ticket_amount }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Festival Image</label>
                            <input type="file"
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.image }"
                                   @input="(e) => {
                                       const target = e.target as HTMLInputElement;
                                       if (target.files) {
                                           form.image = target.files[0];
                                       }
                                   }"
                                   id="image">
                            <div class="invalid-feedback">{{ form.errors.image }}</div>
                            <img v-if="editingFestival?.image_path"
                                 :src="`/storage/${editingFestival.image_path}`"
                                 class="mt-2 img-thumbnail"
                                 style="height: 100px"
                                 alt="Current image">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit"
                                    class="btn btn-primary"
                                    :disabled="form.processing">
                                {{ showEditForm ? 'Update Festival' : 'Create Festival' }}
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
                                    <th>Available Tickets</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="festival in festivals" :key="festival.id">
                                    <td>{{ festival.name }}</td>
                                    <td>
                                        <img :src="`/storage/${festival.image_path}`"
                                             :alt="festival.name"
                                             class="img-thumbnail"
                                             style="height: 50px">
                                    </td>
                                    <td>
                                        <span class="badge" :class="festival.isGame ? 'bg-success' : 'bg-secondary'">
                                            {{ festival.isGame ? 'Game' : 'Standard' }}
                                        </span>
                                    </td>
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
