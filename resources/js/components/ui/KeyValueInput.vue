<script setup lang="ts">
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps<{
    metadata?: Record<string, any>;
}>();

// Format array and object values into a readable string
const formatValue = computed(() => {
    return (value: any): string => {
        if (Array.isArray(value)) {
            return value.join(', ');
        }
        return typeof value === 'object' ? JSON.stringify(value, null, 2) : String(value);
    };
});

const metadataEntries = computed(() => {
    if (!props.metadata) return [];
    return Object.entries(props.metadata).map(([key, value]) => ({
        key,
        value: formatValue.value(value),
    }));
});
</script>

<template>
    <div class="p-4 bg-white dark:bg-gray-800 shadow-sm">
        <table class="w-full text-sm">
            <thead>
            <tr class="">
                <th class="text-left p-2 font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.option') }}</th>
                <th class="text-left p-2 font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.value') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(entry, index) in metadataEntries" :key="index" class="border-t hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="p-2 font-medium text-gray-900 dark:text-gray-100">{{ entry.key }}</td>
                <td class="p-2 text-gray-700 dark:text-gray-300 break-words max-w-md">
                    <pre class="whitespace-pre-wrap font-normal">{{ entry.value }}</pre>
                </td>
            </tr>
            </tbody>
        </table>
        <p v-if="!metadataEntries.length" class="p-2 text-gray-500 dark:text-gray-400">
            {{ trans('ui.no_metadata') }}
        </p>
    </div>
</template>
