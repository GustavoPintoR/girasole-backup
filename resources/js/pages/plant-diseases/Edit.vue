<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
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
import { Badge } from '@/components/ui/badge';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { computed, ref, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { cn } from '@/lib/utils';
import { Cultivation, PlantDisease } from '@/types';

type Option = { value: number; label: string };

const props = defineProps<{
    plantDisease: PlantDisease;
    cultivations: Cultivation[];
    selectedCultivationIds: number[];
}>();

const breadcrumbs = [
    { title: trans('ui.plant_diseases'), href: route('plant-diseases.index') },
    { title: trans('ui.edit'), href: route('plant-diseases.edit', props.plantDisease.id) },
];

const form = useForm({
    name: props.plantDisease.name,
    description: props.plantDisease.description,
    cultivation_ids: props.selectedCultivationIds || [],
});

// Build options list
const allOptions = computed<Option[]>(() =>
    props.cultivations.map(c => ({ value: c.id, label: c.name })),
);

// Searchable options
const cultivationQuery = ref('');
const filteredOptions = computed(() => {
    const q = cultivationQuery.value.trim().toLowerCase();
    if (!q) return allOptions.value;
    return allOptions.value.filter(o => o.label.toLowerCase().includes(q));
});

// Selected options (multi-select)
const selectedCultivations = ref<Option[]>(
    props.selectedCultivationIds
        .map(id => allOptions.value.find(opt => opt.value === id))
        .filter((opt): opt is Option => !!opt),
);

// Control combobox open state
const isOpen = ref(false);

// Sync form.cultivation_ids with selectedCultivations
watch(
    selectedCultivations,
    (items) => {
        form.cultivation_ids = items.map(i => i.value);
    },
    { deep: true },
);

function isSelected(value: number) {
    return selectedCultivations.value.some(i => i.value === value);
}

function toggleSelection(option: Option, event: Event) {
    event.stopPropagation(); // Prevent combobox from closing
    if (isSelected(option.value)) {
        selectedCultivations.value = selectedCultivations.value.filter(i => i.value !== option.value);
    } else {
        selectedCultivations.value = [...selectedCultivations.value, option];
    }
}

function removeSelection(option: Option) {
    selectedCultivations.value = selectedCultivations.value.filter(i => i.value !== option.value);
}

function submit() {
    form.put(route('plant-diseases.update', props.plantDisease.id), {
        preserveScroll: true,
        onSuccess: () => {
            //
        },
    });
}

const cancel = () => {
    router.visit(route('plant-diseases.index'));
};
</script>

<template>
    <Head :title="trans('ui.plant_disease_edit_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.plant_disease_edit_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.plant_disease_edit_header_details') }}</CardTitle>
                    <CardDescription>{{ trans('ui.plant_disease_edit_edit_desc') }}</CardDescription>
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
                            <Label>{{ trans('ui.cultivations') }}</Label>
                            <Combobox v-model:open="isOpen" :modelValue="selectedCultivations" multiple>
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button
                                            variant="outline"
                                            class="h-11 w-[462px] justify-between"
                                        >
                                            <span class="truncate text-left">
                                                <template v-if="selectedCultivations.length === 0">
                                                    {{ trans('ui.select_cultivations') }}
                                                </template>
                                                <template v-else>
                                                    {{ selectedCultivations.slice(0, 2).map(i => i.label).join(', ') }}
                                                    <template v-if="selectedCultivations.length > 2">
                                                        &nbsp;+{{ selectedCultivations.length - 2 }}
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
                                            v-model="cultivationQuery"
                                        />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                            <Search class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>

                                    <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                        <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                            {{ trans('ui.no_cultivations_found') }}
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

                            <!-- Selected cultivations as badges -->
                            <div v-if="selectedCultivations.length" class="mt-2 flex flex-wrap gap-2">
                                <Badge
                                    v-for="item in selectedCultivations"
                                    :key="item.value"
                                    class="text-xs"
                                >
                                    {{ item.label }}
                                    <button
                                        type="button"
                                        class="ml-2 text-muted-foreground hover:text-foreground"
                                        @click="removeSelection(item)"
                                    >
                                        ×
                                    </button>
                                </Badge>
                            </div>

                            <InputError :message="form.errors.cultivation_ids" />
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                        {{ trans('ui.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing" @click="submit">
                        {{ form.processing ? trans('ui.updating') : trans('ui.edit_plant_disease') }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
