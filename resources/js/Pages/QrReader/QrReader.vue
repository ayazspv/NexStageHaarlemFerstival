<script>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Pages/Layouts/AppLayout.vue";

export default {
    name: 'QrReader',
    components: {AppLayout},
    setup() {
        const result = ref(null)
        const showPopup = ref(false)
        const isValid = ref(false)
        const alreadyUsed = ref(false)
        const ticketDetails = ref(null)
        const festivalDetails = ref(null)
        const orderDetails = ref(null)
        const redeeming = ref(false)
        let html5QrCode = null

        const page = usePage();
        const csrfToken = page.props.csrf_token;

        const onScanSuccess = async (decodedText) => {
            result.value = decodedText
            html5QrCode.stop().catch(err => console.error('Stop failed', err))

            await fetchTicketDetails(decodedText)
        }

        const fetchTicketDetails = async (qrCode) => {
            try {
                const response = await fetch('/api/fetch-ticket-details', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ qrCode }),
                })

                if (!response.ok) {
                    throw new Error('Failed to fetch ticket details')
                }

                const data = await response.json()

                if (data.valid && data.ticket) {
                    isValid.value = true
                    alreadyUsed.value = data.already_used
                    ticketDetails.value = data.ticket
                    festivalDetails.value = data.festival
                    orderDetails.value = data.order
                } else {
                    isValid.value = false
                    alreadyUsed.value = false
                    ticketDetails.value = null
                    festivalDetails.value = null
                    orderDetails.value = null
                }

                showPopup.value = true
            } catch (error) {
                console.error('Error fetching ticket details:', error)
                isValid.value = false
                showPopup.value = true
            }
        }

        const redeemTicket = async () => {
            redeeming.value = true

            try {
                const response = await fetch('/api/redeem-ticket', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ qrCode: result.value }),
                })

                const data = await response.json()

                if (data.success) {
                    // Mark as used and show success
                    alreadyUsed.value = true
                    alert('✅ Ticket redeemed successfully!')
                } else {
                    alert('❌ Failed to redeem ticket: ' + data.message)
                }
            } catch (error) {
                console.error('Error redeeming ticket:', error)
                alert('❌ Error redeeming ticket')
            } finally {
                redeeming.value = false
            }
        }

        const closePopup = () => {
            showPopup.value = false
            result.value = null
            isValid.value = false
            alreadyUsed.value = false
            ticketDetails.value = null
            festivalDetails.value = null
            orderDetails.value = null

            // Resume scanning
            startScanning()
        }

        const startScanning = async () => {
            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 300, height: 300 } },
                    onScanSuccess,
                    (err) => {
                        // Suppress continuous error logging
                    }
                )
            } catch (error) {
                console.error('Failed to start scanning:', error)
            }
        }

        const formatDate = (dateString) => {
            if (!dateString) return 'N/A'
            return new Date(dateString).toLocaleString()
        }

        onMounted(async () => {
            const { Html5Qrcode } = await import('html5-qrcode')
            html5QrCode = new Html5Qrcode("reader")

            try {
                const cameras = await Html5Qrcode.getCameras()
                if (cameras && cameras.length) {
                    await startScanning()
                }
            } catch (error) {
                console.error('Camera initialization error:', error)
                alert('Camera access denied or not available')
            }
        })

        onBeforeUnmount(() => {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear()
                }).catch(err => console.error('Cleanup failed:', err))
            }
        })

        return {
            result,
            showPopup,
            isValid,
            alreadyUsed,
            ticketDetails,
            festivalDetails,
            orderDetails,
            redeeming,
            closePopup,
            redeemTicket,
            formatDate
        }
    }
}
</script>
<template>
    <AppLayout title="QR Reader">
        <div class="qr-reader">
            <div class="header">
                <h2>🎫 Festival QR Code Scanner</h2>
                <p class="subtitle">Scan tickets to validate and redeem entry</p>
            </div>

            <div id="reader"></div>

            <p v-if="result" class="scan-result">
                📱 Scanned: {{ result.substring(0, 50) }}{{ result.length > 50 ? '...' : '' }}
            </p>

            <!-- Popup for validation result -->
            <div v-if="showPopup" class="popup-overlay">
                <div class="popup-content" :class="{ success: isValid && !alreadyUsed, warning: isValid && alreadyUsed, error: !isValid }">

                    <!-- Valid and Not Used -->
                    <div v-if="isValid && !alreadyUsed" class="result-section">
                        <div class="status-icon">✅</div>
                        <h3>Valid Ticket!</h3>
                        <div class="ticket-details">
                            <p><strong>🎪 Festival:</strong> {{ festivalDetails.name }}</p>
                            <p><strong>🎫 Ticket Code:</strong> {{ ticketDetails.qr_code }}</p>
                            <p><strong>💰 Price:</strong> €{{ ticketDetails.price }}</p>
                            <p><strong>👤 Customer:</strong> {{ orderDetails.customer_name }}</p>
                            <p v-if="festivalDetails.date"><strong>📅 Date:</strong> {{ formatDate(festivalDetails.date) }}</p>
                            <p v-if="festivalDetails.time_slot"><strong>⏰ Time:</strong> {{ festivalDetails.time_slot }}</p>
                        </div>
                        <div class="action-buttons">
                            <button @click="redeemTicket" class="redeem-btn" :disabled="redeeming">
                                {{ redeeming ? 'Redeeming...' : '🎫 Redeem Ticket' }}
                            </button>
                            <button @click="closePopup" class="cancel-btn">Cancel</button>
                        </div>
                    </div>

                    <!-- Valid but Already Used -->
                    <div v-else-if="isValid && alreadyUsed" class="result-section">
                        <div class="status-icon">⚠️</div>
                        <h3>Ticket Already Used!</h3>
                        <div class="ticket-details">
                            <p><strong>🎪 Festival:</strong> {{ festivalDetails.name }}</p>
                            <p><strong>🎫 Ticket Code:</strong> {{ ticketDetails.qr_code }}</p>
                            <p><strong>❌ Status:</strong> Already Redeemed</p>
                            <p v-if="ticketDetails.used_at"><strong>⏰ Used At:</strong> {{ formatDate(ticketDetails.used_at) }}</p>
                        </div>
                        <button @click="closePopup" class="close-btn">Close</button>
                    </div>

                    <!-- Invalid Ticket -->
                    <div v-else class="result-section">
                        <div class="status-icon">❌</div>
                        <h3>Invalid Ticket!</h3>
                        <p class="error-message">This QR code is not a valid festival ticket.</p>
                        <button @click="closePopup" class="close-btn">Close</button>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
