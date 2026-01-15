<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import CustomFieldsDialog from '@/components/shared/CustomFieldsDialog.vue';

import { CustomField } from '@/types';

const breadcrumbs = [
  { title: 'Cultivars', href: route('cultivars.index') },
  { title: 'Create', href: route('cultivars.create') },
];

const form = useForm({
  name: '',
  description: '',
  cultivation_id: '',
  custom_fields: {} as Record<string, any>,
});

const props = defineProps<{
  cultivations: { id: number; name: string; description: string;}[];
  customFields?: CustomField[]
}>();

function submit() {
  form.post(route('cultivars.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

const cancel = () => {
  router.visit(route('cultivars.index'));
};

</script>

<template>

  <Head title="Create Cultivar" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.create_cultivar') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }} <span class="text-red-500">*</span></Label>
              <Input id="name" v-model="form.name" type="text" :placeholder="trans('ui.cultivars_name_placeholder')"
                :class="{ 'border-destructive': form.errors.name }" required />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">
                {{ trans('ui.description') }}
              </Label>
              <Textarea id="description" v-model="form.description" :placeholder="trans('ui.cultivars_description_placeholder')"
                :rows="10" :class="{ 'border-red-500': form.errors.description }" :disabled="form.processing" />
              <p v-if="form.errors.description" class="text-sm text-red-500">
                {{ form.errors.description }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="cultivation_id">{{ trans('ui.cultivation') }}</Label>

              <Select v-model="form.cultivation_id" :required="true" :disabled="!props.cultivations?.length">
                <SelectTrigger id="cultivation_id" :class="[
                  'w-full',
                  form.errors.cultivation_id ? 'border-destructive ring-1 ring-destructive' : ''
                ]" :aria-invalid="!!form.errors.cultivation_id">
                  <SelectValue :placeholder="trans('ui.select_cultivation')" />
                </SelectTrigger>

                <SelectContent>
                  <SelectItem v-for="cultivation in props.cultivations" :key="cultivation.id" :value="cultivation.id">
                    {{ cultivation.name }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <p v-if="form.errors.cultivation_id" class="text-sm text-destructive">
                {{ form.errors.cultivation_id }}
              </p>
            </div>

            <!-- Custom Fields Section -->
            <CustomFieldsDialog v-if="customFields && customFields.length > 0" :fields="customFields"
              v-model="form.custom_fields" :errors="form.errors" />

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
