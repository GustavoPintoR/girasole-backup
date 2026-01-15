<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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

interface Irrigation {
    id: number;
    type: string;
    description?: string | null;
}

interface SelectedIrrigation {
    id: number;
    type: string;
    description?: string | null;
}

interface Props {
    availableIrrigations: Irrigation[];
    selectedIrrigations: SelectedIrrigation[];
    disabled?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:selectedIrrigations': [value: SelectedIrrigation[]];
}>();

const localSelectedIrrigations = ref<SelectedIrrigation[]>([...props.selectedIrrigations]);
const dialogOpen = ref(false);

const irrigationForm = ref({
    id: null as number | null,
});

const availableToAdd = computed(() => {
    const selectedIds = new Set(localSelectedIrrigations.value.map(c => c.id));
    return props.availableIrrigations.filter(c => !selectedIds.has(c.id));
});

const openAddDialog = () => {
    irrigationForm.value = {
        id: null,
    };
    dialogOpen.value = true;
};

const saveIrrigation = () => {
    // Adding new
    if (!irrigationForm.value.id) return;

    const irrigationData = props.availableIrrigations.find(c => c.id === irrigationForm.value.id);
    if (!irrigationData) return;

    localSelectedIrrigations.value.push({
        ...irrigationData,
    });

    emit('update:selectedIrrigations', localSelectedIrrigations.value);
    dialogOpen.value = false;
};

const removeIrrigation = (index: number) => {
    localSelectedIrrigations.value.splice(index, 1);
    emit('update:selectedIrrigations', localSelectedIrrigations.value);
};

watch(() => props.selectedIrrigations, (newVal) => {
    localSelectedIrrigations.value = [...newVal];
}, { deep: true });
</script>

<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <Label class="text-base">
                {{ trans('ui.irrigations') }}
            </Label>
            <Dialog v-model:open="dialogOpen">
                <DialogTrigger as-child>
                    <Button variant="outline" size="sm" :disabled="disabled" @click="openAddDialog">
                        <Plus class="w-4 h-4 mr-2" />
                        {{ trans('ui.add_irrigation') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ trans('ui.add_irrigation') }}</DialogTitle>
                        <DialogDescription>
                            {{ trans('ui.select_irrigation_to_add') }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="irrigation-select">{{ trans('ui.irrigation') }} <span class="text-red-500">*</span></Label>
                            <Select v-model="irrigationForm.id">
                                <SelectTrigger id="irrigation-select">
                                    <SelectValue :placeholder="trans('ui.select_irrigation') || 'Select irrigation'" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="irrigation in availableToAdd" :key="irrigation.id" :value="irrigation.id">
                                        <div>
                                            <p class="font-medium">{{ irrigation.type }}</p>
                                            <p v-if="irrigation.description" class="text-xs text-gray-500">{{ irrigation.description }}</p>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" @click="dialogOpen = false">
                            {{ trans('ui.cancel') }}
                        </Button>
                        <Button @click="saveIrrigation" :disabled="!irrigationForm.id">
                            {{ trans('ui.add_irrigation') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <div v-if="localSelectedIrrigations.length === 0" class="text-center py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-gray-500">{{ trans('ui.no_irrigations_selected') }}</p>
            <p class="text-sm text-gray-400 mt-1">{{ trans('ui.add_irrigations_help') || 'Click "Add Irrigation" to start adding irrigations to this field' }}</p>
        </div>

        <div v-else class="space-y-2">
            <Card v-for="(irrigation, index) in localSelectedIrrigations" :key="irrigation.id" class="py-2">
                <CardContent class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold">{{ irrigation.type }}</h4>
                            </div>

                            <p v-if="irrigation.description" class="text-sm text-gray-500 mt-1">{{ irrigation.description }}</p>
                        </div>

                        <div class="flex gap-1 ml-4">
                            <Button type="button" variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="removeIrrigation(index)" :disabled="disabled">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
        <!-- Summary -->
        <div v-if="localSelectedIrrigations.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">{{ trans('ui.total_irrigations') }}</p>
                    <p class="font-semibold">{{ localSelectedIrrigations.length }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
