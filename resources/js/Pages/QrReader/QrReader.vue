<template>
    <div class="container mt-5">
        <h1 class="text-center mb-4">QR Code Scanner</h1>

        <!-- QR Code Reader -->
        <qrcode-stream @decode="onDecode" @init="onInit">
            <div v-if="error" class="alert alert-danger text-center">
                {{ error }}
            </div>
        </qrcode-stream>

        <!-- Display Decoded Data -->
        <div v-if="decodedData" class="alert alert-success text-center mt-4">
            <h5>Decoded Data:</h5>
            <p>{{ decodedData }}</p>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal-content">
                <div v-if="isValid" class="modal-body success">
                    <i class="fas fa-check-circle"></i>
                    <p>QR Code is valid!</p>
                </div>
                <div v-else class="modal-body error">
                    <i class="fas fa-times-circle"></i>
                    <p>QR Code is invalid!</p>
                </div>
                <button class="btn btn-secondary mt-3" @click="closeModal">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { QrcodeStream } from 'vue-qrcode-reader';

const decodedData = ref<string | null>(null);
const error = ref<string | null>(null);
const showModal = ref(false);
const isValid = ref(false);

async function onDecode(data: string) {
    // Prevent processing the same QR code multiple times
    if (decodedData.value === data) return;

    decodedData.value = data; // Store the decoded QR code data
    console.log('Decoded QR Code:', data);

    // Call the validateQrCode API
    try {
        const response = await fetch('/api/validate-qr-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ qrCode: data }),
        });

        if (!response.ok) {
            throw new Error('Failed to validate QR code');
        }

        const result = await response.json();
        isValid.value = result.valid; // Set the validation result
        showModal.value = true; // Show the modal
    } catch (err) {
        console.error('Error validating QR code:', err);
        error.value = 'An error occurred while validating the QR code.';
    }
}

function onInit(promise: Promise<void>) {
    promise.catch(err => {
        error.value = 'Unable to access the camera. Please check your device permissions.';
        console.error(err);
    });
}

function closeModal() {
    showModal.value = false; // Close the modal
    decodedData.value = null; // Reset decoded data to allow scanning for new QR codes
}
</script>

<style scoped>
.container {
    text-align: center;
}

qrcode-stream {
    display: block;
    margin: 0 auto;
    max-width: 100%;
    width: 300px;
    height: 300px;
    border: 2px solid #ccc;
    border-radius: 10px;
}

.alert {
    margin-top: 20px;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    width: 300px;
}

.modal-body.success {
    color: green;
}

.modal-body.error {
    color: red;
}

.modal-body i {
    font-size: 50px;
    margin-bottom: 10px;
}

button {
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button.btn-secondary {
    background-color: #ccc;
    color: #333;
}
</style>