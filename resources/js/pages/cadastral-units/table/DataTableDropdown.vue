<script setup lang="ts">
import { MoreHorizontal, Trash, Edit, MapPin } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
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
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { trans } from 'laravel-vue-i18n';

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
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0 cursor-pointer">
                <span class="sr-only">{{ trans('open_menu') }}</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end">
            <DropdownMenuLabel>{{ trans('ui.actions') }}</DropdownMenuLabel>

            <DropdownMenuItem class="p-0" v-if="cadastral_unit.city">
                <Button variant="ghost" size="sm" :title="trans('ui.center')" class="w-full justify-start"
                    @click="centerOnLocation">
                    <MapPin class="h-4 w-4 text-blue-600" />
                    {{ trans('ui.center_map_location') }}
                </Button>
            </DropdownMenuItem>

            <DropdownMenuItem class="p-0">
                <Link :href="route('cadastral-units.edit', cadastral_unit.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.edit')" class="w-full justify-start">
                    <Edit class="h-4 w-4" /> {{ trans('ui.edit') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <DropdownMenuItem class="p-0">
                <Button variant="ghost" size="sm" :title="trans('ui.delete')" @click="confirmDelete(cadastral_unit.id)"
                    class="w-full justify-start">
                    <Trash class="h-4 w-4 text-red-600" /> {{ trans('ui.delete') }}
                </Button>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

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
