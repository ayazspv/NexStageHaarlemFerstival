<script setup lang="ts">
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";
import { useForm } from '@inertiajs/vue3';
import { ref } from "vue";

interface User {
    id: number;
    firstName: string;
    lastName: string;
    username: string;
    email: string;
    phoneNumber: string | null;
    role: 'admin' | 'user';
}

defineProps<{
    users: User[];
}>();

const showEditForm = ref(false);
const editingUser = ref<User | null>(null);

const form = useForm({
    firstName: '',
    lastName: '',
    username: '',
    email: '',
    password: '',
    phoneNumber: '',
    role: 'user' as 'admin' | 'user'
});

const editUser = (user: User) => {
    editingUser.value = user;
    form.firstName = user.firstName;
    form.lastName = user.lastName;
    form.username = user.username;
    form.email = user.email;
    form.phoneNumber = user.phoneNumber || '';
    form.role = user.role;
    form.password = ''; // Clear password field
    showEditForm.value = true;
};

const resetForm = () => {
    form.reset();
    showEditForm.value = false;
    editingUser.value = null;
};

const submit = () => {
    if (editingUser.value) {
        form.put(`/admin/users/${editingUser.value.id}`, {
            preserveScroll: true,
            onSuccess: () => resetForm()
        });
    }
};

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/admin/users/${id}`);
    }
};
</script>

<template>
    <AdminAppLayout :title="'Manage Users'">
        <div class="container-fluid p-4">
            <!-- Edit Form -->
            <div v-if="showEditForm" class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">Edit User</h3>

                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input v-model="form.firstName" 
                                       type="text" 
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.firstName }"
                                       id="firstName">
                                <div class="invalid-feedback">{{ form.errors.firstName }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input v-model="form.lastName" 
                                       type="text" 
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.lastName }"
                                       id="lastName">
                                <div class="invalid-feedback">{{ form.errors.lastName }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input v-model="form.username" 
                                   type="text" 
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.username }"
                                   id="username">
                            <div class="invalid-feedback">{{ form.errors.username }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input v-model="form.email" 
                                   type="email" 
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.email }"
                                   id="email">
                            <div class="invalid-feedback">{{ form.errors.email }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password (leave empty to keep current)</label>
                            <input v-model="form.password" 
                                   type="password" 
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.password }"
                                   id="password">
                            <div class="invalid-feedback">{{ form.errors.password }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input v-model="form.phoneNumber" 
                                   type="text" 
                                   class="form-control"
                                   :class="{ 'is-invalid': form.errors.phoneNumber }"
                                   id="phoneNumber">
                            <div class="invalid-feedback">{{ form.errors.phoneNumber }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select v-model="form.role" 
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.role }"
                                    id="role">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="invalid-feedback">{{ form.errors.role }}</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" 
                                    class="btn btn-primary"
                                    :disabled="form.processing">
                                Update User
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

            <!-- Users List -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id">
                                    <td>{{ user.firstName }} {{ user.lastName }}</td>
                                    <td>{{ user.username }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.phoneNumber || '-' }}</td>
                                    <td>{{ user.role }}</td>
                                    <td>
                                        <div class="btn-group gap-2">
                                            <button @click="editUser(user)"
                                                    class="btn btn-sm btn-info">
                                                Edit
                                            </button>
                                            <button @click="deleteUser(user.id)"
                                                    class="btn btn-sm btn-danger"
                                                    :disabled="user.id === $page.props.auth.user.id">
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