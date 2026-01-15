import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'type',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.type')),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('type'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'description',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.description')),
        cell: ({ row }) => {
            const desc = (row.getValue('description') ?? '-') as string;
            const text = desc.length > 150 ? desc.slice(0, 150) + '...' : desc;
            return h('div', { class: 'text-left font-medium' }, text);
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const irrigation = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    irrigation,
                }),
            );
        },
    },
];
