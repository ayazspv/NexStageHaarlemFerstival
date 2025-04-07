<script setup lang="ts">
import { ref } from 'vue';
import AdminAppLayout from "@/Pages/Layouts/AdminAppLayout.vue";
import { Order } from "../../../models";

const props = defineProps<{
    orders?: Order[]
}>();

console.log(props.orders);

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

const showExportModal = ref(false);
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const selectedFestivalType = ref<string | null>(null);

const festivalOptions = [
    { label: 'All', value: null },
    { label: 'Jazz', value: '0' },
    { label: 'Yummy', value: '1' }
];

function clearDates() {
    startDate.value = null;
    endDate.value = null;
}

function generateExcel() {
    const params: Record<string, string> = {};
    if (startDate.value) {
        params.start_date = startDate.value;
    }
    if (endDate.value) {
        params.end_date = endDate.value;
    }
    if (selectedFestivalType.value !== null && selectedFestivalType.value !== '') {
        params.festival_type = selectedFestivalType.value;
    }

    const queryString = new URLSearchParams(params).toString();
    window.location.href = `/admin/orders/export?${queryString}`;
    showExportModal.value = false;
}
</script>

<template>
    <AdminAppLayout title="Orders">
        <div class="container-fluid p-4">
            <h1>Orders</h1>
            <button class="btn btn-primary mt-2 mb-2" @click="showExportModal = true">
                Export to excel
            </button>

            <div v-if="showExportModal" class="modal-overlay">
                <div class="modal-content">
                    <h2>Export Orders</h2>
                    <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input type="date" id="startDate" v-model="startDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date</label>
                        <input type="date" id="endDate" v-model="endDate" class="form-control">
                    </div>
                    <button class="btn btn-secondary" @click="clearDates">Clear Dates</button>
                    <div class="form-group mt-3">
                        <label for="festivalType">Festival Type</label>
                        <select id="festivalType" v-model="selectedFestivalType" class="form-control">
                            <option :value="option.value" v-for="option in festivalOptions" :key="option.label">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-success" @click="generateExcel">Generate Excel</button>
                        <button class="btn btn-danger" @click="showExportModal = false">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Count</th>
                                <th>Price</th>
                                <th>Tickets</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="order in orders" :key="order.id">
                                <td>{{ order.user.firstName }} {{ order.user.lastName }}</td>
                                <td>{{ order.user.email }}</td>
                                <td>{{ order.tickets.length }}</td>
                                <td>{{ order.total_price }}</td>
                                <td class="d-flex flex-column">
                                    <b v-for="group in groupTickets(order.tickets)" :key="group.festival.id">
                                        {{ group.count }}x {{ group.festival.name }}
                                    </b>
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
/* Basic modal styling (adjust as needed) */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 5px;
    width: 90%;
    max-width: 500px;
}
</style>
