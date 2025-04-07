<script setup lang="ts">

import AdminAppLayout from "@/Pages/Layouts/AdminAppLayout.vue";
import {Order} from "../../../models";

defineProps<{
    orders: Order[]
}>();
</script>

<template>
    <AdminAppLayout title="Orders">
        <h1>Orders</h1>
    </AdminAppLayout>
</template>

<style scoped>

</style>
