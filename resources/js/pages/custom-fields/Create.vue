<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { InfoIcon, Plus, Trash2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';

const props = defineProps<{
    availableModels: Record<string, string>;
    fieldTypes: Record<string, string>;
    preselectedModel?: string;
}>();

const breadcrumbs = [
    {
        title: trans('ui.custom_fields'),
        href: route('custom-fields.index'),
    },
    {
        title: trans('ui.create_custom_field'),
        href: route('custom-fields.create'),
    },
];

const form = useForm({
    model_type: props.preselectedModel || '',
    key: '',
    label: '',
    type: 'text',
    unit: '',
    is_required: false,
    description: '',
    options: [] as string[],
    order: 0,
});

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

// Auto-generate key from label
watch(() => form.label, (newLabel) => {
    if (newLabel && !form.key) {
        form.key = newLabel
            .toLowerCase()
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_');
    }
});

function submit() {
    form.post(route('custom-fields.store'), {
        preserveScroll: true,
    });
}

const cancel = () => {
    router.visit(route('custom-fields.index'));
};
</script>

<template>

    <Head :title="trans('ui.create_custom_field')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ trans('ui.create_custom_field') }}
            </h1>

            <Alert class="max-w-4xl">
                <InfoIcon class="h-4 w-4" />
                <AlertDescription>
                    {{ trans('ui.custom_field_help_text') }}
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
                                    <SelectItem v-for="(label, modelType) in availableModels" :key="modelType"
                                        :value="modelType">
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
                                :placeholder="trans('ui.field_label_placeholder') || 'e.g., Address, Phone Number'"
                                required :class="{ 'border-destructive': form.errors.label }" />
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
                            <Input id="key" v-model="form.key" type="text"
                                :placeholder="trans('ui.field_key_placeholder') || 'e.g., address, phone_number'"
                                required pattern="^[a-z_]+$" :class="{ 'border-destructive': form.errors.key }" />
                            <p v-if="form.errors.key" class="text-sm text-destructive">
                                {{ form.errors.key }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ trans('ui.field_key_help') }}
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
                                    <Input :value="option"
                                        @input="updateOption(index, ($event.target as HTMLInputElement).value)"
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
                                :placeholder="trans('ui.field_description_placeholder') || 'Optional help text shown below the field'"
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
                                {{ form.processing ? trans('ui.creating') || 'Creating...' : trans('ui.create') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
