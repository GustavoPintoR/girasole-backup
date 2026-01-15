<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
} from '@/components/ui/card';
import { Search, Eye, ArrowLeft, Edit, LucideArrowLeft } from 'lucide-vue-next';
import Input from '@/components/ui/input/Input.vue';
import { useDebounceFn } from '@vueuse/core';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

interface Province {
  id: number;
  name: string;
  code: string;
  region: { id: number; name: string; code: string };
}

interface Region {
  id: number;
  name: string;
  code: string;
}

interface City {
  id: number;
  name: string;
  cadastral_code: string;
  province_id: number;
}

interface ServerFilterState {
  search?: string;
  searchableColumns?: string[];
}

// Props
const props = defineProps<{
  region: Region;
  province: Province;
  cities: City[];
  filtering?: ServerFilterState;
}>();

const searchValue = ref(props.filtering?.search || '');

const handleBack = () => {
  router.visit(route('provinces.index'));
};

const handleEdit = () => {
  router.visit(route('provinces.edit', props.province.id));
};

const handleSearch = useDebounceFn((value: string) => {
  searchValue.value = value;
}, 300);

watch(searchValue, (newValue) => {
  handleSearch(newValue);
});

const filteredCities = computed(() => {
  if (!searchValue.value) {
    return props.cities;
  }
  const searchLower = searchValue.value.toLowerCase();
  return props.cities.filter(city =>
    city.name.toLowerCase().includes(searchLower)
  );
});

const breadcrumbs = [
  { title: 'Provinces', href: route('provinces.index') },
  { title: props.province.name, href: route('provinces.show', props.province.id) },
];

const { can } = usePermissions();
</script>

<template>
  <Head :title="props.province.name" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ props.province.name }}
          </h1>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_province" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Province Details -->
        <Card class="xl:col-span-1">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              {{ trans('ui.province_details') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.name') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ props.province.name }}
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.code') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ props.province.code }}
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.cities') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ props.cities.length }}
                </dd>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Cities -->
        <Card class="xl:col-span-2">
          <CardHeader class="flex items-center justify-between w-full gap-x-4">
            <CardTitle>
              {{ trans('ui.cities') }} ({{ props.cities.length }})
            </CardTitle>
            <div class="flex items-center">
              <div class="relative max-w-sm">
                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input v-model="searchValue" :placeholder="trans('ui.search')" class="pl-8" />
              </div>
            </div>
          </CardHeader>
          <CardContent class="space-y-1 max-h-80 overflow-y-auto">
            <div
              v-for="city in filteredCities"
              :key="city.id"
              class="flex justify-between items-center py-1"
            >
              <span class="flex-1">
                {{ city.name }}
              </span>
              <span class="w-8 flex justify-center">
                <Link :href="route('cities.show', city.id)" class="text-primary hover:underline">
                  <Eye class="h-4 w-4" />
                </Link>
              </span>
            </div>

            <div v-if="!props.cities.length" class="py-4 text-center text-gray-500 dark:text-gray-400">
              {{ trans('ui.no_cities_found') }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Back link -->
      <div class="mt-6">
        <Link
          :href="route('provinces.index')"
          class="inline-flex items-center space-x-1 text-sm text-muted-foreground hover:text-primary"
        >
          <LucideArrowLeft class="h-4 w-4" />
          <span>{{ trans('ui.back_to_list') }}</span>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
