<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { trans } from 'laravel-vue-i18n';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface Province {
  id: number;
  name: string;
  code: string;
  region: { id: number; name: string; code: string }
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
  region: Region;
  province: Province;
}

const props = defineProps<{
  provinces: Province[];
  regions: Region[];
  city: City;
}>();

const breadcrumbs = [
  { title: 'Cities', href: route('cities.index') },
  { title: props.city.name, href: route('cities.show', props.city.id) },
  { title: 'Edit', href: route('cities.edit', props.city.id) },
];

const form = useForm({
  name: props.city.name,
  cadastral_code: props.city.cadastral_code,
  region_id: props.city.region?.id || '',
  province_id: props.city.province?.id || '',
});

const filteredProvinces = computed(() => {
  if (!form.region_id) return props.provinces;
  return props.provinces.filter(province => province.region && province.region.id === form.region_id);
});

watch(() => form.region_id, () => {
  const found = filteredProvinces.value.find(p => p.id === form.province_id);
  if (!found) {
    form.province_id = '';
  }
});

function submit() {
  form.put(route('cities.update', props.city.id), {
    preserveScroll: true,
    onSuccess: () => {
      //
    }
  });
}

const cancel = () => {
  router.visit(route('cities.index'));
};
</script>

<template>

  <Head title="Edit City" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.edit_city') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">

            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }}</Label>
              <Input id="name" v-model="form.name" type="text" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Cadastral code -->
            <div class="space-y-2">
              <Label for="cadastral_code">{{ trans('ui.cadastral_code') }}</Label>
              <Input id="cadastral_code" v-model="form.cadastral_code" type="text"
                :class="{ 'border-destructive': form.errors.cadastral_code }" />
              <p v-if="form.errors.cadastral_code" class="text-sm text-destructive">
                {{ form.errors.cadastral_code }}
              </p>
            </div>

            <!-- Region -->
            <div class="space-y-2">
              <Label for="region_id">{{ trans('ui.region') }}</Label>

              <Select v-model="form.region_id" :required="true" :disabled="!props.regions?.length">
                <SelectTrigger id="region_id" :class="[
                  'w-full',
                  form.errors.region_id ? 'border-destructive ring-1 ring-destructive' : ''
                ]" :aria-invalid="!!form.errors.region_id">
                  <SelectValue :placeholder="trans('ui.select_region')" />
                </SelectTrigger>

                <SelectContent>
                  <SelectItem v-for="region in props.regions" :key="region.id" :value="region.id">
                    {{ region.name }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <p v-if="form.errors.region_id" class="text-sm text-destructive">
                {{ form.errors.region_id }}
              </p>
            </div>

            <!-- Province (disabled until region selected, filtered by region) -->
            <div class="space-y-2">
              <Label for="province_id">{{ trans('ui.province') }}</Label>

              <Select v-model="form.province_id" :required="true"
                :disabled="!form.region_id || !filteredProvinces.length">
                <SelectTrigger id="province_id" :class="[
                  'w-full',
                  form.errors.province_id ? 'border-destructive ring-1 ring-destructive' : ''
                ]" :aria-invalid="!!form.errors.province_id">
                  <SelectValue :placeholder="trans('ui.select_province')" />
                </SelectTrigger>

                <SelectContent>
                  <SelectItem v-for="province in filteredProvinces" :key="province.id" :value="province.id">
                    {{ province.name }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <p v-if="form.errors.province_id" class="text-sm text-destructive">
                {{ form.errors.province_id }}
              </p>
            </div>
            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-4">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                Cancel
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.updating') : trans('ui.update_city') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
