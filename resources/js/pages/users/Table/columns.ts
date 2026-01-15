import { ColumnDef } from '@tanstack/vue-table';
import { h, defineComponent, ref, onMounted } from 'vue';
import DropdownAction from './DataTableDropdown.vue';
import { trans } from 'laravel-vue-i18n';
import Badge from '../../../components/ui/badge/Badge.vue';

import axios from 'axios';
import { format } from 'date-fns';
import { it } from 'date-fns/locale';

const SubscriptionBadge = defineComponent({
  props: {
    userId: { type: [Number, String], required: true },
  },
  setup(props) {
    const type = ref<string | null>(null);
    // Per-user promise cache to avoid duplicate requests while rows mount
    const staticCache =
      (SubscriptionBadge as any)._cache ||
      ((SubscriptionBadge as any)._cache = new Map<number | string, Promise<string | null>>());

    onMounted(async () => {
      if (!staticCache.has(props.userId)) {
        staticCache.set(
          props.userId,
          axios
            .get<{ type: string | null }>(`/users/${props.userId}/subscription-type`, { withCredentials: true })
            .then((r) => r.data ?? null)
            .catch(() => null),
        );
      }
      type.value = await staticCache.get(props.userId);
    });


    return () => h('span', { class: 'inline-flex' }, type.value);
  },
});

export const columns: ColumnDef<any>[] = [
  {
    accessorKey: 'first_name',
    header: () => h('div', trans('ui.first_name')),
    cell: ({ row }) => {
      return h('div', row.getValue('first_name'));
    },
    enableSorting: true,
  },
  {
    accessorKey: 'last_name',
    header: () => h('div', trans('ui.last_name')),
    cell: ({ row }) => {
      return h('div', row.getValue('last_name'));
    },
    enableSorting: true,
  },
  {
    accessorKey: 'email',
    header: () => h('div', trans('ui.email')),
    cell: ({ row }) => {
      return h('div', row.getValue('email'));
    },
    enableSorting: true,
  },
  {
    accessorKey: 'mobile_number',
    header: () => h('div', trans('ui.mobile_phone')),
    cell: ({ row }) => {
      return h('div', row.getValue('mobile_number'));
    },
    enableSorting: true,
  },
  {
    accessorKey: 'active',
    header: () => h('div', trans('ui.status')),
    cell: ({ row }) => {
      const isTrashed = !!row.original.deleted_at;
      if (isTrashed) {
        return h(
          Badge,
          { variant: 'destructive' },
          { default: () => trans('ui.trashed') },
        );
      }
      return h(
        Badge,
        { class: row.getValue('active') ? ' bg-green-700' : ' bg-gray-400' },
        {
          default: () => (row.getValue('active') ? String(trans('ui.active')) : trans('ui.inactive')),
        },
      );
    },
    enableSorting: true,
  },
  // {
  //   accessorKey: 'subscriptions',
  //   header: () => h('div', trans('ui.subscription_ends_at')),
  //   cell: ({ row }) => {
  //     return h(
  //       Badge,
  //       { class: 'bg-gray-400' },
  //       {
  //         default: () => h(SubscriptionBadge, { userId: row.original.id }),
  //       },
  //     );
  //   },
  //   enableSorting: true,
  // },
  {
    accessorKey: 'subscriptions',
    header: () => h('div', trans('ui.subscription_ends_at')),
    cell: ({ row }) => {
      const subs = (row.original?.subscriptions ?? []) as Array<{
        stripe_status?: string;
        type?: string | null;
        ends_at?: string | null;
      }>;

      const active = subs.find((s) => s?.stripe_status === 'active');

      let formatted = '—';
      if (active) {
        const typeLabel =
          active.type === 'default'
            ? trans('ui.automatic')
            : active.type === 'manual'
            ? trans('ui.manual')
            : '—';

        const endsAt = active.ends_at
          ? format(new Date(active.ends_at), 'yyyy-MM-dd', { locale: it })
          : '—';

        formatted = `${typeLabel} (${endsAt})`;
      }

      return h(
        Badge,
        { class: 'bg-gray-400' },
        { default: () => formatted },
      );
    },
    enableSorting: true,
  },
  {
    accessorKey: 'role',
    header: () =>
      h('div', { class: 'flex items-center text-center justify-center w-full' }, trans('ui.role')),
    cell: ({ row }) => {
      const roleName = row.original.roles[0].name.charAt(0).toUpperCase() + row.original.roles[0].name.slice(1);
      return h('div', { class: 'text-center font-medium' }, roleName ?? '—');
    },
    enableSorting: false,
  },
  {
    accessorKey: 'termsAndConditions',
    header: () => h('div', trans('ui.terms_and_conditions')),
    cell: ({ row }) => {
      const { terms_and_conditions, accepted_at } = row.original
      const version = terms_and_conditions?.version
      const formatted = version
        ? `${version} (${accepted_at ?? '—'})`
        : '—'

      return h('div', { class: 'text-center font-medium' }, formatted)
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    header: () => h('div'),
    enableHiding: false,
    enableSorting: false,
    cell: ({ row }) => {
      const user = row.original;
      return h('div', { class: 'relative flex items-center justify-end' }, h(DropdownAction, { user }));
    },
  },
];
