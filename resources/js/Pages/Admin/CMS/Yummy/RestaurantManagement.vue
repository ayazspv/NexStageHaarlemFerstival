<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Festival, JazzFestival } from '../../../../../models';
import { useCsrf } from '@/composables/csrf';
import { Inertia } from '@inertiajs/inertia';
import RestaurantCardForm from './RestaurantCardForm.vue';

const { csrfToken } = useCsrf();

const props = defineProps<{
    festival: Festival,
}>();

const restaurants = ref<any[]>([]);
const error = ref<string | null>(null);
const showForm = ref(false);
const editingRestaurant = ref<any | null>(null);

onMounted(async () => {
    await reloadRestaurants();
});

function createNewRestaurant() {
    editingRestaurant.value = null;
    showForm.value = true;
}

function editRestaurant(restaurant: any) {
    editingRestaurant.value = restaurant;
    showForm.value = true;
}

async function reloadRestaurants() {
    try {
        const response = await fetch('/api/restaurants');
        if (!response.ok) throw new Error('Failed to fetch restaurants');
        restaurants.value = await response.json();
    } catch (err) {
        error.value = err instanceof Error ? err.message : String(err);
    }
}

async function deleteRestaurant(id: number) {
    if (!confirm('Are you sure you want to delete this restaurant?')) return;
    try {
        const response = await fetch(`/api/restaurants/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (!response.ok) throw new Error('Failed to delete restaurant');
        await reloadRestaurants();
    } catch (err) {
        alert('Error deleting restaurant: ' + (err instanceof Error ? err.message : String(err)));
    }
}
</script>

<template>
    <div class="restaurants-tab">
        <!-- Restaurant Form Modal -->
        <RestaurantCardForm
            :show="showForm"
            :restaurant="editingRestaurant"
            :csrf-token="csrfToken"
            @close="showForm = false"
            @saved="showForm = false; reloadRestaurants()"
        />
        
        <!-- Restaurants Management Interface -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Restaurants List</h3>
                <button class="btn btn-success" @click="createNewRestaurant">
                    <i class="fas fa-plus me-1"></i> Add Restaurant
                </button>
            </div>
            <div class="card-body">          
                <!-- Restaurants' Cards -->
                <div v-if="restaurants?.length" class="row">
                    <div v-for="restaurant in restaurants" :key="restaurant.id" class="col-lg-6 col-xl-4 mb-4">
                        <div class="card h-100">
                            <img
                                :src="restaurant.picture_1 ? `/storage/main/yummy/${restaurant.picture_1}` : '/storage/main/yummy/top-view-table-full-food.jpg'"
                                :alt="restaurant.name"
                                class="card-img-top"
                                style="height: 200px; object-fit: cover;"
                            />
                            <div class="card-body">
                                <h5 class="card-title">{{ restaurant.name }}</h5>
                                <p class="card-text">{{ restaurant.location }}</p>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <button class="btn btn-primary" @click="editRestaurant(restaurant)">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button class="btn btn-danger" @click="deleteRestaurant(restaurant.id)">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-else class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-music mb-3" style="font-size: 4rem;"></i>
                        <h3>No Available Restaurants</h3>
                        <p class="text-muted">Click on the button below to add restaurants.</p>
                        <button class="btn btn-primary mt-2" @click="createNewRestaurant">Add a Restaurant</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.empty-state {
    color: #6c757d;
}

.artist-count {
    font-size: 2.5rem;
    font-weight: bold;
    color: #2565c7;
}

.nav-tabs .nav-link {
    cursor: pointer;
    color: #2565c7;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    border-bottom: 2px solid #2565c7;
    background-color: #f0f8ff;
}

.restaurants-list {
    padding: 2rem;
}
.card {
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
</style>