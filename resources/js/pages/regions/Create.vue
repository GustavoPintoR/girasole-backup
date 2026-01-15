<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';

const breadcrumbs = [
  { title: 'Regions', href: route('regions.index') },
  { title: 'Create', href: route('regions.create') }
];

const form = useForm({
  name: '',
  code: '',
});

function submit() {
  form.post(route('regions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}
const page = usePage();
const flashError = computed(() => page.props.flash?.error);
const flashSuccess = computed(() => page.props.flash?.success);

const cancel = () => {
  router.visit(route('regions.index'));
};
</script>

<template>

  <Head title="Create Region" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.create_region') }}
      </h1>
      <template v-if="flashSuccess">
        <Alert variant="success"
          class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
        >
          <AlertTitle>{{trans('ui.success')}}</AlertTitle>
          <AlertDescription>{{ flashSuccess }}</AlertDescription>
        </Alert>
      </template>

      <template v-if="flashError">
        <Alert variant="destructive"
          class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
        >
          <AlertTitle>{{trans('ui.error')}}</AlertTitle>
          <AlertDescription>{{ flashError }}</AlertDescription>
        </Alert>
      </template>
      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }}</Label>
              <Input id="name" v-model="form.name" type="text" placeholder="Enter region name"
                :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="code">{{ trans('ui.code') }}</Label>
              <Input id="code" v-model="form.code" type="text" placeholder="Enter region code"
                :class="{ 'border-destructive': form.errors.code }"  maxlength="2" required />
              <p v-if="form.errors.code" class="text-sm text-destructive">
                {{ form.errors.code }}
              </p>
            </div>

            <div class="flex justify-end space-x-3">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ trans('ui.create_region') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
