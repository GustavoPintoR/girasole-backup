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

interface Cultivar {
    id: number;
    name: string;
    description?: string | null;
    cultivation_name?: string | null;
}

interface SelectedCultivar {
    id: number;
    name: string;
    description?: string | null;
    cultivation_name?: string | null;
}

interface Props {
    availableCultivars: Cultivar[];
    selectedCultivars: SelectedCultivar[];
    disabled?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:selectedCultivars': [value: SelectedCultivar[]];
}>();

const localSelectedCultivars = ref<SelectedCultivar[]>([...props.selectedCultivars]);
const dialogOpen = ref(false);

const cultivarForm = ref({
    id: null as number | null,
});

const availableToAdd = computed(() => {
    const selectedIds = new Set(localSelectedCultivars.value.map(c => c.id));
    return props.availableCultivars.filter(c => !selectedIds.has(c.id));
});

const openAddDialog = () => {
    cultivarForm.value = {
        id: null,
    };
    dialogOpen.value = true;
};

const saveCultivar = () => {

    // Adding new
    if (!cultivarForm.value.id) return;

    const cultivarData = props.availableCultivars.find(c => c.id === cultivarForm.value.id);
    if (!cultivarData) return;

    localSelectedCultivars.value.push({
        ...cultivarData,
    });

    emit('update:selectedCultivars', localSelectedCultivars.value);
    dialogOpen.value = false;
};

const removeCultivar = (index: number) => {
    localSelectedCultivars.value.splice(index, 1);
    emit('update:selectedCultivars', localSelectedCultivars.value);
};

watch(() => props.selectedCultivars, (newVal) => {
    localSelectedCultivars.value = [...newVal];
}, { deep: true });
</script>

<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <Label class="text-base">
                {{ trans('ui.cultivars') || 'Cultivars' }}
            </Label>
            <Dialog v-model:open="dialogOpen">
                <DialogTrigger as-child>
                    <Button type="button" variant="outline" size="sm" @click="openAddDialog"
                        :disabled="disabled || availableToAdd.length === 0">
                        <Plus class="h-4 w-4 mr-1" />
                        {{ trans('ui.add_cultivar') || 'Add Cultivar' }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {{ (trans('ui.add_cultivar') || 'Add Cultivar') }}
                        </DialogTitle>
                        <DialogDescription>
                            {{ trans('ui.cultivar_dialog_description') || 'Select a cultivar and optionally specify the area and notes.' }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="cultivar-select">
                                {{ trans('ui.cultivar') || 'Cultivar' }} <span class="text-red-500">*</span>
                            </Label>
                            <Select v-model="cultivarForm.id">
                                <SelectTrigger id="cultivar-select">
                                    <SelectValue :placeholder="trans('ui.select_cultivar') || 'Select a cultivar'" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="cultivar in availableToAdd" :key="cultivar.id"
                                        :value="cultivar.id">
                                        <div>
                                            <p class="font-medium">{{ cultivar.name }}</p>
                                            <p v-if="cultivar.cultivation_name" class="text-xs text-gray-500">
                                                {{ cultivar.cultivation_name }}
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
                        <Button type="button" @click="saveCultivar" :disabled="!cultivarForm.id">
                            {{ trans('ui.add_cultivar') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <!-- Selected cultivars list -->
        <div v-if="localSelectedCultivars.length === 0" class="text-center py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-gray-500">
                {{ trans('ui.no_cultivars_selected') || 'No cultivars selected' }}
            </p>
            <p class="text-sm text-gray-400 mt-1">
                {{ trans('ui.add_cultivars_help') || 'Click "Add Cultivar" to start adding cultivars to this field' }}
            </p>
        </div>

        <div v-else class="space-y-2">
            <Card v-for="(cultivar, index) in localSelectedCultivars" :key="cultivar.id" class="py-2">
                <CardContent class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold">{{ cultivar.name }}</h4>
                                <Badge v-if="cultivar.cultivation_name" variant="outline" class="text-xs">
                                    {{ cultivar.cultivation_name }}
                                </Badge>
                            </div>

                            <p v-if="cultivar.description" class="text-sm text-gray-500 mt-1">
                                {{ cultivar.description }}
                            </p>
                        </div>

                        <div class="flex gap-1 ml-4">
                            <Button type="button" variant="ghost" size="icon" class="h-8 w-8 text-destructive"
                                @click="removeCultivar(index)" :disabled="disabled">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Summary -->
        <div v-if="localSelectedCultivars.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">{{ trans('ui.total_cultivars') || 'Total Cultivars' }}</p>
                    <p class="font-semibold">{{ localSelectedCultivars.length }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
