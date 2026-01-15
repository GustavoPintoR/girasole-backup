<script setup lang="ts">
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  ArrowLeft,
  Edit,
  User,
  Eye
} from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';
import { Company } from '@/types';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
  company: Company;
  selectedUserId?: number | null;
}>();

const emit = defineEmits<{ (e: 'select', userId: number): void }>()

const handleSelect = (id: number) => {
  emit('select', id)
}

const breadcrumbs = [
  {
    title: trans('ui.companies'),
    href: route('companies.index'),
  },
  {
    title: props.company.name,
    href: route('companies.show', props.company.id),
  },
];

const handleBack = () => {
  router.visit(route('companies.index'));
};

const handleEdit = () => {
  router.visit(route('companies.edit', props.company.id));
};

const { can } = usePermissions();
const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
</script>

<template>

  <Head :title="props.company.name" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            {{ props.company.name }} - {{ trans('ui.company') }}
            <Badge v-if="props.company.is_main" variant="secondary">
              {{ trans('ui.main') }}
            </Badge>
          </h1>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_company && currentUser && (currentUser.id === props.company.owner?.id || !props.company.owner)" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <Card class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            {{ trans('ui.company_info') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.name') }}</dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.company.name }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.description') }}</dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.company.description }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.owner') }}</dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                <span v-if="props.company.owner">
                  {{ props.company.owner.full_name || props.company.owner.first_name }}
                </span>
                <span v-else class="text-gray-400 dark:text-gray-500 italic">
                  {{ trans('ui.no_owner') }}
                </span>
              </dd>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Billing Info -->
      <Card class="max-w-4xl mb-6" v-if="props.company.billing_info">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            {{ trans('ui.billing_info') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.type') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ trans('ui.' + props.company.billing_info.fiscal_type) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.fiscal_code') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ props.company.billing_info.fiscal_code }}</dd>
            </div>
            <div v-if="props.company.billing_info.fiscal_type === 'business'">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.business_name') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ props.company.billing_info.business_name }}</dd>
            </div>
            <div v-if="props.company.billing_info.fiscal_type === 'business'">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.vat_number') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ props.company.billing_info.vat_number }}</dd>
            </div>
            <div v-if="props.company.billing_info.fiscal_type === 'business'">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.sdi_code') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ props.company.billing_info.sdi }}</dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <!-- Billing Address -->
      <Card class="max-w-4xl mb-6" v-if="props.company.billing_address">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            {{ trans('ui.billing_address') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.address') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.billing_address.street }} {{ props.company.billing_address.street_number }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.city') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.billing_address.city?.name }} ({{ props.company.billing_address.province?.name }})
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.postal_code') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.billing_address.postal_code?.code }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.region') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.billing_address.region?.name }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.state') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.billing_address.state }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.mobile_phone') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.billing_address.mobile_number }}
              </dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <!-- Shipping Address -->
      <Card class="max-w-4xl mb-6" v-if="props.company.shipping_address">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            {{ trans('ui.shipping_address') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="props.company.shipping_address.same_as_billing">
             <p class="text-sm text-gray-900 dark:text-gray-100">{{ trans('ui.same_as_billing_address') }}</p>
          </div>
          <dl v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.address') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.shipping_address.address }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.city') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.shipping_address.city?.name }} ({{ props.company.shipping_address.province?.name }})
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.postal_code') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.shipping_address.postal_code?.code }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.region') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.shipping_address.region?.name }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.name') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.shipping_address.name }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.mobile_phone') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.company.shipping_address.phone_number }}
              </dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <Card class="max-w-4xl mb-6" v-if="props.company.users?.length">
  <CardHeader>
    <CardTitle class="flex items-center gap-2">
      <User class="h-5 w-5" />
      {{ trans('ui.users') }} ({{ props.company.users.length }})
    </CardTitle>
  </CardHeader>

  <CardContent class="space-y-1 max-h-80 overflow-y-auto">
    <div
      v-for="u in props.company.users"
      :key="u.id"
      class="flex justify-between items-center py-1 cursor-pointer transition"
      :class="u.id === props.selectedUserId ? 'bg-blue-50 dark:bg-blue-900' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
      @click="handleSelect(u.id)"
    >
      <span class="flex-1 truncate">
        {{ u.full_name }}

        <span v-if="page.props.auth.isSuperAdmin" class="ml-2 text-sm text-muted-foreground truncate">{{ u.email }}</span>
      </span>

      <span v-if="page.props.auth.isSuperAdmin" class="w-8 flex justify-center">
        <Link :href="route('users.show', u.id)" class="inline-flex">
          <Eye class="h-4 w-4" />
        </Link>
      </span>
    </div>
  </CardContent>
</Card>

<Card class="max-w-4xl mb-6" v-else>
  <CardHeader>
    <CardTitle class="flex items-center gap-2">
      <User class="h-5 w-5" />
      {{ trans('ui.users') }}
    </CardTitle>
  </CardHeader>
  <CardContent>
    <p class="text-sm text-muted-foreground">
      {{ trans('ui.no_users_attached') }}
    </p>
  </CardContent>
</Card>

    </div>
  </AppLayout>
</template>
