<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './table/columns';
import DataTable from '@/components/DataTable/DataTable.vue';
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { CustomField } from '@/types';

defineProps<{
    fields: {
        data: CustomField[];
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
    availableModels: Record<string, string>;
}>();

const breadcrumbs = [
    {
        title: trans('ui.custom_fields'),
        href: route('custom-fields.index'),
    },
];
</script>

<template>

    <Head :title="trans('ui.custom_fields')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.custom_fields') }}
                </h1>
                <Button @click="router.visit(route('custom-fields.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable :route="route('custom-fields.index')" :columns="columns" :data="fields.data" :pagination="{
                pageIndex: fields.current_page - 1,
                pageSize: fields.per_page,
                pageCount: fields.last_page,
                total: fields.total,
                from: fields.from,
                to: fields.to
            }" :sorting="sorting" :filtering="filtering"
                :search-placeholder="trans('ui.custom_field_search_placeholder')" />
        </div>
    </AppLayout>
</template>
