<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";
import { Order } from "../../../models";

const props = defineProps<{
    orders?: {
        data: Order[];
        links: any[];
        prev_page_url: string | null;
        next_page_url: string | null;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters?: {
        search?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
    statistics?: {
        total_orders: number;
        total_tickets: number;
        total_revenue: number;
        unique_customers: number;
    };
}>();

// Search and filter states
const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const dateFromFilter = ref(props.filters?.date_from || '');
const dateToFilter = ref(props.filters?.date_to || '');

// Watch for filter changes and apply them automatically with debounce
watch([
    () => searchQuery.value,
    () => statusFilter.value,
    () => dateFromFilter.value,
    () => dateToFilter.value,
], () => {
    applyFilters();
}, { debounce: 500 });

// Export modal state
const showExportModal = ref(false);
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const selectedFestivalType = ref<string | null>(null);
const selectedFormat = ref<string>('xlsx');

const festivalOptions = [
    { label: 'All Festivals', value: null },
    { label: 'Jazz Festival', value: '0' },
    { label: 'Food Festival', value: '1' },
    { label: 'History Festival', value: '2' },
    { label: 'Night@Teylers', value: '3' }
];

const statusOptions = [
    { label: 'All Statuses', value: '' },
    { label: 'Completed', value: 'completed' },
    { label: 'Pending', value: 'pending' },
    { label: 'Cancelled', value: 'cancelled' }
];

function groupTickets(tickets: any[]) {
    const grouped: Record<string, { festival: any; count: number }> = {};
    tickets.forEach((ticket) => {
        const festivalId = ticket.festival?.id;
        if (festivalId) {
            if (grouped[festivalId]) {
                grouped[festivalId].count += 1;
            } else {
                grouped[festivalId] = { festival: ticket.festival, count: 1 };
            }
        }
    });
    return Object.values(grouped);
}

function applyFilters() {
    const filters: any = {};

    if (searchQuery.value) filters.search = searchQuery.value;
    if (statusFilter.value) filters.status = statusFilter.value;
    if (dateFromFilter.value) filters.date_from = dateFromFilter.value;
    if (dateToFilter.value) filters.date_to = dateToFilter.value;

    router.get('/admin/orders', filters, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    searchQuery.value = '';
    statusFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
    // Note: applyFilters() will be called automatically by the watcher
}

function clearDates() {
    startDate.value = null;
    endDate.value = null;
}

function generateExport() {
    const params: Record<string, string> = {};
    if (startDate.value) {
        params.start_date = startDate.value;
    }
    if (endDate.value) {
        params.end_date = endDate.value;
    }
    if (selectedFestivalType.value !== null && selectedFestivalType.value !== '') {
        params.festivalType = selectedFestivalType.value;
    }
    params.format = selectedFormat.value;

    const queryString = new URLSearchParams(params).toString();
    window.location.href = `/admin/orders/export?${queryString}`;
    showExportModal.value = false;
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusBadgeClass(status: string) {
    switch (status) {
        case 'completed': return 'bg-success';
        case 'pending': return 'bg-warning';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function goToPage(url: string) {
    if (url) {
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
        });
    }
}

const paginationInfo = computed(() => {
    if (!props.orders) return '';
    return `Showing ${props.orders.from} to ${props.orders.to} of ${props.orders.total} results`;
});
</script>

<template>
    <AdminAppLayout title="Manage Orders">
        <div class="container-fluid p-4">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">Orders Management</h1>
                    <p class="text-muted mb-0">Manage and export festival ticket orders</p>
                </div>
                <button class="btn btn-primary" @click="showExportModal = true">
                    <i class="fas fa-download me-2"></i>
                    Export Orders
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="card-title mb-1">Total Orders</h6>
                                    <h4 class="mb-0">{{ statistics?.total_orders || 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="card-title mb-1">Total Tickets</h6>
                                    <h4 class="mb-0">{{ statistics?.total_tickets || 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-warning">
                                    <i class="fas fa-euro-sign"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="card-title mb-1">Total Revenue</h6>
                                    <h4 class="mb-0">€{{ Number(statistics?.total_revenue || 0).toFixed(2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="card-title mb-1">Unique Customers</h6>
                                    <h4 class="mb-0">{{ statistics?.unique_customers || 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Search & Filter
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search Orders</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="searchQuery"
                                    placeholder="Search by ID, customer name, email..."
                                >
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" v-model="statusFilter">
                                <option :value="option.value" v-for="option in statusOptions" :key="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" v-model="dateFromFilter">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" v-model="dateToFilter">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary flex-fill" @click="clearFilters">
                                    <i class="fas fa-times me-1"></i>
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Modal -->
            <div v-if="showExportModal" class="modal-overlay" @click.self="showExportModal = false">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-download me-2"></i>
                            Export Orders
                        </h3>
                        <button type="button" class="btn-close" @click="showExportModal = false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="startDate" class="form-label">Start Date</label>
                                    <input type="date" id="startDate" v-model="startDate" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="endDate" class="form-label">End Date</label>
                                    <input type="date" id="endDate" v-model="endDate" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-outline-secondary btn-sm" @click="clearDates">
                                <i class="fas fa-times me-1"></i>
                                Clear Dates
                            </button>
                        </div>

                        <div class="form-group mb-3">
                            <label for="festivalType" class="form-label">Festival Type</label>
                            <select id="festivalType" v-model="selectedFestivalType" class="form-select">
                                <option :value="option.value" v-for="option in festivalOptions" :key="option.label">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Export Format Selection -->
                        <div class="form-group mb-4">
                            <label class="form-label">Export Format</label>
                            <div class="format-options">
                                <div class="form-check format-option">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        id="formatXlsx"
                                        value="xlsx"
                                        v-model="selectedFormat">
                                    <label class="form-check-label" for="formatXlsx">
                                        <div class="format-card">
                                            <i class="fas fa-file-excel text-success fa-2x mb-2"></i>
                                            <div class="format-title">Excel</div>
                                            <div class="format-desc">(.xlsx format)</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check format-option">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        id="formatCsv"
                                        value="csv"
                                        v-model="selectedFormat">
                                    <label class="form-check-label" for="formatCsv">
                                        <div class="format-card">
                                            <i class="fas fa-file-csv text-primary fa-2x mb-2"></i>
                                            <div class="format-title">CSV</div>
                                            <div class="format-desc">(.csv format)</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success" @click="generateExport">
                            <i class="fas fa-download me-2"></i>
                            Generate {{ selectedFormat.toUpperCase() }}
                        </button>
                        <button class="btn btn-secondary" @click="showExportModal = false">
                            <i class="fas fa-times me-2"></i>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Orders List
                    </h5>
                    <div class="text-muted small">
                        {{ paginationInfo }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Tickets</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                <th>Festival Details</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="order in orders?.data" :key="order.id" class="order-row">
                                <td>
                                    <span class="badge bg-light text-dark">#{{ order.id }}</span>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            {{ order.user?.firstName?.charAt(0) || 'U' }}{{ order.user?.lastName?.charAt(0) || '' }}
                                        </div>
                                        <div class="customer-name">
                                            {{ order.user?.firstName || 'N/A' }} {{ order.user?.lastName || '' }}
                                        </div>
                                    </div>
                                </td>
                                <td>{{ order.user?.email || 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ order.tickets.length }} tickets</span>
                                </td>
                                <td>
                                    <span class="price">€{{ parseFloat(order.total_price).toFixed(2) }}</span>
                                </td>
                                <td>
                                        <span class="badge" :class="getStatusBadgeClass(order.status)">
                                            {{ order.status || 'pending' }}
                                        </span>
                                </td>
                                <td>{{ formatDate(order.ordered_at || order.created_at) }}</td>
                                <td>
                                    <div class="festival-details">
                                        <div v-for="group in groupTickets(order.tickets)"
                                             :key="group.festival.id"
                                             class="festival-item">
                                            <span class="ticket-count">{{ group.count }}x</span>
                                            <span class="festival-name">{{ group.festival.name }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="!orders?.data || orders.data.length === 0" class="empty-state">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Orders Found</h5>
                            <p class="text-muted">
                                {{ filters?.search ? 'No orders match your search criteria.' : 'There are no orders to display at the moment.' }}
                            </p>
                            <button v-if="filters?.search" class="btn btn-outline-primary" @click="clearFilters">
                                Clear Search
                            </button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders && orders.last_page > 1" class="pagination-wrapper">
                        <nav aria-label="Orders pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <!-- Previous Page -->
                                <li class="page-item" :class="{ disabled: !orders.prev_page_url }">
                                    <button
                                        class="page-link"
                                        @click="goToPage(orders.prev_page_url)"
                                        :disabled="!orders.prev_page_url"
                                        aria-label="Previous">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                </li>

                                <!-- Page Numbers -->
                                <li
                                    v-for="link in orders.links.slice(1, -1)"
                                    :key="link.label"
                                    class="page-item"
                                    :class="{ active: link.active }">
                                    <button
                                        class="page-link"
                                        @click="goToPage(link.url)"
                                        :disabled="!link.url"
                                        v-html="link.label">
                                    </button>
                                </li>

                                <!-- Next Page -->
                                <li class="page-item" :class="{ disabled: !orders.next_page_url }">
                                    <button
                                        class="page-link"
                                        @click="goToPage(orders.next_page_url)"
                                        :disabled="!orders.next_page_url"
                                        aria-label="Next">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </li>
                            </ul>
                        </nav>

                        <!-- Pagination Info -->
                        <div class="pagination-info text-center mt-3">
                            <small class="text-muted">
                                Page {{ orders.current_page }} of {{ orders.last_page }}
                                ({{ orders.total }} total orders)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
/* Stats Cards */
.stats-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.stats-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

/* Search and Filter Section */
.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(2px);
}

.modal-content {
    background: white;
    border-radius: 15px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    color: #2c3e50;
}

.btn-close {
    background: none;
    border: none;
    color: #6c757d;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 5px;
    transition: background-color 0.2s ease;
}

.btn-close:hover {
    background-color: #f8f9fa;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

/* Format Selection */
.format-options {
    display: flex;
    gap: 1rem;
}

.format-option {
    flex: 1;
    margin: 0;
}

.format-option .form-check-input {
    display: none;
}

.format-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.format-option .form-check-input:checked + .form-check-label .format-card {
    border-color: #007bff;
    background: #f8f9ff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
}

.format-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.format-desc {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Table Styles */
.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.order-row:hover {
    background-color: #f8f9fa;
}

.customer-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.customer-avatar {
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

.customer-name {
    font-weight: 500;
}

.price {
    font-weight: 600;
    color: #28a745;
}

.festival-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.festival-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.ticket-count {
    background: #e9ecef;
    padding: 0.125rem 0.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.75rem;
}

.festival-name {
    color: #495057;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
}

/* Pagination Styles */
.pagination-wrapper {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.pagination {
    --bs-pagination-padding-x: 0.75rem;
    --bs-pagination-padding-y: 0.5rem;
    --bs-pagination-font-size: 0.875rem;
    --bs-pagination-color: #6c757d;
    --bs-pagination-bg: #fff;
    --bs-pagination-border-width: 1px;
    --bs-pagination-border-color: #dee2e6;
    --bs-pagination-border-radius: 0.375rem;
    --bs-pagination-hover-color: #495057;
    --bs-pagination-hover-bg: #f8f9fa;
    --bs-pagination-hover-border-color: #dee2e6;
    --bs-pagination-focus-color: #495057;
    --bs-pagination-focus-bg: #f8f9fa;
    --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    --bs-pagination-active-color: #fff;
    --bs-pagination-active-bg: #007bff;
    --bs-pagination-active-border-color: #007bff;
    --bs-pagination-disabled-color: #6c757d;
    --bs-pagination-disabled-bg: #fff;
    --bs-pagination-disabled-border-color: #dee2e6;
}

.page-link {
    position: relative;
    display: block;
    padding: var(--bs-pagination-padding-y) var(--bs-pagination-padding-x);
    font-size: var(--bs-pagination-font-size);
    color: var(--bs-pagination-color);
    text-decoration: none;
    background-color: var(--bs-pagination-bg);
    border: var(--bs-pagination-border-width) solid var(--bs-pagination-border-color);
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.page-link:hover {
    z-index: 2;
    color: var(--bs-pagination-hover-color);
    background-color: var(--bs-pagination-hover-bg);
    border-color: var(--bs-pagination-hover-border-color);
}

.page-link:focus {
    z-index: 3;
    color: var(--bs-pagination-focus-color);
    background-color: var(--bs-pagination-focus-bg);
    outline: 0;
    box-shadow: var(--bs-pagination-focus-box-shadow);
}

.page-item:not(:first-child) .page-link {
    margin-left: -1px;
}

.page-item:first-child .page-link {
    border-top-left-radius: var(--bs-pagination-border-radius);
    border-bottom-left-radius: var(--bs-pagination-border-radius);
}

.page-item:last-child .page-link {
    border-top-right-radius: var(--bs-pagination-border-radius);
    border-bottom-right-radius: var(--bs-pagination-border-radius);
}

.page-item.active .page-link {
    z-index: 3;
    color: var(--bs-pagination-active-color);
    background-color: var(--bs-pagination-active-bg);
    border-color: var(--bs-pagination-active-border-color);
}

.page-item.disabled .page-link {
    color: var(--bs-pagination-disabled-color);
    pointer-events: none;
    background-color: var(--bs-pagination-disabled-bg);
    border-color: var(--bs-pagination-disabled-border-color);
}

.pagination-info {
    color: #6c757d;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .format-options {
        flex-direction: column;
    }

    .stats-card {
        margin-bottom: 1rem;
    }

    .modal-content {
        width: 95%;
        margin: 1rem;
    }

    .customer-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }

    .pagination {
        --bs-pagination-padding-x: 0.5rem;
        --bs-pagination-padding-y: 0.375rem;
        --bs-pagination-font-size: 0.75rem;
    }

    .pagination-wrapper {
        margin-top: 1rem;
        padding-top: 1rem;
    }

    /* Stack search filters on mobile */
    .row.g-3 > .col-md-4,
    .row.g-3 > .col-md-2 {
        margin-bottom: 1rem;
    }
}

/* Card styling */
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* Badge improvements */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Search input focus styles */
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Filter section styling */
.card-body .row.g-3 {
    align-items: end;
}

/* Button styling improvements */
.btn {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}

/* Loading states */
.table-responsive {
    position: relative;
}

/* Smooth transitions */
* {
    transition: all 0.15s ease-in-out;
}
</style>
