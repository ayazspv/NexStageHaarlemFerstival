<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const csrfToken = page.props.csrf_token as string || "";

const festivalForm = ref({
    header_text_1: '',
    header_text_2: '',
    line_1: '',
    line_2: '',
    button_text: '',
    header_image: '',
    description: '',
});

const imagePreviewUrl = ref<string | null>(null);
const headerImageFile = ref<File | null>(null);

const successMessage = ref('');
const errorMessage = ref('');

const loadYummyData = async () => {
    try {
        const response = await fetch('/storage/main/yummy/yummy.json');
        if (!response.ok) throw new Error('Failed to load yummy.json');
        const data = await response.json();
        festivalForm.value = {
            header_text_1: data.header_text_1 || '',
            header_text_2: data.header_text_2 || '',
            line_1: data.line_1 || '',
            line_2: data.line_2 || '',
            button_text: data.button_text || '',
            header_image: data.header_image || '',
            description: data.description || '',
        };
        if (data.header_image) {
            imagePreviewUrl.value = `/storage/main/yummy/${data.header_image}`;
        }
    } catch (err) {
        errorMessage.value = 'Error loading yummy.json';
        successMessage.value = '';
        console.error('Error loading yummy.json:', err);
    }
};

function onHeaderImageChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        headerImageFile.value = target.files[0];
        festivalForm.value.header_image = target.files[0].name;
        imagePreviewUrl.value = URL.createObjectURL(target.files[0]);
    }
}

async function saveFestivalDetails(e: Event) {
    e.preventDefault();
    successMessage.value = '';
    errorMessage.value = '';
    const formData = new FormData();
    Object.entries(festivalForm.value).forEach(([key, value]) => {
        formData.append(key, value as string);
    });
    if (headerImageFile.value) {
        formData.append('header_image_file', headerImageFile.value);
    }
    formData.append('_token', csrfToken);

    await fetch('/api/cms/yummy/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        }
    }).then(async res => {
        if (!res.ok) throw new Error('Failed to update');
        successMessage.value = 'Yummy homepage updated!';
        errorMessage.value = '';
        await loadYummyData();
    }).catch(err => {
        errorMessage.value = 'Error updating: ' + err.message;
        successMessage.value = '';
    });
}

onMounted(() => {
    loadYummyData();
});
</script>

<template>
    <div class="festival-details-tab">
        <form @submit="saveFestivalDetails">
            <div class="mb-3">
                <label for="header-text-1" class="form-label">Header Text 1</label>
                <input type="text" class="form-control" id="header-text-1" v-model="festivalForm.header_text_1" required>
            </div>
            <div class="mb-3">
                <label for="header-text-2" class="form-label">Header Text 2</label>
                <input type="text" class="form-control" id="header-text-2" v-model="festivalForm.header_text_2" required>
            </div>
            <div class="mb-3">
                <label for="line-1" class="form-label">Line 1</label>
                <input type="text" class="form-control" id="line-1" v-model="festivalForm.line_1" required>
            </div>
            <div class="mb-3">
                <label for="line-2" class="form-label">Line 2</label>
                <input type="text" class="form-control" id="line-2" v-model="festivalForm.line_2" required>
            </div>
            <div class="mb-3">
                <label for="button-text" class="form-label">Button Text</label>
                <input type="text" class="form-control" id="button-text" v-model="festivalForm.button_text" required>
            </div>
            <div class="mb-3">
                <label for="header-image" class="form-label">Header Image</label>
                <input
                    type="file"
                    class="form-control"
                    id="header-image"
                    accept="image/*"
                    @change="onHeaderImageChange"
                >
                <div v-if="imagePreviewUrl" class="mt-2">
                    <img :src="imagePreviewUrl" alt="Header Image" style="max-width: 300px;">
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" v-model="festivalForm.description" rows="3"></textarea>
            </div>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
        <div v-if="successMessage" class="alert alert-success mt-3">{{ successMessage }}</div>
        <div v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</div>
    </div>
</template>