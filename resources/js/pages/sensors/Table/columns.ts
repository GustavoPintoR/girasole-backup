import { ColumnDef } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';

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
        accessorKey: 'urn',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.urn')),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('urn'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'type',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.sensor_type')),
        cell: ({ row }) => {
            const ownerName = row.original.sensor_type?.name ?? '-';

            return h('div', { class: 'text-center font-medium' }, ownerName);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'firmware',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.firmware')),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('firmware'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'owner',
        header: () =>
        h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.owner')),
        cell: ({ row }) => {
        const ownerName = row.original.owner?.first_name + ' ' + row.original.owner?.last_name || '-';

        return h('div', { class: 'text-center font-medium' }, ownerName);
        },
        enableSorting: false,
    },
    {
        accessorKey: 'company',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.company')),
        cell: ({ row }) => {
            const companyName = row.original.company?.name || '-';

            return h('div', { class: 'text-center font-medium' }, companyName);
        },
        enableSorting: false,
    },
    {
        accessorKey: 'cadastral_group',
        header: () =>
            h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.cadastral_group')),
        cell: ({ row }) => {
            const fieldName = row.original.cadastral_group?.name || '-';

            return h('div', { class: 'text-center font-medium' }, fieldName);
        },
        enableSorting: false,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const sensor = row.original;
            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    sensor,
                }),
            );
        },
    },
];
