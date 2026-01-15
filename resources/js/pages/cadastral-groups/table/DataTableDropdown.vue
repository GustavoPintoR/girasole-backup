<script setup lang="ts">
import { MoreHorizontal, Trash, Edit, Download, Eye, MapPin } from 'lucide-vue-next'
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

import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();

interface CadastralGroup {
    id: number;
    name: string;
    description?: string;
    total_area: number;
    units_count: number;
    creation_method: 'units' | 'manual' | 'import';
    color: string;
    created_at: string;
    updated_at: string;
    boundary_geometry_json?: string;
    centroid_json?: string;
}

const props = defineProps<{
    group: CadastralGroup
}>()

const deleteDialogOpen = ref(false);
const selectedId = ref<number | null>(null);

function confirmDelete(id: number) {
    selectedId.value = id;
    deleteDialogOpen.value = true;
}

function deleteItem() {
    if (selectedId.value) {
        router.delete(route('cadastral-groups.destroy', selectedId.value), {
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

function downloadGeojson(groupId: number) {
    window.open(route('cadastral-groups.export-geojson', groupId), '_blank');
}

function centerOnLocation() {
    const event = new CustomEvent('center-map-on-group', {
        detail: props.group,
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

            <DropdownMenuItem class="p-0">
                <Button variant="ghost" size="sm" :title="trans('ui.center')" class="w-full justify-start"
                    @click="centerOnLocation">
                    <MapPin class="h-4 w-4 text-blue-600" />
                    {{ trans('ui.center_map_location') }}
                </Button>
            </DropdownMenuItem>

            <DropdownMenuItem v-if="can.read_cadastral_group" class="p-0">
                <Button variant="ghost" size="sm" :title="trans('ui.download')" class="w-full justify-start"
                    @click="downloadGeojson(props.group.id)">
                    <Download class="h-4 w-4 text-green-600" />
                    {{ trans('ui.export_geojson') }}
                </Button>
            </DropdownMenuItem>

            <DropdownMenuItem v-if="can.read_cadastral_group" class="p-0">
                <Link :href="route('cadastral-groups.show', props.group.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.view')" class="w-full justify-start">
                    <Eye class="h-4 w-4" /> {{ trans('ui.view') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <DropdownMenuItem v-if="can.update_cadastral_group" class="p-0">
                <Link :href="route('cadastral-groups.edit', props.group.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.edit')" class="w-full justify-start">
                    <Edit class="h-4 w-4" /> {{ trans('ui.edit') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <DropdownMenuItem v-if="can.delete_cadastral_group" class="p-0">
                <Button variant="ghost" size="sm" :title="trans('ui.delete')" @click="confirmDelete(props.group.id)"
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
