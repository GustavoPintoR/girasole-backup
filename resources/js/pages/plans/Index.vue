<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { columns } from './Table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button'
import { FilePlus2 } from 'lucide-vue-next'
import { trans } from 'laravel-vue-i18n'
import { usePermissions } from '@/composables/usePermissions'

defineProps<{
  plans: {
    data: {
      id: number
      name: string
      slug: string
      interval: 'monthly' | 'yearly'
      currency: string
      unit_amount: number
      active: boolean
      stripe_price_id: string
    }[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
  }
  sorting: {
    sortBy: string | null
    sortOrder: 'asc' | 'desc' | null
  }
  filtering: {
    search?: string
    searchableColumns: string[]
  }
}>()

const breadcrumbs = [
  { title: 'Plans', href: route('plans.index') },
]

const { can } = usePermissions()
</script>

<template>
  <Head :title="trans('ui.plans')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ trans('ui.plans') }}
        </h1>

        <Button v-if="can.create_plan" @click="router.visit(route('plans.create'))">
          <FilePlus2 class="mr-2 h-4 w-4" />
          {{ trans('ui.create_new') }}
        </Button>
      </div>

      <DataTable
        :route="route('plans.index')"
        :columns="columns"
        :data="plans.data"
        :pagination="{
          pageIndex: plans.current_page - 1,
          pageSize: plans.per_page,
          pageCount: plans.last_page,
          total: plans.total,
          from: plans.from,
          to: plans.to,
        }"
        :sorting="sorting"
        :filtering="filtering"
        :search-placeholder="trans('ui.search')"
      />
    </div>
  </AppLayout>
</template>
