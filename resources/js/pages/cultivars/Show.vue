<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Edit, LucideArrowLeft } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
} from '@/components/ui/card';
import { usePermissions } from '@/composables/usePermissions';

interface Cultivation {
  id: number;
  name: string;
  description: string;
}

interface Cultivar {
  id: number;
  name: string;
  description: string;
  cultivation: Cultivation;
}

const props = defineProps<{
  cultivar: Cultivar;
  customFieldValues: Record<string, any>;
}>();

const handleBack = () => {
  router.visit(route('cultivars.index'));
};

const handleEdit = () => {
  router.visit(route('cultivars.edit', props.cultivar.id));
};

const breadcrumbs = [
  { title: 'Cultivars', href: route('cultivars.index') },
  { title: props.cultivar.name, href: route('cultivars.show', props.cultivar.id) },
];

const { can } = usePermissions();
</script>

<template>
  <Head :title="props.cultivar.name" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ props.cultivar.name }}
          </h1>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_cultivar" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <!-- Cultivar Details -->
      <Card class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            {{ trans('ui.cultivar_details') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.name') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.cultivar.name }}
              </dd>
            </div>

            <div class="md:col-span-2">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.description') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.cultivar.description ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.cultivation') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                <Link
                  v-if="props.cultivar.cultivation"
                  :href="route('cultivations.show', props.cultivar.cultivation.id)"
                  class="text-primary hover:underline"
                >
                  {{ props.cultivar.cultivation.name }}
                </Link>
                <span v-else>-</span>
              </dd>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Custom Fields -->
      <Card v-if="customFieldValues && Object.keys(customFieldValues).length > 0" class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle>{{ trans('ui.custom_fields') }}</CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="(field, key) in customFieldValues" :key="key">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ field.label }}
                <span v-if="field.unit" class="text-xs">({{ field.unit }})</span>
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ field.value || '—' }}
              </dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <!-- Back link -->
      <div class="mt-6">
        <Link
          :href="route('cultivars.index')"
          class="inline-flex items-center space-x-1 text-sm text-muted-foreground hover:text-primary"
        >
          <LucideArrowLeft class="h-4 w-4" />
          <span>{{ trans('ui.back_to_list') }}</span>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
