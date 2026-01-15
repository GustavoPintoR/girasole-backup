import Badge from '@/components/ui/badge/Badge.vue';
import { ColumnDef } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'version',
        header: () => h('div', trans('ui.version')),
        cell: ({ row }) => {
            return h('div', row.getValue('version'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'is_active',
        header: () => h('div', trans('ui.status')),
        cell: ({ row }) => {
            return h(
                Badge,
                { class: row.getValue('is_active') ? ' bg-green-700' : ' bg-gray-400' },
                {
                    default: () => (row.getValue('is_active') ? String(trans('ui.active')) : trans('ui.inactive')),
                },
            );
        },
        enableSorting: true,
    },
    {
        accessorKey: 'active_at',
        header: () => h('div', trans('ui.active_at')),
        cell: ({ row }) => {
            return h('div', row.getValue('active_at'));
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const term = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    term,
                }),
            );
        },
    },
];
