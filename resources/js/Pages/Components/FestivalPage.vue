<script setup lang="ts">
import {Festival, CMS} from "../../../models";
import AppLayout from "@/Pages/Layouts/AppLayout.vue";

// Use cms
const props = defineProps<{
    festival: Festival;
    cmsPages: CMS[];
}>();

</script>

<template>
    <AppLayout :title="festival.name">
        <div class="festival-page">
            <h1>{{ festival.name }}</h1>
            <div v-if="cmsPages && cmsPages.length">
                <div v-for="(page, index) in cmsPages" :key="index">
                    <div v-if="!page.parent_id" v-html="page.content"></div>
                </div>
            </div>
            <div v-else>
                <p>No CMS content available.</p>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
.festival-page {
    padding: 20px;
}
</style>
