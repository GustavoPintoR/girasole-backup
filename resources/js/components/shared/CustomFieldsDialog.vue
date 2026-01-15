<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';

import type { CustomField } from '@/types';

const props = defineProps<{
    fields: CustomField[];
    modelValue: Record<string, any>;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, any>];
}>();

const hasErrors = computed(() => {
    if (!props.errors) return false;
    return Object.keys(props.errors).some(key => key.startsWith('custom_fields.'));
});

const updateField = (key: string, value: any) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value,
    });
};
</script>

<template>
    <div v-if="fields.length > 0">
        <Dialog>
            <DialogTrigger as-child>
                <Button variant="outline" :class="{ 'border-destructive': hasErrors }">
                    {{ trans('ui.additional_fields') }}
                    <AlertCircle v-if="hasErrors" class="ml-2 h-4 w-4 text-destructive" />
                </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ trans('ui.additional_fields') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div v-for="field in fields" :key="field.id" class="space-y-2">
                        <Label :for="`custom-${field.key}`"
                            :class="{ 'after:content-[\'*\'] after:ml-0.5 after:text-destructive': field.is_required }">
                            {{ field.label }}
                            <span v-if="field.unit" class="text-sm text-gray-500">({{ field.unit }})</span>
                        </Label>

                        <!-- Text input -->
                        <Input v-if="field.type === 'text'" :id="`custom-${field.key}`"
                            :model-value="modelValue[field.key]" @update:model-value="updateField(field.key, $event)"
                            type="text" :required="field.is_required"
                            :class="{ 'border-destructive': errors?.[`custom_fields.${field.key}`] }" />

                        <!-- Textarea -->
                        <Textarea v-else-if="field.type === 'textarea'" :id="`custom-${field.key}`"
                            :model-value="modelValue[field.key]" @update:model-value="updateField(field.key, $event)"
                            :required="field.is_required"
                            :class="{ 'border-destructive': errors?.[`custom_fields.${field.key}`] }" rows="3" />

                        <!-- Number input -->
                        <Input v-else-if="field.type === 'number'" :id="`custom-${field.key}`"
                            :model-value="modelValue[field.key]" @update:model-value="updateField(field.key, $event)"
                            type="number" :required="field.is_required"
                            :class="{ 'border-destructive': errors?.[`custom_fields.${field.key}`] }" />

                        <!-- Float input -->
                        <Input v-else-if="field.type === 'float'" :id="`custom-${field.key}`"
                            :model-value="modelValue[field.key]" @update:model-value="updateField(field.key, $event)"
                            type="number" step="any" :required="field.is_required"
                            :class="{ 'border-destructive': errors?.[`custom_fields.${field.key}`] }" />

                        <!-- Date input -->
                        <Input v-else-if="field.type === 'date'" :id="`custom-${field.key}`"
                            :model-value="modelValue[field.key]" @update:model-value="updateField(field.key, $event)"
                            type="date" :required="field.is_required"
                            :class="{ 'border-destructive': errors?.[`custom_fields.${field.key}`] }" />

                        <!-- DateTime input -->
                        <Input v-else-if="field.type === 'datetime'" :id="`custom-${field.key}`"
                            :model-value="modelValue[field.key]" @update:model-value="updateField(field.key, $event)"
                            type="datetime-local" :required="field.is_required"
                            :class="{ 'border-destructive': errors?.[`custom_fields.${field.key}`] }" />

                        <!-- Checkbox -->
                        <div v-else-if="field.type === 'checkbox'" class="flex items-center space-x-2">
                            <input :id="`custom-${field.key}`" type="checkbox" :checked="modelValue[field.key]"
                                @change="updateField(field.key, ($event.target as HTMLInputElement).checked)"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        </div>

                        <!-- Select -->
                        <Select v-else-if="field.type === 'select' && field.options"
                            :model-value="modelValue[field.key]" @update:model-value="updateField(field.key, $event)">
                            <SelectTrigger :class="{ 'border-destructive': errors?.[`custom_fields.${field.key}`] }">
                                <SelectValue :placeholder="`Select ${field.label}`" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <p v-if="field.description" class="text-sm text-gray-500">
                            {{ field.description }}
                        </p>
                        <p v-if="errors?.[`custom_fields.${field.key}`]" class="text-sm text-destructive">
                            {{ errors[`custom_fields.${field.key}`] }}
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button>Done</Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
