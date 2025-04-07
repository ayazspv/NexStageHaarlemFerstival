<script setup lang="ts">
import { reactive, onMounted } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { usePage } from '@inertiajs/vue3';
import { Festival } from '../../../../../models';

// Get CSRF token directly from the page props
const page = usePage();
const csrfToken = page.props.csrf_token as string;

const props = defineProps<{
    festival: Festival
}>();

// Festival editing functionality
const festivalForm = reactive({
    name: props.festival.name || '',
    description: props.festival.description || '',
    image: null as File | null,
});

// Initialize festival description editor
const festivalDescriptionEditor = new Editor({
    content: props.festival.description || '<p>Enter festival description...</p>',
    extensions: [StarterKit],
    onUpdate: ({ editor }) => {
        festivalForm.description = editor.getHTML();
        console.log("Editor content updated:", festivalForm.description);
    },
});

// Handle festival image upload
const handleFestivalImage = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        festivalForm.image = input.files[0];
    }
};

// Save festival general details using the new endpoint
const saveFestivalDetails = () => {
    // Ensure we have the latest editor content
    festivalForm.description = festivalDescriptionEditor.getHTML();
    
    console.log('Submitting festival details:', {
        name: festivalForm.name,
        descriptionLength: festivalForm.description?.length || 0,
        hasImage: !!festivalForm.image
    });
    
    const formData = new FormData();
    formData.append('name', festivalForm.name);
    formData.append('description', festivalForm.description);
    formData.append('_method', 'PUT');
    
    if (festivalForm.image) {
        formData.append('image', festivalForm.image);
    }
    
    // Use the new endpoint for updating only festival details
    Inertia.post(`/admin/festivals/${props.festival.id}/details`, formData, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            alert('Festival details updated successfully!');
            // Reload the page to show updated data
            window.location.reload();
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
    festivalForm.description = props.festival.description || '';
    
    // Set initial editor content
    festivalDescriptionEditor.commands.setContent(
        props.festival.description || '<p>Enter festival description...</p>'
    );
});
</script>

<template>
    <div class="festival-details-tab">
        <!-- Festival Info Card -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Festival Information</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="saveFestivalDetails">
                    <div class="mb-3">
                        <label for="festivalName" class="form-label">Festival Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="festivalName" 
                            v-model="festivalForm.name" 
                            required
                        >
                    </div>
                    
                    <div class="mb-3">
                        <label for="festivalImage" class="form-label">Festival Image</label>
                        <input 
                            type="file" 
                            class="form-control" 
                            id="festivalImage" 
                            @change="handleFestivalImage"
                            accept="image/*"
                        >
                        <small class="form-text text-muted">This image will be displayed on the festival page.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Festival Description</label>
                        <div class="border rounded p-3" style="min-height: 200px;">
                            <EditorContent :editor="festivalDescriptionEditor" />
                        </div>
                        <small class="form-text text-muted">This description will be displayed on the festival page.</small>
                    </div>
                    
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Festival Details
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="me-3">Current Festival Image:</div>
                                <img 
                                    v-if="props.festival.image_path" 
                                    :src="`/storage/${props.festival.image_path}`" 
                                    alt="Festival Image" 
                                    class="img-thumbnail" 
                                    style="height: 80px;"
                                >
                                <div v-else class="badge bg-secondary">No image available</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>