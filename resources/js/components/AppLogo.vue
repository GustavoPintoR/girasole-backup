<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';
import { useSidebar } from './ui/sidebar';

const appName = usePage().props.name;
const { state } = useSidebar();
</script>

<template>
    <div class="h-full" :class="{ 'ml-1': state === 'collapsed' }">
        <AppLogoIcon class="" />
    </div>

    <div v-if="state === 'expanded'" class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-none font-semibold">{{ appName }}</span>
    </div>
</template>
