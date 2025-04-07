<script setup lang="ts">
import { Festival } from "../../../../../models";

defineProps<{
    festival: Festival;
}>();
</script>

<template>
    <section class="festival-info py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img 
                        :src="`/storage/${festival.image_path}`" 
                        :alt="festival.name" 
                        class="img-fluid rounded festival-image shadow"
                    />
                </div>
                <div class="col-lg-6">
                    <div class="festival-description" v-html="festival.description"></div>
                </div>
            </div>
        </div>
    </section>
</template>