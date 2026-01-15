<script setup lang="ts">
import { ref } from 'vue';
import InfoModal from './InfoModal.vue';
import { trans } from 'laravel-vue-i18n';

export interface WeatherListItem {
    day: string;
    isToday?: boolean;
    minTemp: number;
    maxTemp: number;
    rainMm?: number;
    rainProb: number; // 0-100
    vpd: number;
    vpdColor?: string;
}

defineProps<{
    items: WeatherListItem[];
}>();

const showModal = ref(false);
</script>

<template>
    <div class="bg-white dark:bg-card text-gray-900 dark:text-gray-300 rounded-xl p-[18px] border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col relative lg:col-span-2">
        <div
            class="text-[0.7rem] font-bold text-gray-500 dark:text-gray-300 uppercase mb-[15px] tracking-[0.5px] border-b border-gray-100 dark:border-gray-500 pb-2 flex justify-between items-center">
            <span>{{ trans('ui.weather_10_days') }}</span>
            <button
                class="bg-none border-none cursor-pointer text-gray-400 dark:text-gray-300 transition-colors duration-200 p-1 hover:text-blue-500"
                @click="showModal = true">
                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px] fill-current">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                </svg>
            </button>
        </div>

        <!-- Header -->
        <div
            class="grid items-center gap-[10px] py-[7px] border-b border-gray-100 dark:border-gray-500 text-[0.7rem] text-gray-500 dark:text-gray-300 font-bold grid-cols-[50px_60px_1fr_40px]">
            <span>{{ trans('ui.day') }}</span>
            <span>{{ trans('ui.temp') }}</span>
            <span class="text-center uppercase">{{ trans('ui.rain_mm_prob') }}</span>
            <span class="text-right">{{ trans('ui.vpd') }}</span>
        </div>

        <!-- Rows -->
        <div v-for="(item, index) in items" :key="index"
            class="grid items-center gap-[10px] text-[0.85rem] py-[7px] border-b border-gray-100 dark:border-gray-700 last:border-b-0 grid-cols-[50px_60px_1fr_40px]">
            <div class="font-bold uppercase" :class="{ 'text-amber-500': item.isToday }">{{ item.isToday ? trans('ui.today') : item.day }}</div>
            <div class="text-[0.8rem] dark:text-gray-300">{{ item.minTemp }}° / {{ item.maxTemp }}°</div>
            <div class="h-[14px] bg-[#E3F0FF] dark:bg-[#07203a] rounded-[4px] relative overflow-hidden w-full">
                <div class="h-full absolute rounded-[4px] bg-[#1D6FE3] dark:bg-[#1D6FE3]"
                    :style="{ width: item.rainProb + '%' }">
                </div>
                <div class="absolute w-full text-center text-[0.65rem] leading-[14px] font-bold top-0 left-0 z-[2]"
                    :class="{ 'text-white': item.rainProb > 50, 'text-gray-700 dark:text-gray-300': item.rainProb <= 50 }">
                    {{ item.rainMm !== undefined ? item.rainMm + 'mm' : '-' }}
                </div>
            </div>
            <div class="text-[0.75rem] text-right font-bold dark:text-gray-300" :style="{ color: item.vpdColor }">{{ item.vpd }}</div>
        </div>

        <InfoModal :show="showModal" :title="trans('ui.weather_modal_title')" @close="showModal = false">
            <ul class="list-disc pl-5 space-y-2">
                <li>
                    <strong>{{ trans('ui.weather_modal_item1_label') }}</strong>
                    {{ trans('ui.weather_modal_item1_text') }}
                </li>
                <li>
                    <strong>{{ trans('ui.weather_modal_item2_label') }}</strong>
                    <div class="pl-2">
                        <div>{{ trans('ui.weather_modal_item2_line1') }}</div>
                        <div>{{ trans('ui.weather_modal_item2_line2') }}</div>
                    </div>
                </li>
                <li>
                    <strong>{{ trans('ui.weather_modal_item3_label') }}</strong>
                    <div class="pl-2">
                        <div>{{ trans('ui.weather_modal_item3_line1') }}</div>
                        <div>{{ trans('ui.weather_modal_item3_line2') }}</div>
                        <div>{{ trans('ui.weather_modal_item3_line3') }}</div>
                    </div>
                </li>
            </ul>
        </InfoModal>
    </div>
</template>
