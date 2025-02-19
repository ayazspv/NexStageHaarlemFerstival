<script setup>
import AppLayout from "@/Pages/Layouts/AppLayout.vue";

defineProps({
    festivals: {
        type: Array,
        default: () => []
    }
});
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
                    <div v-for="festival in festivals"
                         :key="festival.id"
                         class="col-12 col-md-6">
                        <div class="festival-wrapper">
                            <div class="card festival-card mb-3">
                                <img :src="`/storage/${festival.image_path}`"
                                     :alt="festival.name"
                                     class="card-img-top">
                            </div>
                            <h5 class="text-center mb-3">{{ festival.name }}</h5>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-primary">
                                    <i class="fas fa-ticket-alt me-2"></i>Buy Ticket
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-heart"></i> Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Locations(Map) Section -->
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
.festival-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.festival-card {
    transition: transform 0.3s ease;
    width: 100%;
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
