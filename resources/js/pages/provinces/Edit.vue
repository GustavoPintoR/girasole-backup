<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { Search } from 'lucide-vue-next'; 
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import { trans } from 'laravel-vue-i18n';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

import { useDebounceFn } from '@vueuse/core';
import CardTitle from '@/components/ui/card/CardTitle.vue';

interface Province {
  id: number;
  name: string;
  code: string;
  region_id: number;
  region: { id: number; name: string; code: string }
};
interface Region { id: number; name: string; code: string }
interface City {
  id: number;
  name: string;
  cadastral_code: string;
  province_id: number | null;
}
interface ServerFilterState {
  search?: string;
  searchableColumns?: string[];
}
const props = defineProps<{
  province: Province;
  regions: Region[];
  cities: City[];
  filtering?: ServerFilterState;
}>();

const breadcrumbs = [
  { title: 'Regions', href: route('provinces.index') },
  { title: props.province.name, href: route('provinces.show', props.province.id) },
  { title: 'Edit', href: route('provinces.edit', props.province.id) },
];

const form = useForm({
  name: props.province.name,
  code: props.province.code,
  region_id: props.province.region_id,
});

function submit() {
  form.put(route('provinces.update', props.province.id), {
    preserveScroll: true,
    onSuccess: () => {
    }
  });
}
const cancel = () => {
  router.visit(route('provinces.index'));
};


const attachOpen = ref(false);
const availableError = ref<string | null>(null);
const availableSearch = ref('');
const selectedCityIds = ref<number[]>([]);
const attaching = ref(false);

const toggleSelected = (id: number) => {
  const i = selectedCityIds.value.indexOf(id);
  if (i >= 0) selectedCityIds.value.splice(i, 1);
  else selectedCityIds.value.push(id);
};

const resetAttachState = () => {
  selectedCityIds.value = [];
  availableSearch.value = '';
  availableError.value = null;
};

const searchValue = ref(props.filtering?.search || '');

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

const canAttach = computed(() => selectedCityIds.value.length > 0 && !attaching.value);

const submitAttach = () => {
  if (!selectedCityIds.value.length) return;
  attaching.value = true;
  router.post(
    route('provinces.attach-cities', props.province.id),
    { city_ids: selectedCityIds.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        resetAttachState();
        attachOpen.value = false;
      },
      onError: () => {
      },
      onFinish: () => {
        attaching.value = false;
      },
    }
  );
};
</script>
<template>

  <Head title="Edit Region" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.edit_province') }}
      </h1>

      <!-- Province form -->
      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }}</Label>
              <Input id="name" v-model="form.name" type="text" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="code">{{ trans('ui.code') }}</Label>
              <Input id="code" v-model="form.code" type="text" :class="{ 'border-destructive': form.errors.code }" />
              <p v-if="form.errors.code" class="text-sm text-destructive">
                {{ form.errors.code }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="region_id">{{ trans('ui.region') }}</Label>

              <Select v-model="form.region_id" :required="true" :disabled="!regions?.length">
                <SelectTrigger id="region_id" :class="[
                  'w-full',
                  form.errors.region_id ? 'border-destructive ring-1 ring-destructive' : ''
                ]" aria-invalid="true" v-if="form.errors.region_id">
                  <SelectValue :placeholder="trans('ui.select_region')" />
                </SelectTrigger>

                <SelectTrigger id="region_id" class="w-full" v-else>
                  <SelectValue :placeholder="trans('ui.select_region')" />
                </SelectTrigger>

                <SelectContent>
                  <SelectItem v-for="region in regions" :key="region.id" :value="String(region.id)">
                    {{ region.name }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <p v-if="form.errors.region_id" class="text-sm text-destructive">
                {{ form.errors.region_id }}
              </p>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') || 'Cancel' }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.updating') : trans('ui.update_province') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <Card class="max-w-4xl">
        <CardHeader class="flex items-center justify-between w-full gap-x-4">
          <CardTitle>{{ trans('ui.attach_cities') || 'Attach Cities' }}</CardTitle>
          <div class="flex items-center">
            <div class="relative max-w-sm">
              <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input v-model="searchValue" placeholder="Search..." class="pl-8" />
            </div>
          </div>
        </CardHeader>

        <CardContent class="flex flex-col h-96 ">
          <div class="flex-1 overflow-y-auto mb-4">
            <div v-for="city in filteredCities" :key="city.id" class="flex justify-between items-center py-1">
              <span class="flex-1 cursor-pointer" @click="toggleSelected(city.id)">{{ city.name }}</span>
              <span class="w-8 flex justify-center">
                <input type="checkbox" class="h-4 w-4 mr-6" :checked="selectedCityIds.includes(city.id)"
                  @change="toggleSelected(city.id)" :aria-label="`Select ${city.name}`">
              </span>
            </div>
            <div v-if="!props.cities.length" class="py-4 text-center text-gray-500 dark:text-gray-400">
              {{ trans('ui.no_cities_available') }}
            </div>
          </div>

          <div class="flex items-center justify-end space-x-2 pt-4">
            <Button variant="outline" @click="() => { resetAttachState(); attachOpen = false; }">
              {{ trans('ui.cancel') || 'Cancel' }}
            </Button>
            <Button :disabled="!canAttach" @click="submitAttach">
              {{ attaching ? (trans('ui.saving') || 'Saving...') : (trans('ui.attach') || 'Attach') }}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>