<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { columns } from './Table/columns'; // Define event table columns separately
import DataTable from '@/components/DataTable/DataTable.vue';
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { CadastralGroup } from '@/types';

defineProps<{
  events: {
    data: {
      id: number;
      title: string;
      all_day: boolean;
      start: string;
      end: string;
      description?: string;
      user_id?: number | null;
      attendees:CadastralGroup | null;
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
    title: trans('ui.events'),
    href: route('events.index'),
  },
];

const { can } = usePermissions();
</script>

<template>
  <Head :title="trans('ui.events')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ trans('ui.events') }}
        </h1>
        <Button v-if="can.create_event" @click="router.visit(route('events.create'))">
          <FilePlus2 class="mr-2 h-4 w-4" />
          {{ trans('ui.create_new') }}
        </Button>
      </div>

      <DataTable
        :route="route('events.index')"
        :columns="columns"
        :data="events.data"
        :pagination="{
          pageIndex: events.current_page - 1,
          pageSize: events.per_page,
          pageCount: events.last_page,
          total: events.total,
          from: events.from,
          to: events.to
        }"
        :sorting="sorting"
        :filtering="filtering"
        :search-placeholder="trans('ui.event_search_placeholder')"
      />
    </div>
  </AppLayout>
</template>
