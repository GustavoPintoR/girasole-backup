import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';
import Badge from '../../../components/ui/badge/Badge.vue';
import { format, parseISO } from 'date-fns';
import { it } from 'date-fns/locale';
import { User } from '@/types';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: () => h('div', trans('ui.api_key_name')),
        cell: ({ row }) => {
            return h('div', row.getValue('name'));
        },
        enableSorting: true,
    },
    //  {
    //     accessorKey: 'tokenable',
    //     header: () => h('div', trans('ui.user')),
    //     cell: ({ row }) => {
    //         const user = row.getValue<User>('tokenable');
    //         return h('div', user?.full_name);
    //     },
    //     enableSorting: true,
    // },
    // {
    //     accessorKey: 'abilities',
    //     header: () => h('div', trans('ui.api_key_abilities')),
    //     cell: ({ row }) => {
    //         return h('div', row.getValue('abilities'));
    //     },
    //     enableSorting: true,
    // },
    // {
    //     accessorKey: 'token',
    //     header: () => h('div', trans('ui.token')),
    //     cell: ({ row }) => {
    //         return h('div', row.getValue('token'));
    //     },
    //     enableSorting: true,
    // },
    {
        accessorKey: 'expires_at',
        header: () => h('div', trans('ui.expires_at')),
        cell: ({ row }) => {
            const expiresAt = row.getValue('expires_at');
            return h(
                Badge,
                { class: expiresAt ? ' bg-green-700' : ' bg-gray-400' },
                {
                    default: () => expiresAt ? format(parseISO(expiresAt as string), 'PPP', { locale: it }) : trans('ui.none'),
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
            const token = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    token,
                }),
            );
        },
    },
];
