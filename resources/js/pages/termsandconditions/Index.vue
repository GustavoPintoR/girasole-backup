<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps<{
    termsAndConditions: {
        data: { id: number; version: string; }[];
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
        title: trans('ui.terms_and_conditions'),
        href: route('terms-and-conditions.index'),
    },
];

const { can } = usePermissions();
</script>

<template>

    <Head :title="trans('ui.terms_and_conditions')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.terms_and_conditions') }}
                </h1>

                <Button v-if="can.create_terms_and_conditions" @click="router.visit(route('terms-and-conditions.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable :route="route('terms-and-conditions.index')" :columns="columns" :data="props.termsAndConditions.data"
                :pagination="{
                    pageIndex: props.termsAndConditions.current_page - 1,
                    pageSize: props.termsAndConditions.per_page,
                    pageCount: props.termsAndConditions.last_page,
                    total: props.termsAndConditions.total,
                    from: props.termsAndConditions.from,
                    to: props.termsAndConditions.to,
                }" :sorting="sorting" :filtering="filtering" :search-placeholder="trans('ui.search_by_version')" :striped="true" />
        </div>
    </AppLayout>
</template>
