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
    users: {
        data: { id: number; first_name: string, last_name: string, email: string, mobile_phone: string, active: number, accepted_at: string, deleted_at: string,subscription: {stripe_status: string, type: string, ends_at: string}, roles:{name:string}, termsAndConditions: {version:string} }[];
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
        title: trans('ui.users'),
        href: route('users.index'),
    },
];

const { can } = usePermissions();
</script>

<template>

    <Head :title="trans('ui.users')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                   {{ trans('ui.users') }}
                </h1>

                <Button v-if="can.create_user" @click="router.visit(route('users.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable :route="route('users.index')" :columns="columns" :data="props.users.data"
                :pagination="{
                    pageIndex: props.users.current_page - 1,
                    pageSize: props.users.per_page,
                    pageCount: props.users.last_page,
                    total: props.users.total,
                    from: props.users.from,
                    to: props.users.to,
                }" :sorting="sorting" :filtering="filtering" :search-placeholder="trans('ui.search')" />
        </div>
    </AppLayout>
</template>
