<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';

import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
} from '@/components/ui/card';

import { Edit, Eye, LucideArrowLeft, ArrowLeft } from 'lucide-vue-next';
import { usePermissions } from '@/composables/usePermissions';

// Props
const props = defineProps<{
  region: {
    id: number;
    name: string;
    code: string;
    provinces_count: number;
    cities_count: number;
  };
  provinces: {
    id: number;
    name: string;
    code: string;
    cities_count: number;
  }[];
  cities: {
    id: number;
    name: string;
    cadastral_code: string;
    province_id: number;
  }[];
  selectedProvider: {
    id: number;
  }
}>();

// Province filter
const selectedProvinceId = ref<number | null>(null);
function filterCities(id: number | null) {
  selectedProvinceId.value = id;
}

// function deleteRegion() {
//   router.delete(route('regions.destroy', props.region.id), { preserveScroll: true });
// }

const filteredCities = computed(() => {
  return selectedProvinceId.value
    ? props.cities.filter(c => c.province_id === selectedProvinceId.value)
    : props.cities;
});

const handleBack = () => {
  router.visit(route('regions.index'));
};

const handleEdit = () => {
  router.visit(route('regions.edit', props.region.id));
};

onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  const provId = params.get('province_id');
  if (provId) {
    selectedProvinceId.value = parseInt(provId, 10);
  }
});

const breadcrumbs = [
  { title: 'Regions', href: route('regions.index') },
  { title: props.region.name, href: route('regions.show', props.region.id) }
];

const { can } = usePermissions();
</script>

<template>
  <Head :title=" props.region.name " />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ props.region.name }}
          </h1>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_region" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Region Details -->
        <Card class="xl:col-span-1">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              {{ trans('ui.region_details') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.name') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ region.name }}
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.code') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ region.code }}
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.provinces_count') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ region.provinces_count }}
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.cities_count') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ region.cities_count }}
                </dd>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Provinces List -->
        <Card class="xl:col-span-1">
          <CardHeader>
            <CardTitle>
              {{ trans('ui.provinces') }} ({{ props.provinces.length }})
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-1 max-h-80 overflow-y-auto">
            <div
              v-for="prov in props.provinces"
              :key="prov.id"
              class="flex justify-between items-center py-1 cursor-pointer transition"
              :class="prov.id === selectedProvinceId
                ? 'bg-blue-50 dark:bg-blue-900'
                : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
              @click="filterCities(prov.id)"
            >
              <span class="flex-1">{{ prov.name }}</span>
              <span class="w-12 text-center">{{ prov.cities_count }}</span>
              <span class="w-8 flex justify-center">
                <Link :href="route('provinces.show', prov.id)" class="text-primary hover:underline">
                  <Eye class="h-4 w-4" />
                </Link>
              </span>
            </div>
          </CardContent>
        </Card>

        <!-- Cities List -->
        <Card class="xl:col-span-1">
          <CardHeader>
            <CardTitle>
              {{ trans('ui.cities') }} ({{ filteredCities.length }})
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-1 max-h-80 overflow-y-auto">
            <div
              class="flex justify-between items-center py-1 cursor-pointer transition"
              :class="selectedProvinceId === null
                ? 'bg-gray-50 dark:bg-gray-900'
                : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
              @click="filterCities(null)"
            >
              <span class="flex-1 italic text-gray-600 dark:text-gray-400">
                {{ trans('ui.all_cities') }}
              </span>
              <span class="w-12"></span>
              <span class="w-8"></span>
            </div>

            <div
              v-for="city in filteredCities"
              :key="city.id"
              class="flex justify-between items-center py-1"
            >
              <span class="flex-1">{{ city.name }}</span>
              <span class="w-8 flex justify-center">
                <Link :href="route('cities.show', city.id)" class="text-primary hover:underline">
                  <Eye class="h-4 w-4" />
                </Link>
              </span>
            </div>

            <div v-if="!filteredCities.length" class="py-4 text-center text-gray-500 dark:text-gray-400">
              {{ trans('ui.no_cities_found') }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Back link -->
      <div class="mt-6">
        <Link
          :href="route('regions.index')"
          class="inline-flex items-center space-x-1 text-sm text-muted-foreground hover:text-primary"
        >
          <LucideArrowLeft class="h-4 w-4" />
          <span>{{ trans('ui.back_to_list') }}</span>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>