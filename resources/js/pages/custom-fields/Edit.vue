<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { InfoIcon, Plus, Trash2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { CustomField } from '@/types';

const props = defineProps<{
  field: CustomField;
  availableModels: Record<string, string>;
  fieldTypes: Record<string, string>;
}>();

const breadcrumbs = [
  {
    title: trans('ui.custom_fields'),
    href: route('custom-fields.index'),
  },
  {
    title: props.field.label,
    href: route('custom-fields.edit', props.field.id),
  },
];

const form = useForm({
  model_type: props.field.model_type,
  key: props.field.key,
  label: props.field.label,
  type: props.field.type,
  unit: props.field.unit || '',
  is_required: props.field.is_required,
  description: props.field.description || '',
  options: props.field.options || [],
  order: props.field.order,
});

const deleteDialogOpen = ref(false);

const showOptions = computed(() => form.type === 'select');

const addOption = () => {
  form.options.push('');
};

const removeOption = (index: number) => {
  form.options.splice(index, 1);
};

const updateOption = (index: number, value: string) => {
  form.options[index] = value;
};

function submit() {
  form.put(route('custom-fields.update', props.field.id), {
    preserveScroll: true,
  });
}

function confirmDelete() {
  deleteDialogOpen.value = true;
}

function deleteField() {
  router.delete(route('custom-fields.destroy', props.field.id), {
    preserveScroll: true,
    onSuccess: () => {
      deleteDialogOpen.value = false;
    }
  });
}

function cancelDelete() {
  deleteDialogOpen.value = false;
}

const cancel = () => {
  router.visit(route('custom-fields.index'));
};
</script>

<template>

  <Head :title="trans('ui.update_custom_field')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ trans('ui.update_custom_field') }}
        </h1>
        <Button variant="destructive" @click="confirmDelete">
          <Trash2 class="h-4 w-4 mr-2" />
          {{ trans('ui.delete') }}
        </Button>
      </div>

      <Alert class="max-w-4xl">
        <InfoIcon class="h-4 w-4" />
        <AlertDescription>
          {{ trans('ui.custom_field_edit_warning') }}
        </AlertDescription>
      </Alert>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Model Type -->
            <div class="space-y-2">
              <Label for="model_type" class="after:content-['*'] after:ml-0.5 after:text-destructive">
                {{ trans('ui.model_type') }}
              </Label>
              <Select v-model="form.model_type" required>
                <SelectTrigger :class="{ 'border-destructive': form.errors.model_type }">
                  <SelectValue :placeholder="trans('ui.select_model_type')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="(label, modelType) in availableModels" :key="modelType" :value="modelType">
                    {{ label }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.model_type" class="text-sm text-destructive">
                {{ form.errors.model_type }}
              </p>
              <p class="text-sm text-gray-500">
                {{ trans('ui.select_model_help') }}
              </p>
            </div>

            <!-- Label -->
            <div class="space-y-2">
              <Label for="label" class="after:content-['*'] after:ml-0.5 after:text-destructive">
                {{ trans('ui.field_label') }}
              </Label>
              <Input id="label" v-model="form.label" type="text"
                :placeholder="trans('ui.field_label_placeholder') || 'e.g., Address, Phone Number'" required
                :class="{ 'border-destructive': form.errors.label }" />
              <p v-if="form.errors.label" class="text-sm text-destructive">
                {{ form.errors.label }}
              </p>
              <p class="text-sm text-gray-500">
                {{ trans('ui.field_label_help') }}
              </p>
            </div>

            <!-- Key -->
            <div class="space-y-2">
              <Label for="key" class="after:content-['*'] after:ml-0.5 after:text-destructive">
                {{ trans('ui.field_key') }}
              </Label>
              <Input id="key" v-model="form.key" type="text" :placeholder="trans('ui.field_key_placeholder')" required
                pattern="^[a-z_]+$" :class="{ 'border-destructive': form.errors.key }" />
              <p v-if="form.errors.key" class="text-sm text-destructive">
                {{ form.errors.key }}
              </p>
              <p class="text-sm text-amber-600 dark:text-amber-400">
                {{ trans('ui.field_key_warning') }}
              </p>
            </div>

            <!-- Type -->
            <div class="space-y-2">
              <Label for="type" class="after:content-['*'] after:ml-0.5 after:text-destructive">
                {{ trans('ui.field_type') }}
              </Label>
              <Select v-model="form.type" required>
                <SelectTrigger :class="{ 'border-destructive': form.errors.type }">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="(label, type) in fieldTypes" :key="type" :value="type">
                    {{ label }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.type" class="text-sm text-destructive">
                {{ form.errors.type }}
              </p>
              <p class="text-sm text-amber-600 dark:text-amber-400">
                {{ trans('ui.field_type_warning') }}
              </p>
            </div>

            <!-- Unit (Optional) -->
            <div class="space-y-2">
              <Label for="unit">
                {{ trans('ui.field_unit') }}
              </Label>
              <Input id="unit" v-model="form.unit" type="text"
                :placeholder="trans('ui.field_unit_placeholder') || 'e.g., km, kg, hours'"
                :class="{ 'border-destructive': form.errors.unit }" />
              <p v-if="form.errors.unit" class="text-sm text-destructive">
                {{ form.errors.unit }}
              </p>
              <p class="text-sm text-gray-500">
                {{ trans('ui.field_unit_help') }}
              </p>
            </div>

            <!-- Options (for select type) -->
            <div v-if="showOptions" class="space-y-2">
              <Label>
                {{ trans('ui.field_options') }}
              </Label>
              <div class="space-y-2">
                <div v-for="(option, index) in form.options" :key="index" class="flex gap-2">
                  <Input :value="option" @input="updateOption(index, ($event.target as HTMLInputElement).value)"
                    type="text" :placeholder="`${trans('ui.option')} ${index + 1}`" />
                  <Button type="button" variant="ghost" size="icon" @click="removeOption(index)">
                    <Trash2 class="h-4 w-4 text-destructive" />
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addOption">
                  <Plus class="h-4 w-4 mr-2" />
                  {{ trans('ui.add_option') }}
                </Button>
              </div>
              <p v-if="form.errors.options" class="text-sm text-destructive">
                {{ form.errors.options }}
              </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">
                {{ trans('ui.field_description') }}
              </Label>
              <Textarea id="description" v-model="form.description"
                :placeholder="trans('ui.field_description_placeholder')"
                :class="{ 'border-destructive': form.errors.description }" rows="3" />
              <p v-if="form.errors.description" class="text-sm text-destructive">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Is Required -->
            <div class="flex items-center space-x-2">
              <input type="checkbox" id="is_required" v-model="form.is_required"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              <Label for="is_required" class="cursor-pointer font-normal">
                {{ trans('ui.field_is_required') }}
              </Label>
            </div>

            <!-- Order -->
            <div class="space-y-2">
              <Label for="order">
                {{ trans('ui.field_order') }}
              </Label>
              <Input id="order" v-model.number="form.order" type="number" min="0"
                :class="{ 'border-destructive': form.errors.order }" />
              <p v-if="form.errors.order" class="text-sm text-destructive">
                {{ form.errors.order }}
              </p>
              <p class="text-sm text-gray-500">
                {{ trans('ui.field_order_help') }}
              </p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.updating') || 'Updating...' : trans('ui.update') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>

  <!-- Delete Confirmation Dialog -->
  <AlertDialog v-model:open="deleteDialogOpen">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ trans('ui.are_you_sure_question') }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ trans('ui.custom_field_delete_warning') }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="cancelDelete">{{ trans('ui.cancel') }}</AlertDialogCancel>
        <AlertDialogAction class="bg-destructive text-white hover:bg-destructive-90" @click="deleteField">
          {{ trans('ui.delete') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
