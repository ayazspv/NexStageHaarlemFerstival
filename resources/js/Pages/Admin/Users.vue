<script setup lang="ts">
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";
import { useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from "vue";

// Defines a User interface for type checking
interface User {
    id: number;
    firstName: string;
    lastName: string;
    username: string;
    email: string;
    phoneNumber: string | null;
    role: 'admin' | 'user' | 'employee';
    created_at: string;
}

interface PaginationData {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Filters {
    search: string;
    role: string;
    sort_by: string;
    sort_order: string;
    per_page: number;
}

const props = defineProps<{
    users: PaginationData;
    filters: Filters;
    roles: string[];
}>();

const showEditForm = ref(false);
const showCreateForm = ref(false);
const editingUser = ref<User | null>(null);

// Filter form
const filterForm = useForm({
    search: props.filters.search,
    role: props.filters.role,
    sort_by: props.filters.sort_by,
    sort_order: props.filters.sort_order,
    per_page: props.filters.per_page,
});

// User form
const form = useForm({
    firstName: '',
    lastName: '',
    username: '',
    email: '',
    password: '',
    phoneNumber: '',
    role: 'user' as 'admin' | 'user' | 'employee'
});

const page = usePage();
const csrfToken = page.props.csrf_token as string;

// Watch for filter changes and apply them
watch([
    () => filterForm.search,
    () => filterForm.role,
    () => filterForm.per_page,
], () => {
    applyFilters();
}, { debounce: 500 });

const applyFilters = () => {
    router.get('/admin/users', filterForm.data(), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.search = '';
    filterForm.role = '';
    filterForm.sort_by = 'created_at';
    filterForm.sort_order = 'desc';
    filterForm.per_page = 10;
    applyFilters();
};

const sortBy = (column: string) => {
    if (filterForm.sort_by === column) {
        filterForm.sort_order = filterForm.sort_order === 'asc' ? 'desc' : 'asc';
    } else {
        filterForm.sort_by = column;
        filterForm.sort_order = 'asc';
    }
    applyFilters();
};

const getSortIcon = (column: string) => {
    if (filterForm.sort_by !== column) {
        return 'fas fa-sort text-muted';
    }
    return filterForm.sort_order === 'asc' ? 'fas fa-sort-up text-primary' : 'fas fa-sort-down text-primary';
};

const editUser = (user: User) => {
    editingUser.value = user;
    form.firstName = user.firstName;
    form.lastName = user.lastName;
    form.username = user.username;
    form.email = user.email;
    form.phoneNumber = user.phoneNumber || '';
    form.role = user.role;
    form.password = '';
    showEditForm.value = true;
};

const resetForm = () => {
    form.reset();
    showEditForm.value = false;
    showCreateForm.value = false;
    editingUser.value = null;
};

const submit = () => {
    if (editingUser.value) {
        form.post(`/admin/users/${editingUser.value.id}`, {
            headers: {
                "X-CSRF-TOKEN": csrfToken
            },
            preserveScroll: true,
            onSuccess: () => resetForm(),
            _method: 'PUT'
        });
    } else {
        // New user creation
        form.post('/admin/users', {
            headers: {
                "X-CSRF-TOKEN": csrfToken
            },
            preserveScroll: true,
            onSuccess: () => resetForm()
        });
    }
};

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/admin/users/${id}`, {
            headers: {
                "X-CSRF-TOKEN": csrfToken
            }
        });
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const goToPage = (url: string) => {
    if (url) {
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const getRoleBadgeClass = (role: string) => {
    switch (role) {
        case 'admin': return 'bg-danger';
        case 'employee': return 'bg-warning';
        case 'user': return 'bg-secondary';
        default: return 'bg-secondary';
    }
};
</script>

<template>
    <AdminAppLayout :title="'Manage Users'">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manage Users</h2>
                <button v-if="!showEditForm && !showCreateForm"
                        @click="showCreateForm = true"
                        class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Add New User
                </button>
            </div>

            <!-- Filters Section -->
            <div v-if="!showEditForm && !showCreateForm" class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-filter me-2"></i>
                        Filters & Search
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search Users</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input
                                    v-model="filterForm.search"
                                    type="text"
                                    class="form-control"
                                    placeholder="Search by name, username, email, phone...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Role</label>
                            <select v-model="filterForm.role" class="form-select">
                                <option value="">All Roles</option>
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Per Page</label>
                            <select v-model="filterForm.per_page" class="form-select">
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button @click="clearFilters" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form card to handle both edit and create -->
            <div v-if="showEditForm || showCreateForm" class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">
                        <i :class="showEditForm ? 'fas fa-edit' : 'fas fa-plus'" class="me-2"></i>
                        {{ showEditForm ? 'Edit User' : 'Create New User' }}
                    </h3>

                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name *</label>
                                <input v-model="form.firstName"
                                       type="text"
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.firstName }"
                                       id="firstName"
                                       required>
                                <div class="invalid-feedback">{{ form.errors.firstName }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name *</label>
                                <input v-model="form.lastName"
                                       type="text"
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.lastName }"
                                       id="lastName"
                                       required>
                                <div class="invalid-feedback">{{ form.errors.lastName }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input v-model="form.username"
                                       type="text"
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.username }"
                                       id="username"
                                       required>
                                <div class="invalid-feedback">{{ form.errors.username }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input v-model="form.email"
                                       type="email"
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.email }"
                                       id="email"
                                       required>
                                <div class="invalid-feedback">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Password {{ showEditForm ? '(leave empty to keep current)' : '*' }}
                                </label>
                                <input v-model="form.password"
                                       type="password"
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.password }"
                                       id="password"
                                       :required="!showEditForm">
                                <div class="invalid-feedback">{{ form.errors.password }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number</label>
                                <input v-model="form.phoneNumber"
                                       type="text"
                                       class="form-control"
                                       :class="{ 'is-invalid': form.errors.phoneNumber }"
                                       id="phoneNumber">
                                <div class="invalid-feedback">{{ form.errors.phoneNumber }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="role" class="form-label">Role *</label>
                            <select v-model="form.role"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.role }"
                                    id="role"
                                    required>
                                <option value="user">User</option>
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="invalid-feedback">{{ form.errors.role }}</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit"
                                    class="btn btn-primary"
                                    :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else :class="showEditForm ? 'fas fa-save' : 'fas fa-plus'" class="me-2"></i>
                                {{ showEditForm ? 'Update User' : 'Create User' }}
                            </button>
                            <button type="button"
                                    @click="resetForm"
                                    class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users List -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            Users List
                            <span class="badge bg-primary ms-2">{{ users.total }} total</span>
                        </h5>
                        <div class="text-muted">
                            Showing {{ users.from }}-{{ users.to }} of {{ users.total }} users
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th @click="sortBy('firstName')" class="cursor-pointer">
                                    Name
                                    <i :class="getSortIcon('firstName')" class="ms-1"></i>
                                </th>
                                <th @click="sortBy('username')" class="cursor-pointer">
                                    Username
                                    <i :class="getSortIcon('username')" class="ms-1"></i>
                                </th>
                                <th @click="sortBy('email')" class="cursor-pointer">
                                    Email
                                    <i :class="getSortIcon('email')" class="ms-1"></i>
                                </th>
                                <th>Phone</th>
                                <th @click="sortBy('role')" class="cursor-pointer">
                                    Role
                                    <i :class="getSortIcon('role')" class="ms-1"></i>
                                </th>
                                <th @click="sortBy('created_at')" class="cursor-pointer">
                                    Registration Date
                                    <i :class="getSortIcon('created_at')" class="ms-1"></i>
                                </th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ user.firstName.charAt(0) }}{{ user.lastName.charAt(0) }}
                                        </div>
                                        {{ user.firstName }} {{ user.lastName }}
                                    </div>
                                </td>
                                <td>{{ user.username }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.phoneNumber || '-' }}</td>
                                <td>
                                        <span class="badge" :class="getRoleBadgeClass(user.role)">
                                            {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                        </span>
                                </td>
                                <td>{{ formatDate(user.created_at) }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button @click="editUser(user)"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteUser(user.id)"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete User"
                                                :disabled="user.id === $page.props.auth.user.id">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Page {{ users.current_page }} of {{ users.last_page }}
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li v-for="link in users.links" :key="link.label"
                                    class="page-item"
                                    :class="{
                                        active: link.active,
                                        disabled: !link.url
                                    }">
                                    <button @click="goToPage(link.url)"
                                            class="page-link"
                                            :disabled="!link.url"
                                            v-html="link.label">
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
    user-select: none;
}

.cursor-pointer:hover {
    background-color: #f8f9fa;
}

.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.pagination .page-link {
    border: 1px solid #dee2e6;
    color: #495057;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
