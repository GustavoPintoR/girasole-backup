<script setup lang="ts">
import { Eye, MoreHorizontal, Trash, Edit, Power } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
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
defineProps<{
    term: {
        id: number;
        version: string;
        is_active: boolean;
    }
}>()
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

const deleteDialogOpen = ref(false);
const activateDialogOpen = ref(false);
const selectedId = ref<number | null>(null);

function confirmDelete(id: number) {
    selectedId.value = id;
    deleteDialogOpen.value = true;
}

function deleteItem() {
    if (selectedId.value) {
        router.delete(route('terms-and-conditions.destroy', selectedId.value), {
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

function confirmActivate(id: number) {
    selectedId.value = id;
    activateDialogOpen.value = true;
}

function activateItem() {
    if (selectedId.value) {
        router.patch(route('terms-and-conditions.activate'), { 'id': selectedId.value }, {
            preserveScroll: true,
            onSuccess: () => {
                activateDialogOpen.value = false;
                selectedId.value = null;
            }
        });
    }
}

function cancelActivate() {
    activateDialogOpen.value = false;
    selectedId.value = null;
}

const { can } = usePermissions();
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

            <DropdownMenuItem v-if="!term.is_active && can.update_terms_and_conditions" class="p-0">
                <Button variant="ghost" size="sm" title="Activate" @click="confirmActivate(term.id)"
                    class="w-full justify-start">
                    <Power class="h-4 w-4 text-green-600" /> {{ trans('ui.activate') }}
                </Button>
            </DropdownMenuItem>

            <DropdownMenuSeparator />

            <DropdownMenuItem v-if="can.read_terms_and_conditions" class="p-0">
                <Link :href="route('terms-and-conditions.show', term.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.view')" class="w-full justify-start">
                    <Eye class="h-4 w-4" /> {{ trans('ui.view') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <DropdownMenuItem v-if="can.update_terms_and_conditions" class="p-0">
                <Link :href="route('terms-and-conditions.edit', term.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.edit')" class="w-full justify-start">
                    <Edit class="h-4 w-4" /> {{ trans('ui.edit') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <DropdownMenuItem class="p-0" v-if="!term.is_active && can.delete_terms_and_conditions">
                <Button variant="ghost" size="sm" :title="trans('ui.delete')" @click="confirmDelete(term.id)"
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

    <!-- Activate Confirmation Dialog -->
    <AlertDialog v-model:open="activateDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('ui.alert_dialog_title_version') }} {{ term.version }}?</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ trans('ui.alert_dialog_description_version') }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="cancelActivate">{{ trans('ui.cancel') }}</AlertDialogCancel>
                <AlertDialogAction class="bg-destructive text-white hover:bg-destructive-90" @click="activateItem">
                    {{ trans('ui.activate') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
