<script setup lang="ts">
import { ref, computed } from 'vue';
import { Festival, JazzFestival } from '../../../../../models';
import ArtistForm from './ArtistForm.vue';
import ArtistCard from './ArtistCard.vue';
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
        Inertia.delete(`/admin/festivals/${props.festival.id}/jazz-festival/${bandId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken },
            onSuccess: () => {
                alert('Artist deleted successfully!');
                window.location.reload();
            },
        });
    }
};
</script>

<template>
    <div class="artists-tab">
        <!-- Artist Form Modal -->
        <ArtistForm
            v-if="showArtistForm"
            :festival-id="props.festival.id"
            :mode="artistFormMode"
            :editing-band-id="editingBandId"
            :current-band="currentBand"
            :selected-day="selectedDay"
            @close="closeArtistForm"
            @submitted="handleFormSubmitted"
        />
        
        <!-- Artist Management Interface -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Artists by Day</h3>
                <button class="btn btn-success" @click="createNewArtist">
                    <i class="fas fa-plus me-1"></i> Add Artist
                </button>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-fill mb-4">
                    <li class="nav-item" v-for="day in festivalDays" :key="day">
                        <button 
                            class="nav-link" 
                            :class="{ active: selectedDay === day }" 
                            @click="selectedDay = day">
                            July {{ day }}
                        </button>
                    </li>
                </ul>
                
                <!-- Artist Cards for Selected Day -->
                <div v-if="bandsByDay[selectedDay]?.length" class="row">
                    <div v-for="band in bandsByDay[selectedDay]" :key="band.id" class="col-lg-6 col-xl-4 mb-4">
                        <ArtistCard 
                            :band="band" 
                            @edit="editArtist(band)" 
                            @delete="deleteArtist(band.id)" 
                        />
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-else class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-music mb-3" style="font-size: 4rem;"></i>
                        <h3>No Artists Scheduled</h3>
                        <p class="text-muted">No artists are scheduled for July {{ selectedDay }}.</p>
                        <button class="btn btn-primary mt-2" @click="createNewArtist">Add an Artist</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Artist Count Summary -->
        <div class="row mt-4">
            <div v-for="day in festivalDays" :key="day" class="col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h5>July {{ day }}</h5>
                        <div class="artist-count">{{ bandsByDay[day]?.length || 0 }}</div>
                        <div class="text-muted">Artists</div>
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