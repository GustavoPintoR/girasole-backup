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
    postalCodes: {
        data: { id: number; code: string }[];
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
        title: trans('ui.postal_codes'),
        href: route('postal-codes.index'),
    },
];

const { can } = usePermissions();
</script>

<template>

    <Head :title="trans('ui.postal_codes')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                   {{ trans('ui.postal_codes') }}
                </h1>

                <Button v-if="can.create_postal_code" @click="router.visit(route('postal-codes.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable :route="route('postal-codes.index')" :columns="columns" :data="props.postalCodes.data"
                :pagination="{
                    pageIndex: props.postalCodes.current_page - 1,
                    pageSize: props.postalCodes.per_page,
                    pageCount: props.postalCodes.last_page,
                    total: props.postalCodes.total,
                    from: props.postalCodes.from,
                    to: props.postalCodes.to,
                }" :sorting="sorting" :filtering="filtering" 
                :search-placeholder="trans('ui.search_by_code')" />
        </div>
    </AppLayout>
</template>
