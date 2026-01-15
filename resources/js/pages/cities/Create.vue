<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const props = defineProps<{
  provinces: { id: number; name: string; code: string; region: { id: number; name: string; code: string } }[];
  regions: { id: number; name: string; code: string }[];
}>();

const breadcrumbs = [
  { title: 'Cities', href: route('cities.index') },
  { title: 'Create', href: route('cities.create') },
];

const form = useForm({
  name: '',
  cadastral_code: '',
  province_id: '',
  region_id: '',
});

const filteredProvinces = computed(() => {
  if (!form.region_id) return props.provinces; 
  return props.provinces.filter(province => province.region && province.region.id === form.region_id);
});

// When region changes, clear province selection if invalid
watch(() => form.region_id, () => {
  const found = filteredProvinces.value.find(p => p.id === form.province_id);
  if (!found) {
    form.province_id = '';
  }
});

function submit() {
  form.post(route('cities.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

const cancel = () => {
  router.visit(route('cities.index'));
};
</script>

<template>
  <Head title="Create City" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.create_city') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }}</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="Enter city name"
                :class="{ 'border-destructive': form.errors.name }"
                required
              />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <!-- Cadastral Code -->
            <div class="space-y-2">
              <Label for="cadastral_code">{{ trans('ui.cadastral_code') }}</Label>
              <Input
                id="cadastral_code"
                v-model="form.cadastral_code"
                type="text"
                placeholder="Enter cadastral code"
                maxlength="4"
                :class="{ 'border-destructive': form.errors.cadastral_code }"
                required
              />
              <p v-if="form.errors.cadastral_code" class="text-sm text-destructive">{{ form.errors.cadastral_code }}</p>
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

            <div class="flex justify-end space-x-3">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ trans('ui.create') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>