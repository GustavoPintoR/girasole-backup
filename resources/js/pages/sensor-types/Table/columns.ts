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
        accessorKey: 'description',
        header: () => h('div', trans('ui.description')),
        cell: ({ row }) => {
            const value = (row.getValue('description') as string | null) ?? '';
            const display = value.length > 150 ? value.slice(0, 150) + '...' : value;
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
            const sensorType = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    sensorType,
                }),
            );
        },
    },
];
