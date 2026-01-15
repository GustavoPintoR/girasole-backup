import { ColumnDef } from '@tanstack/vue-table';
import dayjs from 'dayjs';
import { trans } from 'laravel-vue-i18n';
import { CircleCheck } from 'lucide-vue-next';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'title',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.event_title')),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('title'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'cadastral_group',
        header: () =>
            h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.cadastral_groups')),
        cell: ({ row }) => {
            const attendees = row.original.attendees ?? [];

            return h(
                'div',
                { class: 'flex flex-wrap justify-center gap-1' },
                attendees.length
                    ? attendees.map((group: any) =>
                        h(
                            'span',
                            {
                                class:
                                    'px-2 py-0.5 text-xs rounded-full bg-muted text-muted-foreground',
                                title: group.name,
                            },
                            group.name
                        )
                    )
                    : '—'
            );
        },
        enableSorting: false,
    },
    {
        accessorKey: 'all_day',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.event_all_day')),
        cell: ({ row }) => {
            const allDay = row.getValue('all_day');
            return h('div', { class: 'text-center font-medium' }, allDay ? h(CircleCheck, { class: 'mx-auto w-5 h-5 text-green-600' }) : '');
        },
        enableSorting: true,
        size: 50, // less width for icon column
    },
    {
        accessorKey: 'start_date',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.event_start_date')),
        cell: ({ row }) => {
            const startDate: string = row.getValue('start_date') || row.getValue('start');
            return h('div', { class: 'text-center font-mono' }, startDate ? dayjs(startDate).format('YYYY-MM-DD') : '—');
        },
        enableSorting: true,
    },
    {
        accessorKey: 'start',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.event_start_time')),
        cell: ({ row }) => {
            const start: string = row.getValue('start');

            return h('div', { class: 'text-center font-mono' }, start ? dayjs(start).format('HH:mm') : '—');
        },
        enableSorting: true,
    },
    {
        accessorKey: 'end_date',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.event_end_date')),
        cell: ({ row }) => {
            const endDate: string = row.getValue('end_date') || row.getValue('end');
            return h('div', { class: 'text-center font-mono' }, endDate ? dayjs(endDate).format('YYYY-MM-DD') : '—');
        },
        enableSorting: true,
    },
    {
        accessorKey: 'end',
        header: () => h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.event_end_time')),
        cell: ({ row }) => {
            const end: string = row.getValue('end');
            const allDay = row.getValue('all_day');

            return h('div', { class: 'text-center font-mono' }, allDay ? '—' : end ? dayjs(end).format('HH:mm') : '—');
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const event = row.original;
            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    event,
                }),
            );
        },
    },
];
