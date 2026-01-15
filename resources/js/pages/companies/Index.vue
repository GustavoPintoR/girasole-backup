<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './Table/columns'; // Define sensor table columns separately
import DataTable from '@/components/DataTable/DataTable.vue';
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

defineProps<{
  companies: {
    data: {
      id: number;
      name: string;
      description?: string;
      owner_id?: number | null;
      owner?: { id: number; full_name: string;} | null;
    }[];
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
    title: trans('ui.companies'),
    href: route('companies.index'),
  },
];

const { can } = usePermissions();
</script>

<template>
  <Head :title="trans('ui.companies')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ trans('ui.companies') }}
        </h1>
        <Button v-if="can.create_company" @click="router.visit(route('companies.create'))">
          <FilePlus2 class="mr-2 h-4 w-4" />
          {{ trans('ui.create_new') }}
        </Button>
      </div>

      <DataTable
        :route="route('companies.index')"
        :columns="columns"
        :data="companies.data"
        :pagination="{
          pageIndex: companies.current_page - 1,
          pageSize: companies.per_page,
          pageCount: companies.last_page,
          total: companies.total,
          from: companies.from,
          to: companies.to
        }"
        :sorting="sorting"
        :filtering="filtering"
        :search-placeholder="trans('ui.company_search_placeholder')"
      />
    </div>
  </AppLayout>
</template>
