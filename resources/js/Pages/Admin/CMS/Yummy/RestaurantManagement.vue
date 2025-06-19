<script setup lang="ts">
import { ref, computed } from 'vue';
import { Festival, JazzFestival } from '../../../../../models';
// import ArtistForm from './ArtistForm.vue';
// import ArtistCard from './ArtistCard.vue';
import { useCsrf } from '@/composables/csrf';
import { Inertia } from '@inertiajs/inertia';

const { csrfToken } = useCsrf();

const props = defineProps<{
    festival: Festival,
    bands?: JazzFestival[],
}>();

// Artist management state
const showArtistForm = ref(false);
const artistFormMode = ref<'create' | 'edit'>('create');
const editingBandId = ref<number | null>(null);
const selectedDay = ref(24);

// Days for the performance
const festivalDays = [24, 25, 26, 27];

//Organize bands by day for easier filtering
const bandsByDay = computed(() => {
    const result = {};
    festivalDays.forEach(day => {
        result[day] = props.bands?.filter(band => band.performance_day === day) || [];
    });
    return result;
});

// Get current editing band
const currentBand = computed(() => {
    if (artistFormMode.value === 'edit' && props.bands && editingBandId.value) {
        return props.bands.find(b => b.id === editingBandId.value);
    }
    return null;
});

// Start creating a new artist
const createNewArtist = () => {
    artistFormMode.value = 'create';
    editingBandId.value = null;
    showArtistForm.value = true;
};

// Edit an existing artist
const editArtist = (band: JazzFestival) => {
    artistFormMode.value = 'edit';
    editingBandId.value = band.id || null;
    showArtistForm.value = true;
};

// Close artist form
const closeArtistForm = () => {
    showArtistForm.value = false;
};

// Handle form submission result
const handleFormSubmitted = () => {
    closeArtistForm();
    window.location.reload();
};

// Delete an artist
const deleteArtist = (bandId: number) => {
    if (confirm('Are you sure you want to delete this artist? This action cannot be undone.')) {
        // Use axios instead of Inertia for the delete request
        axios.delete(`/admin/festivals/${props.festival.id}/jazz-festival/${bandId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(() => {
            alert('Artist deleted successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error deleting artist:', error);
            if (error.response && error.response.status === 404) {
                // Still reload if it's a 404 but the item was likely deleted
                alert('Artist was deleted, but encountered a response error.');
                window.location.reload();
            }
        });
    }
};
</script>

<template>
    <div class="restaurants-tab">
        <!-- Artist Form Modal -->
        <!-- <ArtistForm
            v-if="showArtistForm"
            :festival-id="props.festival.id"
            :mode="artistFormMode"
            :editing-band-id="editingBandId"
            :current-band="currentBand"
            :selected-day="selectedDay"
            @close="closeArtistForm"
            @submitted="handleFormSubmitted"
        /> -->
        
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
                    <div v-for="band in restaurants" :key="restaurant.id" class="col-lg-6 col-xl-4 mb-4">
                        <ArtistCard 
                            :band="band" 
                            @edit="editRestaurant(band)" 
                            @delete="deleteRestaurant(band.id)" 
                        />
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
</style>