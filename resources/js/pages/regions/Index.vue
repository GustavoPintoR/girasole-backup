<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './Table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps<{
    regions: {
        data: { id: number; name: string; code: number }[];
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
        title: trans('ui.regions'),
        href: route('regions.index'),
    },
];

const { can } = usePermissions();
</script>

<template>
    <Head title="Regions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.regions') }}
                </h1>
                <Button v-if="can.create_region" @click="router.visit(route('regions.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable :route="route('regions.index')" :columns="columns" :data="props.regions.data"
                :pagination="{
                    pageIndex: props.regions.current_page - 1,
                    pageSize: props.regions.per_page,
                    pageCount: props.regions.last_page,
                    total: props.regions.total,
                    from: props.regions.from,
                    to: props.regions.to,
                }" :sorting="sorting" :filtering="filtering" 
                :search-placeholder="trans('ui.search_by_name')" />
        </div>
    </AppLayout>
</template>
