<!-- components/Recaptcha.vue -->
<template>
    <div class="form-group mt-3">
        <div id="recaptcha-container" class="g-recaptcha"></div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onBeforeUnmount, defineEmits } from "vue";

const emit = defineEmits(["verified"]);

const siteKey = "6LelsCMrAAAAAELpl5GinEOSHXQ5gNqYWTMKPDIc";

function renderRecaptcha() {
    if ((window as any).grecaptcha) {
        (window as any).grecaptcha.render("recaptcha-container", {
            sitekey: siteKey,
            callback: (response: string) => {
                emit("verified", response);
            },
            "expired-callback": () => {
                emit("verified", ""); // Clear token when expired
            },
        });
    }
}

onMounted(() => {
    if ((window as any).grecaptcha) {
        renderRecaptcha();
    } else {
        const interval = setInterval(() => {
            if ((window as any).grecaptcha) {
                clearInterval(interval);
                renderRecaptcha();
            }
        }, 500);
    }
});

onBeforeUnmount(() => {
    if ((window as any).grecaptcha) {
        (window as any).grecaptcha.reset();
    }
});
</script>