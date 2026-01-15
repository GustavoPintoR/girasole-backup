<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { trans } from 'laravel-vue-i18n';
import Textarea from '@/components/ui/textarea/Textarea.vue';


interface Cultivation {
  id: number;
  name: string;
  description: string;
}

const props = defineProps<{
  cultivation: Cultivation;
}>();

const breadcrumbs = [
  { title: 'Cultivations', href: route('cultivations.index') },
  { title: props.cultivation.name, href: route('cultivations.show', props.cultivation.id) },
  { title: 'Edit', href: route('cultivations.edit', props.cultivation.id) },
];

const form = useForm({
  name: props.cultivation.name,
  description: props.cultivation.description,
});


function submit() {
  form.put(route('cultivations.update', props.cultivation.id), {
    preserveScroll: true,
    onSuccess: () => {
      //
    }
  });
}

const cancel = () => {
  router.visit(route('cultivations.index'));
};
</script>

<template>

  <Head title="Edit Cultivation" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.edit_cultivation') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">

            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }} <span class="text-red-500">*</span></Label>
              <Input id="name" v-model="form.name" type="text" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">
                {{ trans('ui.full_description') }} 
              </Label>
              <Textarea id="description" v-model="form.description" placeholder="Enter the description text..."
                :rows="10" :class="{ 'border-red-500': form.errors.description }" :disabled="form.processing" />
              <p v-if="form.errors.description" class="text-sm text-red-500">
                {{ form.errors.description }}
              </p>
            </div>
            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-4">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                Cancel
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.updating') : trans('ui.update_cultivation') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
