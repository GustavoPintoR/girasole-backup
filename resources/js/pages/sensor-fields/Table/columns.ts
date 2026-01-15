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
        accessorKey: 'label',
        header: () => h('div', trans('ui.label')),
        cell: ({ row }) => {
            return h('div', row.getValue('label'));
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const sensorField = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    sensorField,
                }),
            );
        },
    },
];
