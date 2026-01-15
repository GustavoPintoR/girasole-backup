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

const props = defineProps<{
  cultivation: Cultivation;
}>();

const handleBack = () => {
  router.visit(route('cultivations.index'));
};

const handleEdit = () => {
  router.visit(route('cultivations.edit', props.cultivation.id));
};

const breadcrumbs = [
  { title: 'Cultivations', href: route('cultivations.index') },
  { title: props.cultivation.name, href: route('cultivations.show', props.cultivation.id) },
];

const { can } = usePermissions();
</script>

<template>
  <Head :title="props.cultivation.name" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ props.cultivation.name }}
          </h1>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_cultivations" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <!-- Cultivation Details -->
      <Card class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            {{ trans('ui.cultivation_details') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.name') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.cultivation.name }}
              </dd>
            </div>

            <div class="md:col-span-2">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.description') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.cultivation.description ?? '-' }}
              </dd>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Back link -->
      <div class="mt-6">
        <Link
          :href="route('cultivations.index')"
          class="inline-flex items-center space-x-1 text-sm text-muted-foreground hover:text-primary"
        >
          <LucideArrowLeft class="h-4 w-4" />
          <span>{{ trans('ui.back_to_list') }}</span>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
