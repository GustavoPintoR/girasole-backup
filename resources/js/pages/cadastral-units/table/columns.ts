import { usePage } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { h } from 'vue';
import DataTableActions from './DataTableActions.vue';
import { sqmToHa } from '@/utils/conversions';

type City = {
    id: number;
    name: string;
    cadastral_code: string;
    region: Region;
    province: Province;
};

type Region = {
    id: number;
    name: string;
    code: string;
};

type Province = {
    id: number;
    name: string;
    code: string;
};

const page = usePage();

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'sheet',
        header: () => h('div', trans('ui.sheet')),
        cell: ({ row }) => {
            return h('div', row.getValue('sheet'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'parcel',
        header: () => h('div', trans('ui.parcel')),
        cell: ({ row }) => {
            return h('div', row.getValue('parcel'));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'cadastral_area',
        header: () => h('div', trans('ui.cadastral_area') + ' (ha)'),
        cell: ({ row }) => {
            return h('div', row.getValue('cadastral_area') ? sqmToHa(row.getValue('cadastral_area')) : "-");
        },
        enableSorting: true,
    },
    {
            accessorKey: 'user',
            header: () => h('div', trans('ui.owner')),
            cell: ({ row }) => {
                const owner = row.getValue<any>('user');
                const pageProps = page.props as any;
                const currentUserId = pageProps.auth?.user?.id ?? null;

                if (!owner) {
                    return h('div', { class: 'text-gray-400' }, '—');
                }

                if (currentUserId && owner.id === currentUserId) {
                    return h('div', { class: 'text-sm font-bold' }, owner.full_name);
                } else {
                    return h('div', { class: 'text-sm' }, owner.full_name);
                }
            },
            enableSorting: false,
        },
    {
        accessorKey: 'region',
        header: () => h('div', trans('ui.region')),
        cell: ({ row }) => {
            return h('div', row.getValue<City>('city').region.name);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'province',
        header: () => h('div', trans('ui.province')),
        cell: ({ row }) => {
            return h('div', row.getValue<City>('city').province.name);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'city',
        header: () => h('div', trans('ui.city')),
        cell: ({ row }) => {
            return h('div', row.getValue<City>('city').name);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'created_at',
        header: () => h('div', trans('ui.created_at')),
        cell: ({ row }) => {
            return h('div', format(parseISO(row.getValue('created_at')), 'PPP'));
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div', trans('ui.actions')),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const cadastral_unit = row.original;

            return h(DataTableActions, {
                cadastral_unit,
            });
        },
    },
];
