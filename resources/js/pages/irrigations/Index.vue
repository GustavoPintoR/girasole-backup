<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './Table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { Irrigation } from '@/types';

const props = defineProps<{
    irrigations: {
        data: Irrigation[];
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
        title: trans('ui.irrigations'),
        href: route('irrigations.index'),
    },
];

const { can } = usePermissions();
</script>

<template>
    <Head :title="trans('ui.irrigations')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.irrigations') }}
                </h1>

                <Button v-if="can.create_irrigation" @click="router.visit(route('irrigations.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable
                :route="route('irrigations.index')"
                :columns="columns"
                :data="props.irrigations.data"
                :pagination="{
                    pageIndex: props.irrigations.current_page - 1,
                    pageSize: props.irrigations.per_page,
                    pageCount: props.irrigations.last_page,
                    total: props.irrigations.total,
                    from: props.irrigations.from,
                    to: props.irrigations.to,
                }"
                :sorting="sorting"
                :filtering="filtering"
                :search-placeholder="trans('ui.search')"
            />
        </div>
    </AppLayout>
</template>
