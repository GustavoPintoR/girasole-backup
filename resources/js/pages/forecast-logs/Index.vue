<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { columns } from './table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { trans } from 'laravel-vue-i18n';
import { ForecastLog } from '@/types';

const props = defineProps<{
    forecastLogs: {
        data: ForecastLog[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    sorting: {
        sortBy: string | null;
        sortOrder: 'asc' | 'desc' | null;
    };
    filtering: {
        search?: string;
        searchableColumns: string[];
    };
}>();

const breadcrumbs = [
    {
        title: trans('ui.forecast_logs'),
        href: route('forecast-logs.index'),
    },
];

</script>

<template>

    <Head :title="trans('ui.forecast_logs')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.forecast_logs') }}
                </h1>

            </div>

            <DataTable :route="route('forecast-logs.index')" :columns="columns" :data="props.forecastLogs.data"
                       :pagination="{
                    pageIndex: props.forecastLogs.current_page - 1,
                    pageSize: props.forecastLogs.per_page,
                    pageCount: props.forecastLogs.last_page,
                    total: props.forecastLogs.total,
                    from: props.forecastLogs.from,
                    to: props.forecastLogs.to,
                }" :sorting="sorting" :filtering="filtering"
                       :search-placeholder="trans('ui.search')" />
        </div>
    </AppLayout>
</template>
