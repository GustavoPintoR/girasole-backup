// resources/js/pages/plans/columns.ts
import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n'

export type PlanRow = {
  id: number
  name: string
  slug: string
  interval: 'monthly' | 'yearly'
  currency: string
  unit_amount: number // cents
  active: boolean
  stripe_price_id: string
}

export const columns: ColumnDef<PlanRow>[] = [
  {
    accessorKey: 'name',
    header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.name')),
    cell: ({ row }) => h('div', { class: 'text-left font-medium' }, row.getValue('name') as string),
    enableSorting: true,
  },
  {
    accessorKey: 'interval',
    header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.interval')),
    cell: ({ row }) => h('div', { class: 'text-center font-medium' }, row.getValue('interval') as string),
    enableSorting: true,
  },
  {
    accessorKey: 'unit_amount',
    header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.price')),
    cell: ({ row }) => {
      const cents = row.getValue('unit_amount') as number
      const currency = row.original.currency || 'EUR'
      const formatted = new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(cents / 100)
      return h('div', { class: 'text-center font-medium' }, formatted)
    },
    enableSorting: true,
  },
  {
    accessorKey: 'active',
    header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.status')),
    cell: ({ row }) => {
      const active = row.getValue('active') as boolean
      return h('span', { class: active ? 'text-green-600' : 'text-zinc-500' }, active ? trans('ui.active') : trans('ui.inactive'))
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    header: () => h('div'),
    enableHiding: false,
    enableSorting: false,
    cell: ({ row }) => {
      const plan = row.original
      return h('div', { class: 'relative flex items-center justify-end' }, h(DropdownAction, { plan }))
    },
  },
]
