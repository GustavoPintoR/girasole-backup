import { ColumnDef } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { Badge } from '@/components/ui/badge';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.company_name')),
        cell: ({ row }) => {
            const name = row.getValue('name');
            const isMain = row.original.is_main;

            return h('div', { class: 'text-left font-medium flex items-center gap-2' }, [
                name,
                isMain ? h(Badge, { variant: 'secondary', class: 'ml-2' }, () => trans('ui.main')) : null
            ]);
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
        accessorKey: 'owner',
        header: () =>
        h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.owner')),
        cell: ({ row }) => {
        const ownerName = row.original.owner ? row.original.owner?.first_name + ' ' + row.original.owner?.last_name || '-' : trans('ui.no_owner');

        return h('div', { class: 'text-center font-medium' }, ownerName);
        },
        enableSorting: false, // You can turn this on if you want
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const company = row.original;
            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    company,
                }),
            );
        },
    },
];
