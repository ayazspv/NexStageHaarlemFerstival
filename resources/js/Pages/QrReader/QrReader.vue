<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Pages/Layouts/AppLayout.vue";

const result = ref(null);
const showPopup = ref(false);
const isValid = ref(false);
const alreadyUsed = ref(false);
const wasJustRedeemed = ref(false);
const ticketDetails = ref(null);
const festivalDetails = ref(null);
const orderDetails = ref(null);
const processing = ref(false);
let html5QrCode = null;

const page = usePage();
const csrfToken = page.props.csrf_token;

const onScanSuccess = async (decodedText) => {
    result.value = decodedText;
    html5QrCode.stop().catch(err => console.error('Stop failed', err));

    await processTicket(decodedText);
};

const processTicket = async (qrCode) => {
    processing.value = true;

    try {
        // First, get ticket details
        const detailsResponse = await fetch('/api/fetch-ticket-details', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ qrCode }),
        });

        if (!detailsResponse.ok) {
            throw new Error('Failed to fetch ticket details');
        }

        const detailsData = await detailsResponse.json();

        if (detailsData.valid && detailsData.ticket) {
            isValid.value = true;
            alreadyUsed.value = detailsData.already_used;
            ticketDetails.value = detailsData.ticket;
            festivalDetails.value = detailsData.festival;
            orderDetails.value = detailsData.order;

            // If valid and not used, automatically redeem
            if (!detailsData.already_used) {
                const redeemSuccess = await redeemTicket(qrCode);
                if (redeemSuccess) {
                    wasJustRedeemed.value = true;
                    alreadyUsed.value = true; // Now it's used after we redeemed it
                }
            } else {
                wasJustRedeemed.value = false;
            }
        } else {
            isValid.value = false;
            alreadyUsed.value = false;
            wasJustRedeemed.value = false;
            ticketDetails.value = null;
            festivalDetails.value = null;
            orderDetails.value = null;
        }

        showPopup.value = true;
    } catch (error) {
        console.error('Error processing ticket:', error);
        isValid.value = false;
        showPopup.value = true;
    } finally {
        processing.value = false;
    }
};

const redeemTicket = async (qrCode) => {
    try {
        const response = await fetch('/api/redeem-ticket', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ qrCode }),
        });

        const data = await response.json();
        return data.success;
    } catch (error) {
        console.error('Error redeeming ticket:', error);
        return false;
    }
};

const closePopup = () => {
    showPopup.value = false;
    result.value = null;
    isValid.value = false;
    alreadyUsed.value = false;
    wasJustRedeemed.value = false;
    ticketDetails.value = null;
    festivalDetails.value = null;
    orderDetails.value = null;
    processing.value = false;

    // Resume scanning
    startScanning();
};

const startScanning = async () => {
    try {
        await html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 300, height: 300 } },
            onScanSuccess,
            (err) => {
                // Suppress continuous error logging
            }
        );
    } catch (error) {
        console.error('Failed to start scanning:', error);
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
};

onMounted(async () => {
    const { Html5Qrcode } = await import('html5-qrcode');
    html5QrCode = new Html5Qrcode("reader");

    try {
        const cameras = await Html5Qrcode.getCameras();
        if (cameras && cameras.length) {
            await startScanning();
        }
    } catch (error) {
        console.error('Camera initialization error:', error);
        alert('Camera access denied or not available');
    }
});

onBeforeUnmount(() => {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
        }).catch(err => console.error('Cleanup failed:', err));
    }
});
</script>

<template>
    <AppLayout title="QR Scanner">
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center">
                            <h4 class="mb-1">Ticket Scanner</h4>
                            <small class="text-muted">Point camera at QR code to scan</small>
                        </div>
                        <div class="card-body p-0">
                            <div id="reader" style="width: 100%; min-height: 300px;"></div>
                        </div>
                        <div class="card-footer text-center">
                            <div v-if="processing" class="d-flex align-items-center justify-content-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <span>Processing ticket...</span>
                            </div>
                            <div v-else-if="result" class="text-success">
                                <small>Scanned: {{ result.substring(0, 30) }}...</small>
                            </div>
                            <div v-else class="text-muted">
                                <small>Ready to scan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Modal -->
        <div v-if="showPopup" class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <!-- Valid Ticket - Successfully Redeemed (was not used before) -->
                    <div v-if="isValid && wasJustRedeemed" class="modal-header bg-success text-white">
                        <h5 class="modal-title">Ticket Accepted</h5>
                    </div>

                    <!-- Valid Ticket - Already Used Previously -->
                    <div v-else-if="isValid && alreadyUsed && !wasJustRedeemed" class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Ticket Already Used</h5>
                    </div>

                    <!-- Invalid Ticket -->
                    <div v-else class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Invalid Ticket</h5>
                    </div>

                    <div class="modal-body">
                        <!-- Valid Ticket Details -->
                        <div v-if="isValid">
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Festival:</strong></div>
                                <div class="col-sm-8">{{ festivalDetails.name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Ticket ID:</strong></div>
                                <div class="col-sm-8"><code>{{ ticketDetails.qr_code }}</code></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Customer:</strong></div>
                                <div class="col-sm-8">{{ orderDetails.customer_name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Price:</strong></div>
                                <div class="col-sm-8">€{{ ticketDetails.price }}</div>
                            </div>
                            <div v-if="festivalDetails.time_slot" class="row mb-3">
                                <div class="col-sm-4"><strong>Time:</strong></div>
                                <div class="col-sm-8">{{ festivalDetails.time_slot }}</div>
                            </div>
                            <div v-if="(alreadyUsed && !wasJustRedeemed) && ticketDetails.used_at" class="row mb-3">
                                <div class="col-sm-4"><strong>Used At:</strong></div>
                                <div class="col-sm-8">{{ formatDate(ticketDetails.used_at) }}</div>
                            </div>

                            <!-- Status Alert -->
                            <div v-if="wasJustRedeemed" class="alert alert-success mb-0">
                                <strong>Entry Granted:</strong> Ticket has been successfully redeemed.
                            </div>
                            <div v-else-if="alreadyUsed && !wasJustRedeemed" class="alert alert-warning mb-0">
                                <strong>Warning:</strong> This ticket has already been used!
                            </div>
                        </div>

                        <!-- Invalid Ticket -->
                        <div v-else>
                            <div class="alert alert-danger mb-0">
                                <strong>Entry Denied:</strong> This QR code is not a valid festival ticket!
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closePopup" class="btn btn-primary">
                            Continue Scanning
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
#reader {
    background-color: #f8f9fa;
}

.modal {
    backdrop-filter: blur(2px);
}

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.9em;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.alert {
    border: none;
}

.row {
    margin-bottom: 0.5rem;
}

.row:last-child {
    margin-bottom: 0;
}
</style>
