<template>
    <div class="qr-reader">
        <h2>QR Code Reader</h2>
        <div id="reader"></div>
        <p v-if="result">✅ Scanned Result: {{ result }}</p>

        <!-- Popup for validation result -->
        <div v-if="showPopup" class="popup-overlay">
            <div class="popup-content" :class="{ success: isValid, error: !isValid }">
                <p v-if="isValid">✅ Ticket is valid!</p>
                <p v-else>❌ Ticket is invalid!</p>

                <!-- Display event and festival details if valid -->
                <div v-if="isValid && eventDetails && festivalDetails">
                    <p><strong>Event Name:</strong> {{ eventDetails.name }}</p>
                    <p><strong>Event Time:</strong> {{ eventDetails.time }}</p>
                    <p><strong>Festival Name:</strong> {{ festivalDetails.name }}</p>
                </div>

                <button @click="closePopup">Close</button>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import { usePage } from "@inertiajs/vue3";

export default {
    name: 'QrReader',
    setup() {
        const result = ref(null)
        const showPopup = ref(false)
        const isValid = ref(false)
        const eventDetails = ref(null) // Store event details
        const festivalDetails = ref(null) // Store festival details
        let html5QrCode = null

        // Retrieve CSRF token from meta tag
        const page = usePage();
        const csrfToken = page.props.csrf_token;

        const onScanSuccess = async (decodedText) => {
            result.value = decodedText
            html5QrCode.stop().catch(err => console.error('Stop failed', err))

            // Call the fetchTicketDetails API
            try {
                const response = await fetch('/api/fetch-ticket-details', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken, // Include CSRF token
                    },
                    body: JSON.stringify({ qrCode: decodedText }),
                })

                if (!response.ok) {
                    throw new Error('Failed to fetch ticket details')
                }

                const data = await response.json()
                if (data.ticket) {
                    isValid.value = true
                    eventDetails.value = data.event // Store event details
                    festivalDetails.value = data.festival // Store festival details
                } else {
                    isValid.value = false
                    eventDetails.value = null
                    festivalDetails.value = null
                }
                showPopup.value = true
            } catch (error) {
                console.error('Error fetching ticket details:', error)
            }
        }

        const closePopup = () => {
            showPopup.value = false
            result.value = null
            eventDetails.value = null
            festivalDetails.value = null

            // Resume scanning
            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: 500 },
                onScanSuccess,
                (err) => {
                    console.warn('Scan failed:', err)
                }
            )
        }

        onMounted(async () => {
            const { Html5Qrcode } = await import('html5-qrcode')
            html5QrCode = new Html5Qrcode("reader")

            try {
                const cameras = await Html5Qrcode.getCameras()
                if (cameras && cameras.length) {
                    await html5QrCode.start(
                        cameras[0].id,
                        {
                            fps: 10,
                            qrbox: 500
                        },
                        onScanSuccess,
                        (err) => {
                            console.warn('Scan failed:', err)
                        }
                    )
                }
            } catch (error) {
                console.error('Camera initialization error:', error)
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
            eventDetails,
            festivalDetails,
            closePopup
        }
    }
}
</script>

<style scoped>
.qr-reader {
    text-align: center;
    margin-top: 2rem;
    position: relative;
    height: 100vh;
    width: 100vw;
    overflow: hidden;
}

#reader {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.popup-overlay {
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

.popup-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.popup-content.success {
    border: 2px solid green;
}

.popup-content.error {
    border: 2px solid red;
}

.popup-content button {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.popup-content button:hover {
    background: #0056b3;
}
</style>
