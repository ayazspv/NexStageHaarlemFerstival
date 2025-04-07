<script setup lang="ts">
import { ref } from 'vue';
import AdminAppLayout from "@/Pages/Layouts/AdminAppLayout.vue";
import FestivalDetails from './JazzFestival/FestivalDetails.vue';
import ArtistManagement from './JazzFestival/ArtistManagement.vue';
import { Festival, JazzFestival } from '../../../../models';

const props = defineProps<{
    festival: Festival,
    bands?: JazzFestival[],
}>();

// Active tab - festival details or artists
const activeTab = ref('artists'); // Default to artist management tab
</script>

<template>
    <AdminAppLayout :title="`Manage ${props.festival.name}`">
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>{{ props.festival.name }} Management</h1>
                <div>
                    <button 
                        class="btn me-2" 
                        :class="activeTab === 'festival-details' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="activeTab = 'festival-details'"
                    >
                        <i class="fas fa-info-circle me-1"></i> Festival Details
                    </button>
                    <button 
                        class="btn" 
                        :class="activeTab === 'artists' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="activeTab = 'artists'"
                    >
                        <i class="fas fa-music me-1"></i> Manage Artists
                    </button>
                </div>
            </div>
            
            <FestivalDetails 
                v-if="activeTab === 'festival-details'" 
                :festival="festival" 
            />
            
            <ArtistManagement 
                v-if="activeTab === 'artists'" 
                :festival="festival" 
                :bands="bands" 
            />
        </div>
    </AdminAppLayout>
</template>
