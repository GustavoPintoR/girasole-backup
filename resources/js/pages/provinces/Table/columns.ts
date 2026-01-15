import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.name')),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('name'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'code',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.code')),
        cell: ({ row }) => {
            return h('div', { class: 'text-center font-medium' }, row.getValue('code'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'region',
        header: () =>
        h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.region')),
        cell: ({ row }) => {
        const regionName = row.original.region?.name ?? '—';
        return h('div', { class: 'text-center font-medium' }, regionName);
        },
        enableSorting: false, // You can turn this on if you want
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const province = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    province,
                }),
            );
        },
    },
];
