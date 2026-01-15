<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent} from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';

const breadcrumbs = [
  { title: 'Provinces', href: route('provinces.index') },
  { title: 'Create', href: route('provinces.create') }
];

const form = useForm({
  name: '',
  code: '',
  region_id: '',
});

function submit() {
  form.post(route('provinces.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

defineProps<{ regions: { id: number; name: string; code: string }[] }>();

const page = usePage();
const flashError = computed(() => page.props.flash?.error);
const flashSuccess = computed(() => page.props.flash?.success);

const cancel = () => {
  router.visit(route('provinces.index'));
};
</script>

<template>

  <Head title="Create Province" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.create') }}
      </h1>
      <template v-if="flashSuccess">
        <Alert variant="success" class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
          >
          <AlertTitle>{{ trans('ui.success') }}</AlertTitle>
          <AlertDescription>{{ flashSuccess }}</AlertDescription>
        </Alert>
      </template>

      <template v-if="flashError">
        <Alert variant="destructive" class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
          >
          <AlertTitle>{{ trans('ui.error') }}</AlertTitle>
          <AlertDescription>{{ flashError }}</AlertDescription>
        </Alert>
      </template>
      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }}</Label>
              <Input id="name" v-model="form.name" type="text" placeholder="Enter province name"
                :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="code">{{ trans('ui.code') }}</Label>
              <Input id="code" v-model="form.code" type="text" placeholder="Enter province code"
                :class="{ 'border-destructive': form.errors.code }" maxlength="2" required />
              <p v-if="form.errors.code" class="text-sm text-destructive">
                {{ form.errors.code }}
              </p>
            </div>

            <!-- Region select field -->
            <div class="space-y-2">
              <Label for="region_id">{{ trans('ui.region') }}</Label>
              <select id="region_id" v-model="form.region_id" :class="[
                'block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary',
                form.errors.region_id ? 'border-destructive' : ''
              ]" required>
                <option value="" disabled>{{ trans('ui.select_region') }}</option>
                <option v-for="region in regions" :key="region.id" :value="region.id">
                  {{ region.name }}
                </option>
              </select>
              <p v-if="form.errors.region_id" class="text-sm text-destructive">
                {{ form.errors.region_id }}
              </p>
            </div>
            <address></address>

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