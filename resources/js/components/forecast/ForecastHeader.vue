<script setup lang="ts">
defineProps<{
    title: string;
    meta: string;
    // date: string;
}>();
</script>

<template>
    <div class="mb-5 flex justify-between items-end border-b-2 border-gray-200 dark:border-gray-700 pb-4">
        <div>
            <h1 class="m-0 text-[1.8rem] font-bold text-gray-800 dark:text-gray-400">{{ title }}</h1>
            <div class="text-[0.9rem] font-semibold text-gray-500 dark:text-gray-300 mt-1">{{ meta }}</div>
        </div>
        <!-- <div class="text-right text-[0.85rem] text-gray-500 font-semibold">
            {{ date }}
        </div> -->
    </div>
</template>
