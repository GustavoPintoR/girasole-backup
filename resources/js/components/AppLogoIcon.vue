<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { usePage } from '@inertiajs/vue3';

const appLogo = usePage().props.appLogo;
defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img :src="appLogo" class="h-full w-full max-w-[3.75rem]"  alt="logo" />
</template>
