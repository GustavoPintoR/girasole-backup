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

interface PlantDisease {
    id: number;
    name: string;
    description?: string | null;
}

interface SelectedPlantDisease {
    id: number;
    name: string;
    description?: string | null;
}

interface Props {
    availablePlantDiseases: PlantDisease[];
    selectedPlantDiseases: SelectedPlantDisease[];
    disabled?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:selectedPlantDiseases': [value: SelectedPlantDisease[]];
}>();

const localSelectedPlantDiseases = ref<SelectedPlantDisease[]>([...props.selectedPlantDiseases]);
const dialogOpen = ref(false);

const diseaseForm = ref({
    id: null as number | null,
});

const availableToAdd = computed(() => {
    const selectedIds = new Set(localSelectedPlantDiseases.value.map(c => c.id));
    return props.availablePlantDiseases.filter(c => !selectedIds.has(c.id));
});

const openAddDialog = () => {
    diseaseForm.value = {
        id: null,
    };
    dialogOpen.value = true;
};

const savePlantDisease = () => {
    // Adding new
    if (!diseaseForm.value.id) return;

    const diseaseData = props.availablePlantDiseases.find(c => c.id === diseaseForm.value.id);
    if (!diseaseData) return;

    localSelectedPlantDiseases.value.push({
        ...diseaseData,
    });

    emit('update:selectedPlantDiseases', localSelectedPlantDiseases.value);
    dialogOpen.value = false;
};

const removePlantDisease = (index: number) => {
    localSelectedPlantDiseases.value.splice(index, 1);
    emit('update:selectedPlantDiseases', localSelectedPlantDiseases.value);
};

watch(() => props.selectedPlantDiseases, (newVal) => {
    localSelectedPlantDiseases.value = [...newVal];
}, { deep: true });
</script>

<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <Label class="text-base">
                {{ trans('ui.plant_diseases') }}
            </Label>
            <Dialog v-model:open="dialogOpen">
                <DialogTrigger as-child>
                    <Button variant="outline" size="sm" :disabled="disabled" @click="openAddDialog">
                        <Plus class="w-4 h-4 mr-2" />
                        {{ trans('ui.add_plant_disease') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ trans('ui.add_plant_disease') }}</DialogTitle>
                        <DialogDescription>
                            {{ trans('ui.select_plant_disease_to_add') }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="plant_disease-select">{{ trans('ui.plant_disease') }} <span class="text-red-500">*</span></Label>
                            <Select v-model="diseaseForm.id">
                                <SelectTrigger id="plant_disease-select">
                                    <SelectValue :placeholder="trans('ui.select_plant_disease') || 'Select plant disease'" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="disease in availableToAdd" :key="disease.id" :value="disease.id">
                                        <div>
                                            <p class="font-medium">{{ disease.name }}</p>
                                            <p v-if="disease.description" class="text-xs text-gray-500">{{ disease.description }}</p>
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
                        <Button @click="savePlantDisease" :disabled="!diseaseForm.id">
                            {{ trans('ui.add_plant_disease') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <div v-if="localSelectedPlantDiseases.length === 0" class="text-center py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-gray-500">{{ trans('ui.no_plant_diseases_selected') }}</p>
            <p class="text-sm text-gray-400 mt-1">{{ trans('ui.add_plant_diseases_help') || 'Click "Add Plant Disease" to start adding diseases to this field' }}</p>
        </div>

        <div v-else class="space-y-2">
            <Card v-for="(disease, index) in localSelectedPlantDiseases" :key="disease.id" class="py-2">
                <CardContent class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold">{{ disease.name }}</h4>
                            </div>
                            <p v-if="disease.description" class="text-sm text-gray-500 mt-1">{{ disease.description }}</p>
                        </div>

                        <div class="flex gap-1 ml-4">
                            <Button type="button" variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="removePlantDisease(index)" :disabled="disabled">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
        <!-- Summary -->
        <div v-if="localSelectedPlantDiseases.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">{{ trans('ui.total_plant_diseases') || 'Total Plant Diseases' }}</p>
                    <p class="font-semibold">{{ localSelectedPlantDiseases.length }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
