<script setup lang="ts">
import { reactive } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { useCsrf } from '@/composables/csrf';
import { Festival } from '../../../../../models';

const { csrfToken } = useCsrf();

const props = defineProps<{
    festival: Festival
}>();

// Festival editing functionality
const festivalForm = reactive({
    name: props.festival.name || '',
    description: props.festival.description || '',
    time_slot: props.festival.time_slot || '',
    ticket_amount: props.festival.ticket_amount || 0,
    image: null as File | null,
});

// Initialize festival description editor
const festivalDescriptionEditor = new Editor({
    content: props.festival.description || '<p>Enter festival description...</p>',
    extensions: [StarterKit],
    onUpdate({ editor }) {
        festivalForm.description = editor.getHTML();
    },
});

// Handle festival image upload
const handleFestivalImage = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        festivalForm.image = input.files[0];
    }
};

// Save festival general details
const saveFestivalDetails = () => {
    const formData = new FormData();
    formData.append('name', festivalForm.name);
    formData.append('description', festivalForm.description);
    formData.append('time_slot', festivalForm.time_slot);
    formData.append('ticket_amount', festivalForm.ticket_amount.toString());
    formData.append('_method', 'PUT');
    
    if (festivalForm.image) {
        formData.append('image', festivalForm.image);
    }
    
    Inertia.post(`/admin/festivals/${props.festival.id}`, formData, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            alert('Festival details updated successfully!');
        },
    });
};
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
                        <label for="timeSlot" class="form-label">Time Slot</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="timeSlot" 
                            v-model="festivalForm.time_slot"
                            placeholder="e.g. 18:00 - 22:00"
                        >
                    </div>
                    
                    <div class="mb-3">
                        <label for="ticketAmount" class="form-label">Available Tickets</label>
                        <input 
                            type="number" 
                            class="form-control" 
                            id="ticketAmount" 
                            v-model="festivalForm.ticket_amount"
                            min="0"
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
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Festival Description</label>
                        <div class="border rounded p-3" style="min-height: 200px;">
                            <EditorContent :editor="festivalDescriptionEditor" />
                        </div>
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
        
        <!-- Festival Preview Card -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">Festival Preview</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <img 
                            v-if="props.festival.image_path"
                            :src="`/storage/${props.festival.image_path}`" 
                            alt="Festival Image" 
                            class="img-fluid rounded"
                        >
                    </div>
                    <div class="col-md-8">
                        <h2>{{ props.festival.name }}</h2>
                        <div v-html="props.festival.description"></div>
                        <div class="mt-3">
                            <strong>Time Slot:</strong> {{ props.festival.time_slot || 'Not specified' }}
                        </div>
                        <div>
                            <strong>Available Tickets:</strong> {{ props.festival.ticket_amount }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>