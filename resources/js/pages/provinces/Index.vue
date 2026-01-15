<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './Table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

defineProps<{
    provinces: {
        data: { id: number; name: string; code: string; region: { id: number; name: string; code: string } }[];
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
        title: trans('ui.provinces'),
        href: route('provinces.index'),
    },
];

const { can } = usePermissions();
</script>

<template>
    <Head title="Provinces" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.provinces') }}
                </h1>
                <Button v-if="can.create_province" @click="router.visit(route('provinces.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable
                :route="route('provinces.index')"
                :columns="columns"
                :data="provinces.data"
                :pagination="{
                    pageIndex: provinces.current_page - 1,
                    pageSize: provinces.per_page,
                    pageCount: provinces.last_page,
                    total: provinces.total,
                    from: provinces.from,
                    to: provinces.to,
                }"
                :sorting="sorting"
                :filtering="filtering"
                :search-placeholder="trans('ui.search_by_name_code')"
            />
        </div>
    </AppLayout>
</template>
