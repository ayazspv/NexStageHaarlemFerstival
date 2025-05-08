<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { JazzFestival } from '../../../../../models';
import { useCsrf } from '@/composables/csrf';

const { csrfToken } = useCsrf();

const props = defineProps<{
    festivalId: number;
    mode: 'create' | 'edit';
    editingBandId: number | null;
    currentBand: JazzFestival | null;
    selectedDay: number;
}>();

const emit = defineEmits(['close', 'submitted']);

// Festival day options
const festivalDays = [24, 25, 26, 27];

// Artist form data
const artistForm = reactive({
    band_name: '',
    start_time: '',
    end_time: '',
    performance_day: props.selectedDay,
    ticket_price: 0,
    band_description: '<p>Enter artist description...</p>',
    band_details: '<p>Enter artist details...</p>',
    band_image: null as File | null
});

// Initialize tiptap editor
const artistDescriptionEditor = new Editor({
    content: '<p>Enter artist description...</p>',
    extensions: [StarterKit],
    onUpdate({ editor }) {
        artistForm.band_description = editor.getHTML();
    },
});

const artistDetailsEditor = new Editor({
    content: '<p>Enter artist details...</p>',
    extensions: [StarterKit],
    onUpdate({ editor }) {
        artistForm.band_details = editor.getHTML();
    },
});

// Add these near the top with your other variables
const imageError = ref('');
const MAX_FILE_SIZE_MB = 2; // 2MB limit (2048 KB)
const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

