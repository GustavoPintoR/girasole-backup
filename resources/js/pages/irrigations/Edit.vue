<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { trans } from 'laravel-vue-i18n';
import { Irrigation } from '@/types';

const props = defineProps<{
  irrigation: Irrigation;
}>();

const breadcrumbs = [
  { title: trans('ui.irrigations'), href: route('irrigations.index') },
  { title: trans('ui.edit'), href: route('irrigations.edit', props.irrigation.id) },
];

const form = useForm({
  type: props.irrigation.type,
  description: props.irrigation.description,
});

function submit() {
  form.put(route('irrigations.update', props.irrigation.id), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

const cancel = () => {
  router.visit(route('irrigations.index'));
};
</script>

<template>

  <Head :title="trans('ui.irrigation_edit_header')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ trans('ui.irrigation_edit_header') }}
        </h1>
      </div>

      <Card class="max-w-4xl">
        <CardHeader>
          <CardTitle>{{ trans('ui.irrigation_edit_header_details') }}</CardTitle>
          <CardDescription>{{ trans('ui.irrigation_edit_edit_desc') }}</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Type -->
            <div class="space-y-2">
              <Label for="type">
                {{ trans('ui.type') }} <span class="text-red-500">*</span>
              </Label>
              <Input id="type" type="text" required v-model="form.type" :placeholder="trans('ui.type')"
                class="h-11 w-[462px]" :class="{ 'border-red-500': form.errors.type }" :disabled="form.processing" />
              <InputError :message="form.errors.type" />
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">{{ trans('ui.description') }}</Label>
              <Textarea id="description" v-model="form.description" :placeholder="trans('ui.description')"
                class="min-h-24 w-[462px]" :class="{ 'border-red-500': form.errors.description }"
                :disabled="form.processing" />
              <InputError :message="form.errors.description" />
            </div>
          </form>
        </CardContent>
        <CardFooter class="flex justify-end space-x-2">
          <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
            {{ trans('ui.cancel') }}
          </Button>
          <Button type="submit" :disabled="form.processing" @click="submit">
            {{ form.processing ? trans('ui.updating') : trans('ui.edit_irrigation') }}
          </Button>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>
