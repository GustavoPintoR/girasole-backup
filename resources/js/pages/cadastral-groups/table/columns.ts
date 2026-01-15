import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Badge } from '@/components/ui/badge';
import { CadastralGroup, Company, User } from '@/types';
import { sqmToHa } from '@/utils/conversions';
import { ColumnDef } from '@tanstack/vue-table';
import { format, parseISO } from 'date-fns';
import { enUS, it } from 'date-fns/locale';
import { trans } from 'laravel-vue-i18n';
import { h } from 'vue';
import DropdownAction from './DataTableDropdown.vue';

const page = usePage();

const locale = computed(() => {
    return page.props.appLocale === 'it' ? it : enUS;
});

function getCreationMethodLabel(method: string): string {
    switch (method) {
        case 'units':
            return trans('ui.from_units');
        case 'manual':
            return trans('ui.drawn');
        case 'import':
            return trans('ui.imported');
        default:
            return method;
    }
}

function getCreationMethodVariant(method: string): string {
    switch (method) {
        case 'units':
            return 'default';
        case 'manual':
            return 'secondary';
        case 'import':
            return 'outline';
        default:
            return 'outline';
    }
}

export const columns: ColumnDef<CadastralGroup>[] = [
    {
        accessorKey: 'name',
        header: () => h('div', trans('ui.name')),
        cell: ({ row }) => {
            const name = row.getValue<string>('name');
            const color = row.original.color;
            return h('div', { class: 'flex items-center gap-2' }, [
                h('div', {
                    class: 'w-3 h-3 rounded',
                    style: { backgroundColor: color },
                }),
                h('span', { class: 'font-medium' }, name),
            ]);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'user',
        header: () => h('div', trans('ui.owner')),
        cell: ({ row }) => {
            const owner = row.getValue<User>('user');
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
        accessorKey: 'creation_method',
        header: () => h('div', trans('ui.creation_method')),
        cell: ({ row }) => {
            const method = row.getValue<string>('creation_method');
            return h(Badge, { variant: getCreationMethodVariant(method) as any }, () => getCreationMethodLabel(method));
        },
        enableSorting: true,
    },
    {
        accessorKey: 'units_count',
        header: () => h('div', trans('ui.units_count')),
        cell: ({ row }) => {
            const count = row.getValue<number>('units_count');
            const method = row.original.creation_method;
            if (method !== 'units') {
                return h('div', { class: 'text-gray-400' }, '—');
            }
            return h('div', count);
        },
        enableSorting: true,
    },
    {
        accessorKey: 'total_area',
        header: () => h('div', trans('ui.total_area') + ' (ha)'),
        cell: ({ row }) => {
            const area = row.getValue<number>('total_area');
            return h('div', area ? sqmToHa(area) : '-');
        },
        enableSorting: true,
    },
    {
        accessorKey: 'company',
        header: () => h('div', trans('ui.company')),
        cell: ({ row }) => {
            const company = row.getValue<Company>('company');
            if (!company) {
                return h('div', { class: 'text-gray-400' }, '—');
            }
            return h('div', { class: 'text-sm font-medium' }, company.name);
        },
        enableSorting: false,
    },
    {
        accessorKey: 'created_at',
        header: () => h('div', trans('ui.created_at')),
        cell: ({ row }) => {
            return h('div', format(parseISO(row.getValue('created_at')), 'PPP', { locale: locale.value }));
        },
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => h('div'),
        enableHiding: false,
        enableSorting: false,
        cell: ({ row }) => {
            const group = row.original;
            return h(
                'div',
                { class: 'relative flex items-center justify-end' },
                h(DropdownAction, {
                    group,
                }),
            );
        },
    },
];
