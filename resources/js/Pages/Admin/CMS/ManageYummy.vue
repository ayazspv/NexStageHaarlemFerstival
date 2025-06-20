<script setup lang="ts">
import { ref } from 'vue';
import AdminAppLayout from "@/Pages/Layouts/AdminAppLayout.vue";
import FestivalDetails from './Yummy/FestivalDetails.vue';
import RestaurantManagement from './Yummy/RestaurantManagement.vue';
import { useCsrf } from '@/composables/csrf';

const { csrfToken } = useCsrf();

const props = defineProps<{
    festival: any,
}>();

const activeTab = ref('festival-homepage');
</script>

<template>
    <AdminAppLayout :title="`Manage ${props.festival.name}`">
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>{{ props.festival.name }} Management</h1>
                <div>
                    <button class="btn me-2"
                        :class="activeTab === 'festival-homepage' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="activeTab = 'festival-homepage'">
                        <i class="fas fa-info-circle me-1"></i> Festival Home Page
                    </button>
                    <button class="btn"
                        :class="activeTab === 'restaurants' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="activeTab = 'restaurants'">
                        <i class="fa-solid fa-utensils"></i> Manage Restaurants
                    </button>
                </div>
            </div>

            <FestivalDetails
                v-if="activeTab === 'festival-homepage'"
                :festival="festival"
                :csrf-token="csrfToken"
            />

            <RestaurantManagement
                v-if="activeTab === 'restaurants'"
                :festival="festival"
                :csrf-token="csrfToken"
            />
        </div>
    </AdminAppLayout>
</template>

<style scoped></style>
