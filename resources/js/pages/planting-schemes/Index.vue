<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './Table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { PlantingScheme } from '@/types';

const props = defineProps<{
    plantingSchemes: {
        data: PlantingScheme[];
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
        title: trans('ui.planting_schemes'),
        href: route('planting-schemes.index'),
    },
];

const { can } = usePermissions();
</script>

<template>

    <Head :title="trans('ui.planting_schemes')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                   {{ trans('ui.planting_schemes') }}
                </h1>

                <Button v-if="can.create_planting_scheme" @click="router.visit(route('planting-schemes.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable :route="route('planting-schemes.index')" :columns="columns" :data="props.plantingSchemes.data"
                :pagination="{
                    pageIndex: props.plantingSchemes.current_page - 1,
                    pageSize: props.plantingSchemes.per_page,
                    pageCount: props.plantingSchemes.last_page,
                    total: props.plantingSchemes.total,
                    from: props.plantingSchemes.from,
                    to: props.plantingSchemes.to,
                }" :sorting="sorting" :filtering="filtering" :search-placeholder="trans('ui.search')" />
        </div>
    </AppLayout>
</template>
