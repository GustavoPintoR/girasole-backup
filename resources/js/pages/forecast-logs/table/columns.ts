import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';
import { format, parseISO } from 'date-fns';
import Badge from '../../../components/ui/badge/Badge.vue';
import { it } from 'date-fns/locale';

export const columns: ColumnDef<any>[] = [
    {
        id: 'field_name',
        accessorKey: 'field.name',
        header: () => h('div', { class: 'font-semibold' }, trans('ui.field')),
        cell: ({ row }) => {
            const value = `${row.original.field.name} (${row.original.field.id})`;
            return h('div', value)
        }
    },
    {
        accessorKey: 'status',
        header: () => h('div', trans('ui.status')),
        cell: ({ row }) => {
            return h(
                Badge,
                { class: row.getValue('status') == 'success' ? ' bg-green-700' : ' bg-red-500' },
                {
                    default: () => (row.getValue('status') == 'success') ? trans('ui.success'): trans('ui.failed'),
                },
            );
        },
        enableSorting: true,
    },
    {
        accessorKey: 'ran_at',
        header: () => h('div', trans('ui.executed_at')),
        cell: ({ row }) => {
            const runAt = row.getValue('ran_at');
            return h(
                Badge,
                { class: runAt ? ' bg-green-700' : ' bg-gray-400' },
                {
                    default: () => runAt ? format(parseISO(runAt as string), 'PPP', { locale: it }) : trans('ui.not_executed'),
                },
            );
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const forecastLog = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    forecastLog,
                }),
            );
        },
    },
];