* {
    box-sizing: border-box;
}

.qr-reader {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
}

.scanner-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    width: 100%;
    max-width: 400px;
}

.scanner-header {
    background: #2d3748;
    color: white;
    padding: 20px;
    text-align: center;
}

.scanner-header h2 {
    margin: 0 0 8px 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.scanner-header p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.scanner-frame {
    position: relative;
    height: 300px;
    background: #f7fafc;
    overflow: hidden;
}

#reader {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.scanner-corners {
    width: 200px;
    height: 200px;
    border: 3px solid #48bb78;
    border-radius: 12px;
    position: relative;
    background: transparent;
}

.scanner-corners::before,
.scanner-corners::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid #48bb78;
}

.scanner-corners::before {
    top: -3px;
    left: -3px;
    border-right: transparent;
    border-bottom: transparent;
    border-radius: 12px 0 0 0;
}

.scanner-corners::after {
    top: -3px;
    right: -3px;
    border-left: transparent;
    border-bottom: transparent;
    border-radius: 0 12px 0 0;
}

.scanner-line {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 180px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #48bb78, transparent);
    animation: scan 2s infinite;
}

@keyframes scan {
    0%, 100% { transform: translate(-50%, -50%) scaleX(0.8); opacity: 0.7; }
    50% { transform: translate(-50%, -50%) scaleX(1.2); opacity: 1; }
}

