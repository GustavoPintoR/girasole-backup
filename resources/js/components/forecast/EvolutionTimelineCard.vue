<script setup lang="ts">
import { ref } from 'vue';
import InfoModal from './InfoModal.vue';
import { trans } from 'laravel-vue-i18n';

export interface TimelineItem {
    label: string;
    dateString: string;
    isMorning: boolean;
    conditionIcon: string;
    conditionLabel: string;
    conditionColor: string;
    radString: string;
    rainString: string;
    temp: number;
    rh: number;
    dp: number;
}

defineProps<{
    items: TimelineItem[];
}>();

const showModal = ref(false);
</script>

<template>
    <div class="bg-white dark:bg-card text-gray-900 dark:text-gray-300 rounded-xl p-[18px] border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col relative lg:col-span-4">
        <div
            class="text-[0.7rem] font-bold text-gray-500 dark:text-gray-300 uppercase mb-[15px] tracking-[0.5px] border-b border-gray-100 dark:border-gray-500 pb-2 flex justify-between items-center">
            <span>{{ trans('ui.evolution_timeline_title') }}</span>
            <button
                class="bg-none border-none cursor-pointer text-gray-400 dark:text-gray-300 transition-colors duration-200 p-1 hover:text-blue-500"
                @click="showModal = true">
                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px] fill-current">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                </svg>
            </button>
        </div>
        <div
            class="flex overflow-x-auto gap-0 pb-[10px] scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-800">
            <div v-for="(item, index) in items" :key="index"

                class="min-w-[100px] text-center border-r border-dashed border-gray-200 dark:border-gray-700 py-[6px] px-[10px] last:border-r-0"
                :class="{ 'bg-gradient-to-b from-gray-50 to-white dark:from-gray-800 dark:to-card': item.isMorning }">

                <span class="text-[0.7rem] font-bold text-gray-500 dark:text-gray-300 mb-[1px] block"
                    :class="{ 'text-black dark:text-gray-100': !item.isMorning }">{{ item.label }}</span>
                <span class="text-[0.7rem] text-gray-400 dark:text-gray-300 mb-[5px] block">{{ item.dateString }}</span>

                <div class="mb-2 flex flex-col items-center justify-center min-h-[60px]">
                    <div class="text-[1.5rem]">{{ item.conditionIcon }}</div>
                    <div class="text-[0.7rem] font-semibold" :class="item.conditionColor" v-if="item.radString !== '-'">{{ item.radString }}</div>
                    <div class="text-[0.7rem] font-semibold text-blue-500 dark:text-blue-400" v-if="item.radString === '-' || item.radString !== '-' && item.rainString !== '-'">{{ item.rainString }}</div>
                </div>

                <span class="font-bold text-[1.1rem] block mb-[4px]">{{ item.temp }}°</span>
                <span class="text-[0.7rem] text-gray-400 dark:text-gray-300 block mt-[2px]">RH: {{ item.rh }}%</span>
                <span class="text-[0.7rem] text-gray-400 dark:text-gray-300 block mt-[2px]"
                    :class="{ 'font-bold text-gray-600 dark:text-gray-300': item.isMorning }">DP: {{ item.dp }}°</span>
            </div>
        </div>

        <InfoModal :show="showModal" :title="trans('ui.evolution_modal_title')" @close="showModal = false">
            <p class="mb-2">{{ trans('ui.evolution_modal_intro') }}</p>
            <ul class="list-disc pl-5 space-y-2">
                <li>
                    <strong>{{ trans('ui.evolution_modal_item1_label') }}</strong>
                    {{ trans('ui.evolution_modal_item1_text') }}
                </li>
                <li>
                    <strong>{{ trans('ui.evolution_modal_item2_label') }}</strong>
                    {{ trans('ui.evolution_modal_item2_text') }}
                </li>
                <li>
                    <strong>{{ trans('ui.evolution_modal_item3_label') }}</strong>
                    <div class="pl-2">
                        <div>{{ trans('ui.evolution_modal_item3_line1') }}</div>
                        <div>{{ trans('ui.evolution_modal_item3_line2') }}</div>
                        <div>{{ trans('ui.evolution_modal_item3_line3') }}</div>
                    </div>
                </li>
            </ul>
        </InfoModal>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: #D1D5DB #F3F4F6;
}

.scrollbar-thin::-webkit-scrollbar {
    height: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #F3F4F6;
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #D1D5DB;
    border-radius: 4px;
    border: 2px solid #F3F4F6;
}

:deep(.dark) .scrollbar-thin {
    scrollbar-color: #4B5563 #0B1220;
}

:deep(.dark) .scrollbar-thin::-webkit-scrollbar-track {
    background: #0B1220;
}

:deep(.dark) .scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #4B5563;
    border-radius: 4px;
    border: 2px solid #0B1220;
}

.dark .scrollbar-thin {
    scrollbar-color: #4B5563 #0B1220 !important;
}

.dark .scrollbar-thin::-webkit-scrollbar-track {
    background: #0B1220 !important;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #4B5563 !important;
    border-radius: 4px !important;
    border: 2px solid #0B1220 !important;
}

::v-deep(.dark) .scrollbar-thin::-webkit-scrollbar-track {
    background: #0B1220;
}

::v-deep(.dark) .scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #4B5563;
    border-radius: 4px;
    border: 2px solid #0B1220;
}
</style>
