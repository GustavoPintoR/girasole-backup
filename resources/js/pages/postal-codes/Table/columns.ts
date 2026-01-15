import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'code',
        header: () => h('div', trans('ui.code')),
        cell: ({ row }) => {
            return h('div', row.getValue('code'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'cities',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-100%' }, trans('ui.cities')),
        cell: ({ row }) => {
            const cities = row.original.cities ?? [];
            const names = Array.isArray(cities) ? cities.map(c => c.name).filter(Boolean) : [];
            const joined = names.join(', ');
            const text = joined.length > 100 ? joined.slice(0, 100) + '...' : joined;

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
            const postalCode = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    postalCode,
                }),
            );
        },
    },
];
