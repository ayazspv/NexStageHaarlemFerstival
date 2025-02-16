<script setup="ts">
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
</script>

<template>
    <AppLayout title="Home">
        <div class="container-fluid">
            <!-- Schedule Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Schedule</h2>
                <div class="schedule-container p-3 border rounded">
                    <!-- Placeholder for the schedule -->
                    <div class="schedule-grid">
                        <!-- This will be replaced with actual schedule implementation -->
                        <div class="time-slot">Schedule placeholder</div>
                    </div>
                </div>
            </section>

            <!-- Festivals Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Festivals</h2>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="card festival-card">
                            <img src="/path-to-image-1" class="card-img-top" alt="Festival 1">
                            <div class="card-body">
                                <h5 class="card-title text-center">Jazz Festival</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card festival-card">
                            <img src="/path-to-image-2" class="card-img-top" alt="Festival 2">
                            <div class="card-body">
                                <h5 class="card-title text-center">Food Festival</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card festival-card">
                            <img src="/path-to-image-3" class="card-img-top" alt="Festival 3">
                            <div class="card-body">
                                <h5 class="card-title text-center">Dance Festival</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card festival-card">
                            <img src="/path-to-image-4" class="card-img-top" alt="Festival 4">
                            <div class="card-body">
                                <h5 class="card-title text-center">Historic Festival</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Locations Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Locations</h2>
                <div class="map-container">
                    <!-- Placeholder for the map -->
                    <div class="map-placeholder border rounded">
                        Map will be implemented here
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.festival-card {
    transition: transform 0.3s ease;
    height: 100%;
}

.festival-card:hover {
    transform: translateY(-5px);
}

.schedule-container {
    min-height: 200px;
    background: #f8f9fa;
}

.map-container {
    min-height: 400px;
}

.map-placeholder {
    width: 100%;
    height: 400px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}
</style>
