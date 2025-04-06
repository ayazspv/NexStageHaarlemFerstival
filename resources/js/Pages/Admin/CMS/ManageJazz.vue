<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import AdminAppLayout from "@/Pages/Layouts/AdminAppLayout.vue";
import { Festival, JazzFestival } from '../../../../models';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

const props = defineProps<{
    festival: Festival,
    bands?: JazzFestival[],
}>();

// Form mode: 'create' for new band, 'edit' for editing an existing band.
const formMode = ref<'create' | 'edit'>('create');
// When editing, store the id of the band being edited.
const editingBandId = ref<number | null>(null);

// Reactive object for the new (or currently edited) band.
const newBand = reactive({
    band_name: '',
    performance_datetime: '',
    ticket_price: 0,
    band_description: '',
    band_details: '',
    band_image: null as File | null,
    second_image: null as File | null,
});

// Compute the currently edited band from props.bands
const currentBand = computed(() => {
    if (formMode.value === 'edit' && props.bands && editingBandId.value) {
        return props.bands.find(b => b.id === editingBandId.value);
    }
    return null;
});

// Initialize tiptap editor for band description.
const editorDescription = new Editor({
    content: newBand.band_description || '<p>Enter band description...</p>',
    extensions: [StarterKit],
    onUpdate({ editor }) {
        newBand.band_description = editor.getHTML();
    },
});

// Initialize tiptap editor for band details.
const editorDetails = new Editor({
    content: newBand.band_details || '<p>Enter detailed band information...</p>',
    extensions: [StarterKit],
    onUpdate({ editor }) {
        newBand.band_details = editor.getHTML();
    },
});

// Handle file input changes for band image.
const handleBandImage = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        newBand.band_image = input.files[0];
    }
};

// Handle file input changes for second image.
const handleSecondImage = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        newBand.second_image = input.files[0];
    }
};

// Handle form submission (create or update).
const handleSubmit = (e: Event) => {
    e.preventDefault();

    const formData = new FormData();
    formData.append('band_name', newBand.band_name);
    formData.append('performance_datetime', newBand.performance_datetime);
    formData.append('ticket_price', newBand.ticket_price.toString());
    formData.append('band_description', newBand.band_description);
    formData.append('band_details', newBand.band_details);
    if (newBand.band_image) {
        formData.append('band_image', newBand.band_image);
    }
    if (newBand.second_image) {
        formData.append('second_image', newBand.second_image);
    }

    if (formMode.value === 'create') {
        Inertia.post(`/admin/festivals/${props.festival.id}/jazz-festival`, formData, {
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
    } else if (formMode.value === 'edit' && editingBandId.value) {
        formData.append('_method', 'PUT');
        Inertia.post(`/admin/festivals/${props.festival.id}/jazz-festival/${editingBandId.value}`, formData, {
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
    }
};

// Populate form for editing a band.
const editBand = (band: JazzFestival) => {
    formMode.value = 'edit';
    editingBandId.value = band.id || null;
    newBand.band_name = band.band_name;
    newBand.performance_datetime = band.performance_datetime;
    newBand.ticket_price = band.ticket_price;
    newBand.band_description = band.band_description;
    newBand.band_details = band.band_details;
    newBand.band_image = null;
    newBand.second_image = null;
    // Update tiptap editors.
    editorDescription.commands.setContent(newBand.band_description || '<p>Enter band description...</p>');
    editorDetails.commands.setContent(newBand.band_details || '<p>Enter detailed band information...</p>');
};

// Reset the form to create mode.
const cancelEdit = () => {
    formMode.value = 'create';
    editingBandId.value = null;
    newBand.band_name = '';
    newBand.performance_datetime = '';
    newBand.ticket_price = 0;
    newBand.band_description = '';
    newBand.band_details = '';
    newBand.band_image = null;
    newBand.second_image = null;
    editorDescription.commands.setContent('<p>Enter band description...</p>');
    editorDetails.commands.setContent('<p>Enter detailed band information...</p>');
};

// Delete a band record.
const deleteBand = (bandId: number) => {
    if (confirm('Are you sure you want to delete this band?')) {
        Inertia.delete(`/admin/festivals/${props.festival.id}/jazz-festival/${bandId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
    }
};
</script>

<template>
    <AdminAppLayout :title="props.festival.name">
        <div class="container mt-5">
            <h1 class="mb-4">{{ formMode === 'create' ? 'Add New Band' : 'Edit Band' }}</h1>
            <form @submit="handleSubmit" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="bandName" class="form-label">Band Name</label>
                    <input type="text" class="form-control" id="bandName" v-model="newBand.band_name" placeholder="Enter band name" required>
                    <div class="invalid-feedback">
                        Please enter a band name.
                    </div>
                </div>
                <div class="mb-3">
                    <label for="performanceDatetime" class="form-label">Performance Date &amp; Time</label>
                    <input type="datetime-local" class="form-control" id="performanceDatetime" v-model="newBand.performance_datetime" required>
                    <div class="invalid-feedback">
                        Please select a performance date and time.
                    </div>
                </div>
                <div class="mb-3">
                    <label for="ticketPrice" class="form-label">Ticket Price</label>
                    <input type="number" class="form-control" id="ticketPrice" v-model="newBand.ticket_price" placeholder="Enter ticket price" required>
                    <div class="invalid-feedback">
                        Please enter a valid ticket price.
                    </div>
                </div>
                <div v-if="formMode === 'edit' && currentBand && currentBand.band_image" class="mb-3 d-flex flex-row gap-5">
                    <div>
                        <label class="form-label">Current Band Image</label>
                        <img :src="`/storage/${currentBand.band_image}`" alt="Band Image" style="width: 300px; height: 300px; object-fit: cover;">
                    </div>
                    <div>
                        <label class="form-label">Second Band Image:</label>
                        <img :src="`/storage/${currentBand.second_image}`" alt="Band Image" style="width: 300px; height: 300px; object-fit: cover;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Band Description</label>
                    <div class="border p-2" style="min-height: 150px;">
                        <EditorContent :editor="editorDescription" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Band Details</label>
                    <div class="border p-2" style="min-height: 150px;">
                        <EditorContent :editor="editorDetails" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Band Image</label>
                    <input type="file" class="form-control" @change="handleBandImage" accept="image/*" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Second Image</label>
                    <input type="file" class="form-control" @change="handleSecondImage" accept="image/*" />
                </div>
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-primary">{{ formMode === 'create' ? 'Save Band' : 'Update Band' }}</button>
                        <button v-if="formMode === 'edit'" type="button" class="btn btn-secondary ms-2" @click="cancelEdit">Cancel</button>
                    </div>
                </div>
            </form>

            <!-- List of saved bands -->
            <div class="mt-5">
                <h2>Saved Bands</h2>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Band Name</th>
                        <th>Performance</th>
                        <th>Ticket Price</th>
                        <th>Description</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="band in props.bands" :key="band.id">
                        <td>{{ band.band_name }}</td>
                        <td>{{ band.performance_datetime }}</td>
                        <td>{{ band.ticket_price }}</td>
                        <td v-html="band.band_description"></td>
                        <td v-html="band.band_details"></td>
                        <td>
                            <button class="btn btn-sm btn-info me-2" @click="editBand(band)">Edit</button>
                            <button class="btn btn-sm btn-danger" @click="deleteBand(band.id)">Delete</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
.border {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.table td,
.table th {
    vertical-align: middle;
}
</style>
