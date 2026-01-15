<script setup lang="ts">
import { ref } from 'vue';
import InfoModal from './InfoModal.vue';
import { trans } from 'laravel-vue-i18n';

defineProps<{
    rainTotal: number;
    evapotranspiration: number;
    deficit: number;
    deficitColor?: string;
}>();

const showModal = ref(false);
</script>

<template>
    <div
        class="bg-card rounded-xl p-[18px] border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col relative lg:col-span-2">
        <div
            class="text-[0.7rem] font-bold text-gray-500 dark:text-gray-300 uppercase mb-[15px] tracking-[0.5px] border-b border-gray-100 dark:border-gray-500 pb-2 flex justify-between items-center">
            <span>{{ trans('ui.water_balance_title') }}</span>
            <button
                class="bg-none border-none cursor-pointer text-gray-400 transition-colors duration-200 p-1 hover:text-blue-500"
                @click="showModal = true">
                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px] fill-current">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                </svg>
            </button>
        </div>
        <div class="flex justify-around items-center h-full">
            <div class="text-center">
                <div class="text-[1.8rem] font-bold text-blue-700 leading-[1.2]">
                    {{ rainTotal }}<span class="text-[1rem] font-medium text-gray-500 dark:text-gray-300">mm</span>
                </div>
                <div class="text-[0.8rem] text-gray-500 dark:text-gray-300 mt-1">{{ trans('ui.rain_total') }}</div>
            </div>
            <div class="text-center">
                <div class="text-[1.8rem] font-bold text-amber-500 leading-[1.2]">
                    {{ evapotranspiration }}<span
                        class="text-[1rem] font-medium text-gray-500 dark:text-gray-300">mm</span>
                </div>
                <div class="text-[0.8rem] text-gray-500 dark:text-gray-300 mt-1">
                    {{ trans('ui.evapotranspiration_short') }}</div>
            </div>
            <div class="text-center">
                <div class="text-[1.8rem] font-bold leading-[1.2]" :class="deficitColor || 'text-red-500'">
                    {{ deficit }}<span class="text-[1rem] font-medium text-gray-500 dark:text-gray-300">mm</span>
                </div>
                <div class="text-[0.8rem] text-gray-500 dark:text-gray-300 mt-1">{{ trans('ui.deficit_expected') }}
                </div>
            </div>
        </div>

        <InfoModal :show="showModal" :title="trans('ui.water_balance_modal_title')" @close="showModal = false">
            <ul class="list-disc pl-5 space-y-2 text-gray-900 dark:text-gray-300">
                <li>
                    <strong>{{ trans('ui.water_balance_modal_item1_label') }}</strong>
                    {{ trans('ui.water_balance_modal_item1_text') }}
                </li>

                <li>
                    <strong>{{ trans('ui.water_balance_modal_item2_label') }}</strong>
                    {{ trans('ui.water_balance_modal_item2_text') }}
                </li>

                <li>
                    <strong>{{ trans('ui.water_balance_modal_item3_label') }}</strong>
                    {{ trans('ui.water_balance_modal_item3_text') }}
                    <em>{{ trans('ui.water_balance_modal_item3_emphasis') }}</em>
                </li>
            </ul>
        </InfoModal>
    </div>
</template>
