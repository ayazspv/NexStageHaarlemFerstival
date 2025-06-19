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
                <h3 class="mb-0">Festival Homepage Settings</h3>
            </div>
            <div class="card-body">
                <!-- Success message alert -->
                <div v-if="saveSuccess" class="alert alert-success mb-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Festival's home page updated successfully!
                </div>

                <form @submit.prevent="saveFestivalDetails">
                    <!-- Header Text 1 -->
                    <div class="mb-3">
                        <label for="header-text-1" class="form-label">Header Text 1</label>
                        <input type="text" class="form-control" id="header-text-1" v-model="festivalForm.name" required>
                        <small class="form-text text-muted">First header line on the hero banner</small>
                    </div>

                    <!-- Header Text 2 -->
                    <div class="mb-3">
                        <label for="header-text-2" class="form-label">Header Text 2</label>
                        <input type="text" class="form-control" id="header-text-2" v-model="festivalForm.name" required>
                        <small class="form-text text-muted">Second header line on the hero banner</small>
                    </div>

                    <!-- Sub-header text 1 -->
                    <div class="mb-3">
                        <label for="subheader-1" class="form-label">Sub-header Text 1</label>
                        <input type="text" class="form-control" id="subheader-1" v-model="festivalForm.name" required>
                        <small class="form-text text-muted">First sub-header on the hero banner</small>
                    </div>

                    <!-- Sub-header text 2 -->
                    <div class="mb-3">
                        <label for="subheader-2" class="form-label">Sub-header Text 2</label>
                        <input type="text" class="form-control" id="subheader-2" v-model="festivalForm.name" required>
                        <small class="form-text text-muted">Second sub-header on the hero banner</small>
                    </div>

                    <!-- CTA Button Text -->
                    <div class="mb-3">
                        <label for="cta" class="form-label">CTA Button Text</label>
                        <input type="text" class="form-control" id="cta" v-model="festivalForm.name" required>
                        <small class="form-text text-muted">CTA button's inner text on hero banner</small>
                    </div>

                    <!-- Hero banner image -->
                    <div class="mb-3">
                        <label for="festivalImage" class="form-label">Hero Banner Image</label>
                        <input type="file" class="form-control" id="festivalImage" @change="handleFestivalImage"
                            accept="image/*">
                        <small class="form-text text-muted">This will be used as the background image in the hero
                            banner.</small>
                    </div>

                    <!-- Descriptin Text -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description Text</label>
                        <input type="text" class="form-control" id="description" v-model="festivalForm.name" required>
                        <small class="form-text text-muted">The description text below the hero banner</small>
                    </div>

                    <!-- Saving button -->
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Festival Home Page
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>