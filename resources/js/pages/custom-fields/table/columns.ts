import { ColumnDef } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { CircleCheck, CircleMinus } from 'lucide-vue-next';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'label',
        header: () => h('div', { class: 'flex items-center text-left justify-start w-full' }, trans('ui.field_label')),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('label'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'key',
        header: () => h('div', { class: 'flex items-center text-left justify-start w-full' }, trans('ui.field_key')),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-mono text-sm' }, row.getValue('key'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'model_type',
        header: () => h('div', { class: 'flex items-center text-left justify-start w-full' }, trans('ui.model')),
        cell: ({ row }) => {
            const modelType: string = row.getValue('model_type');
            const modelName = modelType.split('\\').pop() || modelType;
            return h('div', { class: 'text-left' }, modelName);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'type',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.field_type')),
        cell: ({ row }) => {
            const type: string = row.getValue('type');
            return h('div', { class: 'text-center capitalize' }, type);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'is_required',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.required')),
        cell: ({ row }) => {
            const isRequired = row.getValue('is_required');
            return h(
                'div',
                { class: 'flex justify-center' },
                isRequired ? h(CircleCheck, { class: 'w-5 h-5 text-red-600' }) : h(CircleMinus, { class: 'w-5 h-5 text-gray-400' }),
            );
        },
        enableSorting: true,
        size: 80,
    },
    {
        accessorKey: 'order',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.order')),
        cell: ({ row }) => {
            const order: number = row.getValue('order');
            return h('div', { class: 'text-center font-mono' }, order);
        },
        enableSorting: true,
        size: 80,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const field = row.original;
            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    field,
                }),
            );
        },
    },
];
