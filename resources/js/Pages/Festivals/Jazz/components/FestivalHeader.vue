<script setup lang="ts">
import { Festival } from "../../../../../models";

defineProps<{
    festival: Festival;
}>();
</script>

<template>
    <section class="festival-header">
        <div class="container text-center">
            <h1 class="festival-title display-4">{{ festival.name }}</h1>
        </div>
    </section>
</template>