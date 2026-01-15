<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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
import Textarea from '@/components/ui/textarea/Textarea.vue';
import CustomFieldsInput from '@/components/shared/CustomFieldsInput.vue';
import { CustomField } from '@/types';

interface Cultivation {
  id: number;
  name: string;
  description: string;
}

interface Cultivar {
  id: number;
  name: string;
  description: string;
  cultivation: Cultivation;
}

const props = defineProps<{
  cultivar: Cultivar;
  cultivations: Cultivation[];
  customFields: CustomField[];
  customFieldValues?: Record<string, any>;
}>();

const breadcrumbs = [
  { title: 'Cultivars', href: route('cultivars.index') },
  { title: props.cultivar.name, href: route('cultivars.show', props.cultivar.id) },
  { title: 'Edit', href: route('cultivars.edit', props.cultivar.id) },
];

const form = useForm({
  name: props.cultivar.name,
  description: props.cultivar.description,
  cultivation_id: props.cultivar.cultivation?.id,
  custom_fields: props.customFieldValues || {} as Record<string, any>,
});


function submit() {
  form.put(route('cultivars.update', props.cultivar.id), {
    preserveScroll: true,
    onSuccess: () => {
      //
    }
  });
}

const cancel = () => {
  router.visit(route('cultivars.index'));
};
</script>

<template>

  <Head title="Edit Cultivar" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.edit_cultivar') }}
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
              <Textarea id="description" v-model="form.description" :placeholder="trans('ui.cultivar_description_placeholder')"
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
            <CustomFieldsInput v-if="customFields && customFields.length > 0" :fields="customFields"
              v-model="form.custom_fields" :errors="form.errors" />

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-4">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.updating') : trans('ui.update_cultivar') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
