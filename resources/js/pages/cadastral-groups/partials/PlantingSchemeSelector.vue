<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Plus, Trash2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';

interface PlantingScheme {
    id: number;
    name: string;
    description?: string | null;
    distance?: string | null;
}

interface Props {
    availableSchemes: PlantingScheme[];
    selectedSchemes: PlantingScheme[];
    disabled?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:selectedSchemes': [value: PlantingScheme[]];
}>();

const localSelectedSchemes = ref<PlantingScheme[]>([...props.selectedSchemes]);
const dialogOpen = ref(false);

const schemeForm = ref({
    id: null as number | null,
});

const availableToAdd = computed(() => {
    const selectedIds = new Set(localSelectedSchemes.value.map(c => c.id));
    return props.availableSchemes.filter(c => !selectedIds.has(c.id));
});

const openAddDialog = () => {
    schemeForm.value = {
        id: null,
    };
    dialogOpen.value = true;
};

const saveScheme = () => {
    // Adding new
    if (!schemeForm.value.id) return;

    const schemeData = props.availableSchemes.find(c => c.id === schemeForm.value.id);
    if (!schemeData) return;

    localSelectedSchemes.value.push({
        ...schemeData,
    });

    emit('update:selectedSchemes', localSelectedSchemes.value);
    dialogOpen.value = false;
};

const removeScheme = (index: number) => {
    localSelectedSchemes.value.splice(index, 1);
    emit('update:selectedSchemes', localSelectedSchemes.value);
};

watch(() => props.selectedSchemes, (newVal) => {
    localSelectedSchemes.value = [...newVal];
}, { deep: true });
</script>

<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <Label class="text-base">
                {{ trans('ui.planting_schemes') }}
            </Label>
            <Dialog v-model:open="dialogOpen">
                <DialogTrigger as-child>
                    <Button type="button" variant="outline" size="sm" @click="openAddDialog"
                        :disabled="disabled || availableToAdd.length === 0">
                        <Plus class="h-4 w-4 mr-1" />
                        {{ trans('ui.add_planting_scheme') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {{ (trans('ui.add_planting_scheme')) }}
                        </DialogTitle>
                        <DialogDescription>
                            {{ trans('ui.planting_scheme_dialog_description') }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="scheme-select">
                                {{ trans('ui.planting_scheme') }} <span class="text-red-500">*</span>
                            </Label>
                            <Select v-model="schemeForm.id">
                                <SelectTrigger id="scheme-select">
                                    <SelectValue :placeholder="trans('ui.select_planting_scheme')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="scheme in availableToAdd" :key="scheme.id" :value="scheme.id">
                                        <div>
                                            <p class="font-medium">{{ scheme.name }}</p>
                                            <p v-if="scheme.distance" class="text-xs text-gray-500">
                                                {{ scheme.distance }}
                                            </p>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="dialogOpen = false">
                            {{ trans('ui.cancel') }}
                        </Button>
                        <Button type="button" @click="saveScheme" :disabled="!schemeForm.id">
                            {{ trans('ui.add_planting_scheme') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <!-- Selected schemes list -->
        <div v-if="localSelectedSchemes.length === 0" class="text-center py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-gray-500">
                {{ trans('ui.no_planting_schemes_selected') }}
            </p>
            <p class="text-sm text-gray-400 mt-1">
                {{ trans('ui.add_planting_schemes_help') }}
            </p>
        </div>

        <div v-else class="space-y-2">
            <Card v-for="(scheme, index) in localSelectedSchemes" :key="scheme.id" class="py-2">
                <CardContent class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold">{{ scheme.name }}</h4>
                                <Badge v-if="scheme.distance" variant="outline" class="text-xs">
                                    {{ scheme.distance }}
                                </Badge>
                            </div>

                            <p v-if="scheme.description" class="text-sm text-gray-500 mt-1">
                                {{ scheme.description }}
                            </p>
                        </div>

                        <div class="flex gap-1 ml-4">
                            <Button type="button" variant="ghost" size="icon" class="h-8 w-8 text-destructive"
                                @click="removeScheme(index)" :disabled="disabled">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Summary -->
        <div v-if="localSelectedSchemes.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">{{ trans('ui.total_planting_schemes') }}</p>
                    <p class="font-semibold">{{ localSelectedSchemes.length }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
