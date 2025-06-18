<script setup lang="ts">
import { reactive, onMounted, ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/vue3';
import { Festival } from '../../../../../models';

// Get CSRF token directly from the page props
const page = usePage();
const csrfToken = page.props.csrf_token as string;

const props = defineProps<{
    festival: Festival
}>();

// Add success message state
const saveSuccess = ref(false);

// Add image preview URL
const imagePreviewUrl = ref<string | null>(null);

// Festival editing functionality
const festivalForm = reactive({
    name: props.festival.name || '',
    image: null as File | null,
});

// Handle festival image upload with live preview
const handleFestivalImage = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        festivalForm.image = input.files[0];
        // Create a preview URL for the selected image
        imagePreviewUrl.value = URL.createObjectURL(input.files[0]);
    }
};

// Save festival general details using the new endpoint
const saveFestivalDetails = () => {
    console.log('Submitting festival details:', {
        name: festivalForm.name,
        hasImage: !!festivalForm.image
    });
    
    const formData = new FormData();
    formData.append('name', festivalForm.name);
    formData.append('_method', 'PUT');
    
    // Only include image if there is one selected
    if (festivalForm.image) {
        formData.append('image', festivalForm.image);
    }
    
    // Use the endpoint for updating festival details
    Inertia.post(`/admin/festivals/${props.festival.id}/details`, formData, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            // Show success message and hide after 3 seconds
            saveSuccess.value = true;
            setTimeout(() => {
                saveSuccess.value = false;
            }, 3000);
        },
        onError: (errors) => {
            console.error('Error updating festival details:', errors);
            alert('Failed to update festival details. Please check console for errors.');
        }
    });
};

onMounted(() => {
    // Set initial form values from props
    festivalForm.name = props.festival.name || '';
    
    // Set initial image preview URL
    if (props.festival.image_path) {
        imagePreviewUrl.value = `/storage/${props.festival.image_path}`;
    }
});
</script>

<template>
    <div class="festival-details-tab">
        <!-- Festival Info Card -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Festival Header Settings</h3>
            </div>
            <div class="card-body">
                <!-- Success message alert -->
                <div v-if="saveSuccess" class="alert alert-success mb-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Festival header updated successfully!
                </div>
                
                <form @submit.prevent="saveFestivalDetails">
                    <div class="mb-3">
                        <label for="festivalName" class="form-label">Festival Title</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="festivalName" 
                            v-model="festivalForm.name" 
                            required
                        >
                        <small class="form-text text-muted">This title will be displayed in the hero banner.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="festivalImage" class="form-label">Hero Banner Image</label>
                        <input 
                            type="file" 
                            class="form-control" 
                            id="festivalImage" 
                            @change="handleFestivalImage"
                            accept="image/*"
                        >
                        <small class="form-text text-muted">This will be used as the background image in the hero banner.</small>
                    </div>
                    
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Festival Header
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">Hero Banner Preview</h3>
            </div>
            <div class="card-body p-0">
                <div class="position-relative" style="height: 200px; overflow: hidden;">
                    <div 
                        class="w-100 h-100" 
                        :style="`background-image: url('${imagePreviewUrl}'); background-size: cover; background-position: center;`"
                    >
                        <div style="background: rgba(0,0,0,0.3); position: absolute; inset: 0;"></div>
                        <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: rgba(255,99,87,0.9); padding: 10px 40px; z-index: 2;">
                            <h3 style="color: white; margin: 0;">{{ festivalForm.name }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>