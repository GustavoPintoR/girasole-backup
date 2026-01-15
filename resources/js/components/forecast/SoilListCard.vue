<script setup lang="ts">
import { ref } from 'vue';
import InfoModal from './InfoModal.vue';
import { trans } from 'laravel-vue-i18n';

export interface SoilListItem {
    day: string;
    isToday?: boolean;
    surfaceMoisture: number;
    deepMoisture: number;
    qualityIndex: number; // 0-100 for bar width
    qualityColor: string; // Hex code
    deepMarkerPos: number; // 0-100
    temp: number;
}

defineProps<{
    items: SoilListItem[];
}>();

const showModal = ref(false);
</script>

<template>
    <div class="bg-white dark:bg-card text-gray-900 dark:text-gray-300 rounded-xl p-[18px] border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col relative lg:col-span-2">
        <div
            class="text-[0.7rem] font-bold text-gray-500 dark:text-gray-300 uppercase mb-[15px] tracking-[0.5px] border-b border-gray-100 dark:border-gray-500 pb-2 flex justify-between items-center">
            <span>{{ trans('ui.soil_10_days') }}</span>
                <div class="flex items-center gap-[5px]">
                <span class="text-[0.65rem] text-gray-500">{{ trans('ui.soil_depths_label') }}</span>
                <button
                    class="bg-none border-none cursor-pointer text-gray-400 dark:text-gray-300 transition-colors duration-200 p-1 hover:text-blue-500"
                    @click="showModal = true">
                    <svg viewBox="0 0 24 24" class="w-[18px] h-[18px] fill-current">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Header -->
        <div
            class="grid items-center gap-[10px] py-[7px] border-b border-gray-100 dark:border-gray-500 text-[0.7rem] text-gray-500 dark:text-gray-300 font-bold grid-cols-[45px_80px_1fr_40px]">
            <span>{{ trans('ui.day') }}</span>
            <span>{{ trans('ui.volumetric') }}</span>
            <span class="text-center">{{ trans('ui.quality_index') }}</span>
            <span class="text-right">{{ trans('ui.temp') }}</span>
        </div>

        <!-- Rows -->
        <div v-for="(item, index) in items" :key="index"
            class="grid items-center gap-[10px] text-[0.85rem] py-[7px] border-b border-gray-100 dark:border-gray-700 last:border-b-0 grid-cols-[45px_80px_1fr_40px]">
            <div class="font-bold uppercase" :class="{ 'text-amber-500': item.isToday }">{{ item.isToday ? trans('ui.today') : item.day }}</div>
            <div class="text-[0.75rem] dark:text-gray-300">S:{{ item.surfaceMoisture.toFixed(2) }} P:{{ item.deepMoisture.toFixed(2) }}
            </div>
            <div class="h-[12px] bg-gray-100 dark:bg-gray-800 rounded-[4px] relative overflow-hidden w-full">
                <div class="h-full absolute rounded-[4px]"
                    :style="{ width: item.qualityIndex + '%', backgroundColor: item.qualityColor }"></div>
                <div class="absolute top-0 w-[2px] h-full bg-gray-900 dark:bg-gray-300 z-[3]"
                    :style="{ left: item.deepMarkerPos + '%' }"></div>
            </div>
            <div class="text-[0.75rem] text-right dark:text-gray-300">{{ item.temp }}°</div>
        </div>

        <InfoModal :show="showModal" :title="trans('ui.soil_modal_title')" @close="showModal = false">
            <ul class="list-disc pl-5 space-y-2">
                <li>
                    <strong>{{ trans('ui.soil_modal_item1_label') }}</strong>
                    {{ trans('ui.soil_modal_item1_text') }}
                </li>
                <li>
                    <strong>{{ trans('ui.soil_modal_item2_label') }}</strong>
                    {{ trans('ui.soil_modal_item2_text') }}
                </li>
                <li>
                    <strong>{{ trans('ui.soil_modal_item3_label') }}</strong>
                    {{ trans('ui.soil_modal_item3_text') }}
                    <div class="pl-2">
                        <div>{{ trans('ui.soil_modal_item3_line1') }}</div>
                        <div>{{ trans('ui.soil_modal_item3_line2') }}</div>
                        <div>{{ trans('ui.soil_modal_item3_line3') }}</div>
                    </div>
                </li>
                <li>
                    <strong>{{ trans('ui.soil_modal_item4_label') }}</strong>
                    {{ trans('ui.soil_modal_item4_text') }}
                </li>
            </ul>
        </InfoModal>
    </div>
</template>
