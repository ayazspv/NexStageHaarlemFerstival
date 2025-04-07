<script setup lang="ts">

import AdminAppLayout from "@/Pages/Layouts/AdminAppLayout.vue";
import {Order} from "../../../models";

defineProps<{
    orders?: Order[]
}>();

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
</script>

<template>
    <AdminAppLayout title="Orders">
        <div class="container-fluid p-4">
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

</style>
