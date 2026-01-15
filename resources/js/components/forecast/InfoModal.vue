<script setup lang="ts">
defineProps<{
    show: boolean;
    title: string;
}>();

defineEmits(['close']);
</script>

<template>
    <div v-if="show"
        class="fixed inset-0 z-50 overflow-auto bg-black/50 backdrop-blur-[5px] flex items-center justify-center"
        @click.self="$emit('close')">
        <div
            class="bg-card m-auto p-8 rounded-2xl border border-gray-400 dark:border-gray-700 w-[90%] max-w-[600px] shadow-[0_20px_50px_rgba(0,0,0,0.2)] animate-[slideDown_0.3s_ease-out]">
            <span class="text-[#aaa] float-right text-3xl font-bold cursor-pointer hover:text-black"
                @click="$emit('close')">&times;</span>
            <h2 class="mt-0 text-blue-500 text-2xl font-bold mb-4">{{ title }}</h2>
            <div class="pl-5 leading-relaxed text-gray-900 dark:text-gray-300 text-[0.95rem]">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
