<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import Textarea from '@/components/ui/textarea/Textarea.vue';

const breadcrumbs = [
  { title: 'Cultivations', href: route('cultivations.index') },
  { title: 'Create', href: route('cultivations.create') },
];

const form = useForm({
  name: '',
  description: '',
});

function submit() {
  form.post(route('cultivations.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

const cancel = () => {
  router.visit(route('cultivations.index'));
};
</script>

<template>

  <Head title="Create Cultivation" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.create_cultivation') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }} <span class="text-red-500">*</span></Label>
              <Input id="name" v-model="form.name" type="text" :placeholder="trans('ui.cultivation_name_placeholder')"
                :class="{ 'border-destructive': form.errors.name }" required />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">
                {{ trans('ui.description') }}
              </Label>
              <Textarea id="description" v-model="form.description" :placeholder="trans('ui.cultivation_description_placeholder')"
                :rows="10" :class="{ 'border-red-500': form.errors.description }" :disabled="form.processing" />
              <p v-if="form.errors.description" class="text-sm text-red-500">
                {{ form.errors.description }}
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
