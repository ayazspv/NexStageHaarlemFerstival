<script setup lang="ts">
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";
import { ref } from 'vue';

interface DashboardProps {
    stats: {
        events: {
            total: number;
            latest: Array<{
                id: number;
                name: string;
                created_at: string;
            }>;
        };
        employees: {
            total: number;
            admins: number;
            latest: Array<{
                id: number;
                firstName: string;
                lastName: string;
                role: string;
                created_at: string;
            }>;
        };
        tickets: {
            placeholder: boolean;
        };
    };
}

defineProps<DashboardProps>();
</script>

<template>
    <AdminAppLayout title="Dashboard">
        <div class="container-fluid p-4">
            <h1 class="mb-4">Dashboard</h1>
            
            <div class="row">
                <!-- Events Column -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Events</h5>
                        </div>
                        <div class="card-body">
                            <div class="stats-box mb-4">
                                <h3 class="text-primary">{{ stats.events.total }}</h3>
                                <p class="text-muted">Total Events</p>
                            </div>
                            
                            <h6 class="card-subtitle mb-3">Latest Events</h6>
                            <div class="latest-items">
                                <div v-for="event in stats.events.latest" 
                                     :key="event.id" 
                                     class="latest-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ event.name }}</span>
                                        <small class="text-muted">
                                            {{ new Date(event.created_at).toLocaleDateString() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees Column -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Employees</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="stats-box">
                                        <h3 class="text-success">{{ stats.employees.total }}</h3>
                                        <p class="text-muted">Total Users</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-box">
                                        <h3 class="text-success">{{ stats.employees.admins }}</h3>
                                        <p class="text-muted">Admins</p>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="card-subtitle mb-3">Latest Registrations</h6>
                            <div class="latest-items">
                                <div v-for="employee in stats.employees.latest" 
                                     :key="employee.id" 
                                     class="latest-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ employee.firstName }} {{ employee.lastName }}</span>
                                        <span class="badge" 
                                              :class="employee.role === 'admin' ? 'bg-success' : 'bg-secondary'">
                                            {{ employee.role }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        {{ new Date(employee.created_at).toLocaleDateString() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Column (Placeholder) -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Tickets</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="placeholder-content">
                                <i class="fas fa-ticket-alt fa-4x text-info mb-3"></i>
                                <h4>Coming Soon</h4>
                                <p class="text-muted">
                                    Ticket management features will be available in future updates.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
.stats-box {
    text-align: center;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.stats-box h3 {
    margin: 0;
    font-size: 2rem;
}

.latest-items {
    max-height: 300px;
    overflow-y: auto;
}

.latest-item {
    padding: 0.75rem;
    border-bottom: 1px solid #eee;
}

.latest-item:last-child {
    border-bottom: none;
}

.placeholder-content {
    padding: 2rem;
}

.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: none;
}
</style>