.scanner-status {
    padding: 20px;
    text-align: center;
    background: #f7fafc;
    border-top: 1px solid #e2e8f0;
}

.status-waiting,
.status-scanning {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #4a5568;
    font-size: 0.9rem;
    font-weight: 500;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #48bb78;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.7; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.2); }
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #e2e8f0;
    border-top: 2px solid #48bb78;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Popup Styles */
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.popup-content {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 420px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.popup-success { border-top: 4px solid #48bb78; }
.popup-warning { border-top: 4px solid #ed8936; }
.popup-error { border-top: 4px solid #e53e3e; }

.result-section {
    padding: 30px 25px;
}

.result-header {
    text-align: center;
    margin-bottom: 25px;
}

.result-header.success .result-icon { color: #48bb78; }
.result-header.warning .result-icon { color: #ed8936; }
.result-header.error .result-icon { color: #e53e3e; }

.result-icon {
    font-size: 3rem;
    margin-bottom: 10px;
    display: block;
}

.result-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
}

.ticket-info {
    background: #f7fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
}

.value {
    color: #2d3748;
    font-weight: 500;
    text-align: right;
}

.value.code {
    font-family: 'Monaco', 'Menlo', monospace;
    background: #edf2f7;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
}

.value.error {
    color: #e53e3e;
    font-weight: 600;
}

.error-text {
    text-align: center;
    color: #718096;
    font-style: italic;
    margin: 20px 0;
    line-height: 1.5;
}

/* Button Styles */
.action-buttons {
    display: flex;
    gap: 12px;
}

.btn-redeem,
.btn-cancel,
.btn-close {
    border: none;
    border-radius: 10px;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-redeem {
    flex: 1;
    background: #48bb78;
    color: white;
}

.btn-redeem:hover:not(:disabled) {
    background: #38a169;
    transform: translateY(-1px);
}

.btn-redeem:disabled {
    background: #a0aec0;
    cursor: not-allowed;
}

.btn-cancel {
    flex: 1;
    background: #edf2f7;
    color: #4a5568;
}

.btn-cancel:hover {
    background: #e2e8f0;
}

.btn-close {
    width: 100%;
    background: #4299e1;
    color: white;
}

.btn-close:hover {
    background: #3182ce;
    transform: translateY(-1px);
}

.btn-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .qr-reader {
        padding: 10px;
    }

    .scanner-container {
        max-width: 100%;
        border-radius: 16px;
    }

    .scanner-frame {
        height: 250px;
    }

    .scanner-corners {
        width: 150px;
        height: 150px;
    }

    .scanner-line {
        width: 130px;
    }

    .popup-content {
        margin: 0;
        border-radius: 12px;
    }

    .result-section {
        padding: 25px 20px;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-redeem,
    .btn-cancel {
        flex: none;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .scanner-container {
        background: #1a202c;
    }

    .scanner-frame {
        background: #2d3748;
    }

    .scanner-status {
        background: #2d3748;
        border-top-color: #4a5568;
    }

    .popup-content {
        background: #1a202c;
        color: #e2e8f0;
    }

    .result-header h3 {
        color: #e2e8f0;
    }

    .ticket-info {
        background: #2d3748;
    }

    .info-row {
        border-bottom-color: #4a5568;
    }

    .label {
        color: #a0aec0;
    }

    .value {
        color: #e2e8f0;
    }

    .value.code {
        background: #4a5568;
        color: #e2e8f0;
    }

    .btn-cancel {
        background: #4a5568;
        color: #e2e8f0;
    }

    .btn-cancel:hover {
        background: #2d3748;
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .scanner-line,
    .pulse-dot,
    .spinner {
        animation: none;
    }

    .popup-overlay,
    .popup-content {
        animation: none;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .scanner-corners,
    .scanner-line {
        border-color: #000;
        background: #000;
    }

    .pulse-dot {
        background: #000;
    }

    .popup-content {
        border: 2px solid #000;
    }
}
</style>
