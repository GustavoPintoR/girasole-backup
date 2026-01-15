import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';
import { format, parseISO } from 'date-fns';
import Badge from '../../../components/ui/badge/Badge.vue';
import { it } from 'date-fns/locale';

export const columns: ColumnDef<any>[] = [
    {
        accessorFn: (row) => {
            const data = typeof row.data === 'string' ? JSON.parse(row.data) : row.data;
            return data?.context?.title || '-';
        },
        id: 'title',
        header: () => h('div', trans('ui.title')),
        cell: ({ row }) => {
            try {
                const data = typeof row.original.data === 'string' ? JSON.parse(row.original.data) : row.original.data;
                return h('div', data?.context?.title || '-');
            } catch {
                return h('div', '-');
            }
        },
        enableSorting: true,
    },
    {
        accessorFn: (row) => {
            const data = typeof row.data === 'string' ? JSON.parse(row.data) : row.data;
            return data?.context?.title || '-';
        },
        id: 'description',
        header: () => h('div', trans('ui.description')),
        cell: ({ row }) => {
            try {
                const data = typeof row.original.data === 'string' ? JSON.parse(row.original.data) : row.original.data;
                const value = data?.context?.description ?? '';
                const tempElement = document.createElement('div');
                tempElement.innerHTML = value;
                const plainText = tempElement.textContent;
                const display = plainText.length > 150 ? plainText.slice(0, 150) + '...' : plainText;
                return h('div', display || '-');
            } catch {
                return h('div', '-');
            }
        },
        enableSorting: true,
    },
    {
        accessorKey: 'read_at',
        header: () => h('div', trans('ui.read_at')),
        cell: ({ row }) => {
            const readAt = row.getValue('read_at');
            return h(
                Badge,
                { class: readAt ? ' bg-green-700' : ' bg-gray-400' },
                {
                    default: () => readAt ? format(parseISO(readAt as string), 'PPP', { locale: it }) : trans('ui.unread'),
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
            const notification = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    notification,
                }),
            );
        },
    },
];
