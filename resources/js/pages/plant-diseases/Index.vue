<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './Table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { PlantDisease } from '@/types';

const props = defineProps<{
    plantDiseases: {
        data: PlantDisease[];
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
        title: trans('ui.plant_diseases'),
        href: route('plant-diseases.index'),
    },
];

const { can } = usePermissions();
</script>

<template>
    <Head :title="trans('ui.plant_diseases')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.plant_diseases') }}
                </h1>

                <Button v-if="can.create_plant_disease" @click="router.visit(route('plant-diseases.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable
                :route="route('plant-diseases.index')"
                :columns="columns"
                :data="props.plantDiseases.data"
                :pagination="{
                    pageIndex: props.plantDiseases.current_page - 1,
                    pageSize: props.plantDiseases.per_page,
                    pageCount: props.plantDiseases.last_page,
                    total: props.plantDiseases.total,
                    from: props.plantDiseases.from,
                    to: props.plantDiseases.to,
                }"
                :sorting="sorting"
                :filtering="filtering"
                :search-placeholder="trans('ui.search')"
            />
        </div>
    </AppLayout>
</template>
