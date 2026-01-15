import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';

type PlantDiseaseRow = {
    id: number;
    name: string;
    description?: string | null;
    created_at?: string;
};

export const columns: ColumnDef<PlantDiseaseRow>[] = [
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
            const display = value.length > 50 ? value.slice(0, 50) + '...' : value;
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
            const plantDisease = row.original;

            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    plantDisease,
                }),
            );
        },
    },
];
