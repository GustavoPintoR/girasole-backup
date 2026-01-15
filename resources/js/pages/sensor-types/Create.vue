<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { computed, ref, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { cn } from '@/lib/utils';
import { SensorOperation } from '@/types';

type Option = { value: number; label: string };

const breadcrumbs = [
    { title: trans('ui.sensor_types'), href: route('sensor-types.index') },
    { title: trans('ui.create_sensor_type'), href: route('sensor-types.create') },
];

const form = useForm({
    name: '',
    description: '',
    operation_ids: [] as number[],
});

const props = defineProps<{
    operations: SensorOperation[];
}>();

const allOptions = computed<Option[]>(() =>
    props.operations.map(c => ({ value: c.id, label: c.label })),
);

const operationQuery = ref('');
const filteredOptions = computed(() => {
    const q = operationQuery.value.trim().toLowerCase();
    if (!q) return allOptions.value;
    return allOptions.value.filter(o => o.label.toLowerCase().includes(q));
});

const selectedOperations = ref<Option[]>([]);

const isOpen = ref(false);

watch(
    selectedOperations,
    (items) => {
        form.operation_ids = items.map(i => i.value);
    },
    { deep: true },
);

function isSelected(value: number) {
    return selectedOperations.value.some(i => i.value === value);
}

function toggleSelection(option: Option, event: Event) {
    event.stopPropagation();
    if (isSelected(option.value)) {
        selectedOperations.value = selectedOperations.value.filter(i => i.value !== option.value);
    } else {
        selectedOperations.value = [...selectedOperations.value, option];
    }
}

function removeSelection(option: Option) {
    selectedOperations.value = selectedOperations.value.filter(i => i.value !== option.value);
}

const submit = () => {
    form.post(route('sensor-types.store'), {
        preserveScroll: true,
    });
};

const cancel = () => {
    router.visit(route('sensor-types.index'));
};
</script>

<template>
    <Head :title="trans('ui.sensor_type_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.sensor_type_create_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.sensor_type_create_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.sensor_type_create_desc') }}
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">
                                {{ trans('ui.name') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                v-model="form.name"
                                :placeholder="trans('ui.name')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.name }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">{{ trans('ui.description') }}</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :placeholder="trans('ui.description')"
                                class="min-h-24 w-[462px]"
                                :class="{ 'border-red-500': form.errors.description }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <!-- Cultivations (Combobox with multi-select behavior) -->
                        <div class="space-y-2">
                            <Label>{{ trans('ui.sensor_operations') }}</Label>

                            <Combobox v-model:open="isOpen" :modelValue="selectedOperations" multiple>
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button
                                            variant="outline"
                                            class="h-11 w-[462px] justify-between"
                                        >
                                            <span class="truncate text-left">
                                                <template v-if="selectedOperations.length === 0">
                                                    {{ trans('ui.select_sensor_operation') }}
                                                </template>
                                                <template v-else>
                                                    {{ selectedOperations.slice(0, 2).map(i => i.label).join(', ') }}
                                                    <template v-if="selectedOperations.length > 2">
                                                        &nbsp;+{{ selectedOperations.length - 2 }}
                                                    </template>
                                                </template>
                                            </span>
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>

                                <ComboboxList class="w-[462px] relative z-10">
                                    <div class="relative w-full">
                                        <ComboboxInput
                                            class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                            :placeholder="trans('ui.search')"
                                            v-model="operationQuery"
                                        />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                            <Search class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>

                                    <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                        <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                            {{ trans('ui.no_sensor_operations_found') }}
                                        </ComboboxEmpty>

                                        <ComboboxGroup>
                                            <ComboboxItem
                                                v-for="opt in filteredOptions"
                                                :key="opt.value"
                                                :value="opt"
                                                :class="cn('px-4 py-2 cursor-pointer', isSelected(opt.value) ? 'bg-gray-100 dark:bg-input/50' : 'hover:bg-gray-100 dark:hover:bg-input/50')"
                                                @select="toggleSelection(opt, $event)"
                                            >
                                                {{ opt.label }}
                                                <ComboboxItemIndicator>
                                                    <Check :class="cn('ml-auto h-4 w-4', !isSelected(opt.value) && 'opacity-0')" />
                                                </ComboboxItemIndicator>
                                            </ComboboxItem>
                                        </ComboboxGroup>
                                    </div>
                                </ComboboxList>
                            </Combobox>

                            <!-- Click outside to close -->
                            <div v-if="isOpen" class="fixed inset-0 z-0" @click="isOpen = false"></div>

                            <!-- Selected tags -->
                            <div v-if="selectedOperations.length" class="mt-2 flex flex-wrap gap-2">
                                <span
                                    v-for="item in selectedOperations"
                                    :key="item.value"
                                    class="inline-flex items-center rounded-md bg-secondary px-2 py-1 text-xs"
                                >
                                    {{ item.label }}
                                    <button
                                        type="button"
                                        class="ml-2 text-muted-foreground hover:text-foreground"
                                        @click="removeSelection(item)"
                                    >
                                        ×
                                    </button>
                                </span>
                            </div>

                            <InputError :message="form.errors.operation_ids" />
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? trans('ui.creating') : trans('ui.create_sensor_type') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
