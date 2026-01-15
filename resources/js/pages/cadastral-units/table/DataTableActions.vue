<script setup lang="ts">
import { MapPin, Edit, Trash } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { trans } from 'laravel-vue-i18n'
import { toast } from 'vue-sonner'

import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();

const props = defineProps<{
    cadastral_unit: {
        id: number;
        version: string;
        is_active: boolean;
        sheet: string;
        parcel: string;
        city: {
            id: number;
            name: string;
            cadastral_code: string;
            region: {
                id: number;
                name: string;
                code: string;
            };
            province: {
                id: number;
                name: string;
                code: string;
            };
        };
    }
}>()

const deleteDialogOpen = ref(false);
const selectedId = ref<number | null>(null);

function confirmDelete(id: number) {
    selectedId.value = id;
    deleteDialogOpen.value = true;
}

function deleteItem() {
    if (selectedId.value) {
        router.delete(route('cadastral-units.destroy', selectedId.value), {
            preserveScroll: true,
            onSuccess: () => {
                deleteDialogOpen.value = false;
                selectedId.value = null;
            },
            onError: (errors) => {
                deleteDialogOpen.value = false;
                selectedId.value = null;
                toast.error(errors.general);
            }
        });
    }
}

function cancelDelete() {
    deleteDialogOpen.value = false;
    selectedId.value = null;
}

function centerOnLocation() {
    const event = new CustomEvent('center-map-location', {
        detail: props.cadastral_unit,
        bubbles: true,
        composed: true
    });
    window.dispatchEvent(event);
}
</script>

<template>
    <div class="flex items-center justify-end gap-1">
        <TooltipProvider>
            <!-- Center on Map Action -->
            <Tooltip v-if="cadastral_unit.city">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="centerOnLocation">
                        <MapPin class="h-4 w-4 text-blue-600" />
                        <span class="sr-only">{{ trans('ui.center_map_location') }}</span>
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ trans('ui.center_map_location') }}</p>
                </TooltipContent>
            </Tooltip>

            <!-- Edit Action -->
            <Tooltip v-if="can.update_cadastral_unit">
                <TooltipTrigger as-child>
                    <Link :href="route('cadastral-units.edit', cadastral_unit.id)">
                    <Button variant="ghost" size="icon" class="h-8 w-8">
                        <Edit class="h-4 w-4" />
                        <span class="sr-only">{{ trans('ui.edit') }}</span>
                    </Button>
                    </Link>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ trans('ui.edit') }}</p>
                </TooltipContent>
            </Tooltip>

            <!-- Delete Action -->
            <Tooltip v-if="can.delete_cadastral_unit">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="confirmDelete(cadastral_unit.id)">
                        <Trash class="h-4 w-4 text-red-600" />
                        <span class="sr-only">{{ trans('ui.delete') }}</span>
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ trans('ui.delete') }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('ui.alert_dialog_title') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ trans('ui.alert_dialog_description') }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="cancelDelete">{{ trans('ui.cancel') }}</AlertDialogCancel>
                <AlertDialogAction class="bg-destructive text-white hover:bg-destructive-90" @click="deleteItem">
                    {{ trans('ui.delete') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
