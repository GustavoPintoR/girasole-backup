<script setup lang="ts">
import ForecastHeader from '@/components/forecast/ForecastHeader.vue';
import WaterBalanceCard from '@/components/forecast/WaterBalanceCard.vue';
import GrowthRisksCard from '@/components/forecast/GrowthRisksCard.vue';
import EvolutionTimelineCard from '@/components/forecast/EvolutionTimelineCard.vue';
import WeatherListCard from '@/components/forecast/WeatherListCard.vue';
import SoilListCard from '@/components/forecast/SoilListCard.vue';
import type { TimelineItem } from '@/components/forecast/EvolutionTimelineCard.vue';
import type { WeatherListItem } from '@/components/forecast/WeatherListCard.vue';
import type { SoilListItem } from '@/components/forecast/SoilListCard.vue';

defineProps<{
    header: {
        title: string;
        meta: string;
        date: string;
    };
    waterBalance: {
        rainTotal: number;
        evapotranspiration: number;
        deficit: number;
        deficitColor?: string;
    };
    growthRisks: {
        gdd: number;
        gddColor?: string;
        wetnessRisk: string;
        wetnessColor?: string;
        maxGusts: number;
        maxGustsColor?: string;
    };
    evolutionTimeline: TimelineItem[];
    weatherList: WeatherListItem[];
    soilList: SoilListItem[];
    hasWaterBalanceData?: boolean;
    hasGrowthRisksData?: boolean;
    hasEvolutionTimelineData?: boolean;
    hasWeatherListData?: boolean;
    hasSoilListData?: boolean;
}>();
</script>

<template>
    <div class="font-sans text-gray-900">
        <ForecastHeader v-bind="header" />

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-[15px] mx-auto">
            <!-- BILANCIO PERIODO (10 GG) -->
            <WaterBalanceCard v-if="hasWaterBalanceData" v-bind="waterBalance" />

            <!-- CRESCITA E RISCHI -->
            <GrowthRisksCard v-if="hasGrowthRisksData" v-bind="growthRisks" />

            <!-- EVOLUZIONE 10 GIORNI  -->
            <EvolutionTimelineCard v-if="hasEvolutionTimelineData" :items="evolutionTimeline" />

            <!-- METEO 10 GIORNI -->
            <WeatherListCard v-if="hasWeatherListData" :items="weatherList" />

            <!-- SUOLO 10 GIORNI -->
            <SoilListCard v-if="hasSoilListData" :items="soilList" />
        </div>
    </div>
</template>
