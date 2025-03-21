<script setup lang="ts">
import { Festival, CMS } from "../../../models";
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
import { Inertia } from '@inertiajs/inertia';

interface CmsPage {
    id?: number;
    title: string;
    content: string;
    parent_id?: number | null;
    children?: CmsPage[];
}

const props = defineProps<{
    festival: Festival;
    cmsPages: CmsPage[];
}>();

const parseTitle = (title: string) => {
    return title.trim().toLowerCase().replace(/\s+/g, '-');
};

const festivalSlug = parseTitle(props.festival.name);

const redirectToChildren = (page: CmsPage) => {
    const pageSlug = parseTitle(page.title);
    const basePath = `/festivals/${festivalSlug}`;
    let currentPath = window.location.pathname;
    let currentSuffix = currentPath.replace(new RegExp(`^${basePath}`), '');
    currentSuffix = currentSuffix.replace(/^\/|\/$/g, '');

    let newPath = currentSuffix === ''
        ? `${basePath}/${pageSlug}`
        : `${basePath}/${currentSuffix}/${pageSlug}`;

    Inertia.visit(newPath);
};

console.log(props.cmsPages);
</script>

<template>
    <AppLayout :title="festival.name">
        <div class="festival-page">
            <h1>{{ festival.name }}</h1>
            <div v-if="cmsPages && cmsPages.length">
                <div v-for="(page, index) in cmsPages" :key="index">
                    <div>
                        <div v-html="page.content"></div>
                        <div class="mt-3 mb-3" v-if="page.children && page.children?.length > 0">
                            <button class="btn btn-primary" @click="redirectToChildren(page)">Read more</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <p>No CMS content available.</p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ... existing styles ... */
</style>