// Enhance the handleArtistImage function with validation
const handleArtistImage = (e: Event) => {
    const input = e.target as HTMLInputElement;
    imageError.value = ''; // Clear previous errors
    
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        
        // Check file size
        if (file.size > MAX_FILE_SIZE_BYTES) {
            imageError.value = `Image is too large. Maximum size is ${MAX_FILE_SIZE_MB}MB.`;
            // Clear the file input
            input.value = '';
            return;
        }
        
        artistForm.band_image = file;
        console.log(`Selected image: ${file.name}, size: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
    }
};

// Helper function to extract time from datetime
const extractTimeFromDateTime = (dateTimeStr: string) => {
    if (!dateTimeStr) return '';
    const date = new Date(dateTimeStr);
    return date.toTimeString().substring(0, 5); // Get HH:MM format
};

// Calculate performance_datetime from day and start_time
const createPerformanceDateTime = (day: number, timeStr: string) => {
    if (!timeStr) return '';
    // Create a date for July [day], 2024
    const date = new Date(2024, 6, day); // July is 6 
    const [hours, minutes] = timeStr.split(':').map(Number);
    date.setHours(hours, minutes);
    return date.toISOString().substring(0, 16); // Format as YYYY-MM-DDTHH:MM
};

// Submit form
const submitArtistForm = (e: Event) => {
    if (e) e.preventDefault();
    
    console.log("Form submission started");
    
    // Update form values with the latest editor content
    artistForm.band_description = artistDescriptionEditor.getHTML();
    artistForm.band_details = artistDetailsEditor.getHTML();
    
    // Check for required fields
    if (!artistForm.band_name || !artistForm.start_time || !artistForm.end_time || 
        !artistForm.band_description || !artistForm.band_details) {
        alert('Please fill in all required fields');
        console.log("Validation failed - missing required fields");
        return;
    }
    
    // Check image size again before submission
    if (artistForm.band_image && artistForm.band_image.size > MAX_FILE_SIZE_BYTES) {
        imageError.value = `Image is too large. Maximum size is ${MAX_FILE_SIZE_MB}MB.`;
        alert(`The selected image is too large. Maximum allowed size is ${MAX_FILE_SIZE_MB}MB.`);
        console.log("Validation failed - image too large", artistForm.band_image.size);
        return;
    }

    console.log("Form validation passed");
    console.log("Submitting artist form:", artistForm);
    
    // Create a performance_datetime from the day and start_time
    const performance_datetime = createPerformanceDateTime(
        artistForm.performance_day, 
        artistForm.start_time
    );

    // FormData for submission
    const formData = new FormData();
    formData.append('band_name', artistForm.band_name);
    formData.append('performance_datetime', performance_datetime);
    formData.append('performance_day', artistForm.performance_day.toString());
    formData.append('start_time', artistForm.start_time);
    formData.append('end_time', artistForm.end_time);
    formData.append('ticket_price', artistForm.ticket_price.toString());
    formData.append('band_description', artistForm.band_description);
    formData.append('band_details', artistForm.band_details);
    
    formData.append('_token', csrfToken);
    
    // Append band image if it exists
    if (artistForm.band_image) {
        console.log("Appending band image");
        formData.append('band_image', artistForm.band_image);
    }
        
    //submission based on mode (edit or create)
    if (props.mode === 'create') {
        console.log("Creating new artist - sending POST request");
        
        //new artist
        fetch(`/admin/festivals/${props.festivalId}/jazz-festival`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.ok) {
                console.log("Artist added successfully");
                emit('submitted'); //success
            } else {
                return response.json().then(err => {
                    throw new Error(JSON.stringify(err));
                });
            }
        })
        .catch(error => {
            console.error("Error adding artist:", error);
            
            // Try to parse the error message
            let errorMessage = 'Error adding artist. Please check the browser console for details.';
            try {
                const parsedError = JSON.parse(error.message);
                if (parsedError.errors && parsedError.errors.band_image) {
                    imageError.value = parsedError.errors.band_image[0];
                    errorMessage = `Upload error: ${parsedError.errors.band_image[0]}`;
                }
            } catch (e) {
                // If parsing fails, use the original error message
            }
            
            alert(errorMessage);
        });
    } else if (props.mode === 'edit' && props.editingBandId) {
        console.log("Updating existing artist - sending PUT request");
        formData.append('_method', 'PUT');
        
        //update existing artist
        fetch(`/admin/festivals/${props.festivalId}/jazz-festival/${props.editingBandId}`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.ok) {
                console.log("Artist updated successfully");
                emit('submitted');
            } else {
                return response.json().then(err => {
                    throw new Error(JSON.stringify(err));
                });
            }
        })
        .catch(error => {
            console.error("Error updating artist:", error);
            alert('Error updating artist. Please check the browser console for details.');
        });
    }
};

// Initialize form with existing data if editing
onMounted(() => {
    if (props.mode === 'edit' && props.currentBand) {
        artistForm.band_name = props.currentBand.band_name;
        artistForm.start_time = props.currentBand.start_time || extractTimeFromDateTime(props.currentBand.performance_datetime);
        artistForm.end_time = props.currentBand.end_time || '';
        artistForm.performance_day = props.currentBand.performance_day || props.selectedDay;
        artistForm.ticket_price = typeof props.currentBand.ticket_price === 'number' ? 
            props.currentBand.ticket_price : Number(props.currentBand.ticket_price);
        artistForm.band_description = props.currentBand.band_description;
        artistForm.band_details = props.currentBand.band_details;
        
        artistDescriptionEditor.commands.setContent(
            props.currentBand.band_description || '<p>Enter artist description...</p>'
        );
        artistDetailsEditor.commands.setContent(
            props.currentBand.band_details || '<p>Enter artist details...</p>'
        );
    }
});
</script>

<template>
    <div class="artist-form-modal">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ props.mode === 'create' ? 'Add New Artist' : 'Edit Artist' }}</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitArtistForm">
                        <input type="hidden" name="_token" :value="csrfToken">
                        
                        <div class="mb-3">
                            <label for="bandName" class="form-label">Artist Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bandName" v-model="artistForm.band_name" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="performanceDay" class="form-label">Festival Day <span class="text-danger">*</span></label>
                                <select class="form-select" id="performanceDay" v-model="artistForm.performance_day">
                                    <option v-for="day in festivalDays" :key="day" :value="day">July {{ day }}</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="startTime" class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="startTime" v-model="artistForm.start_time" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="endTime" class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="endTime" v-model="artistForm.end_time" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ticketPrice" class="form-label">Ticket Price (€) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="ticketPrice" v-model.number="artistForm.ticket_price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="artistImage" class="form-label">Artist Image</label>
                            <input type="file" class="form-control" id="artistImage" 
                                   @change="handleArtistImage" accept="image/*"
                                   :class="{'is-invalid': imageError}">
                            <small class="form-text">This will be displayed on the artist card. Maximum size: {{MAX_FILE_SIZE_MB}}MB</small>
                            <div v-if="imageError" class="invalid-feedback">
                                {{ imageError }}
                            </div>
                        </div>
                                                
                        <div v-if="props.mode === 'edit' && props.currentBand && props.currentBand.band_image" class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div class="text-center">
                                <img :src="`/storage/${props.currentBand.band_image}`" 
                                     alt="Artist Image" 
                                     style="height: 100px; object-fit: cover;" 
                                     class="img-thumbnail">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Artist Description <span class="text-danger">*</span></label>
                            <div class="border rounded p-2" 
                                :class="{'border-danger': !artistForm.band_description}" 
                                style="min-height: 150px;">
                                <EditorContent :editor="artistDescriptionEditor" />
                            </div>
                            <small class="form-text">Brief description displayed on artist cards.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Artist Details <span class="text-danger">*</span></label>
                            <div class="border rounded p-2" 
                                :class="{'border-danger': !artistForm.band_details}"
                                style="min-height: 150px;">
                                <EditorContent :editor="artistDetailsEditor" />
                            </div>
                            <small class="form-text">Detailed information displayed when viewing artist details.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="submitArtistForm">
                        {{ props.mode === 'create' ? 'Add Artist' : 'Update Artist' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.artist-form-modal {
    /* Make the background darker for better contrast */
    background-color: rgba(255, 255, 255);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
    overflow-y: auto;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 30px 15px;
}

.modal-dialog {
    width: 100%;
    max-width: 900px;
    margin: 1.75rem auto;
    z-index: 1051; 
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); 
    border-radius: 8px; 
}

.modal-content {
    border-radius: 8px; 
    border: none; 
}

/* Style the header for better appearance */
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    padding: 1rem 1.5rem;
}

.modal-header .btn-close {
    padding: 1rem;
    margin: -0.5rem -0.5rem -0.5rem auto;
}

.modal-title {
    font-weight: 600;
    color: #2565c7;
}

/* Style the footer for better appearance */
.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    padding: 1rem 1.5rem;
}

/* Improve scrolling for long forms */
.modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
    padding: 1.5rem;
}

/* Style form elements */
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
}

/* Make the editor containers more distinct */
.border.rounded {
    border: 1px solid #ced4da !important;
    background-color: white;
}
</style>