<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { trans } from 'laravel-vue-i18n';

interface Region {
  id: number;
  name: string;
  code: string
};

const props = defineProps<{
  region: Region;
}>();

const breadcrumbs = [
  { title: 'Regions', href: route('regions.index') },
  { title: props.region.name, href: route('regions.show', props.region.id) },
  { title: 'Edit', href: route('regions.edit', props.region.id) },
];

// Inertia form binding
const form = useForm({
  name: props.region.name,
  code: props.region.code,
});


function submit() {
  form.put(route('regions.update', props.region.id), {
    preserveScroll: true,
    onSuccess: () => {
      //
    }
  });
}

const cancel = () => {
  router.visit(route('regions.index'));
};
</script>

<template>
  <Head title="Edit Region" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.edit_region') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }}</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                :class="{ 'border-destructive': form.errors.name }"
              />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="code">{{ trans('ui.code') }}</Label>
              <Input
                id="code"
                v-model="form.code"
                type="text"
                :class="{ 'border-destructive': form.errors.code }"
              />
              <p v-if="form.errors.code" class="text-sm text-destructive">
                {{ form.errors.code }}
              </p>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4">
              <Button
                type="button"
                variant="outline"
                @click="cancel"
                :disabled="form.processing"
              >
                Cancel
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Updating...' : 'Update Region' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

