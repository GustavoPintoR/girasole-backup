import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: () => h('div', trans('ui.name')),
        cell: ({ row }) => {
            return h('div', row.getValue('name'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'pattern',
        header: () => h('div', trans('ui.pattern')),
        cell: ({ row }) => {
            return h('div', trans(`ui.${row.getValue('pattern')}`));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'distance',
        header: () => h('div', trans('ui.distance')),
        cell: ({ row }) => {
            return h('div', row.getValue('distance'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'description',
        header: () => h('div', trans('ui.description')),
        cell: ({ row }) => {
            const value = (row.getValue('description') as string | null) ?? '';
            const display = value.length > 50 ? value.slice(0, 50) + '...' : value;
            return h('div', display);
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const plantingScheme = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    plantingScheme,
                }),
            );
        },
    },
];
