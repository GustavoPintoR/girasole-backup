<script setup lang="ts">
import { Eye, MoreHorizontal, Trash, Edit } from 'lucide-vue-next'
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

const props = defineProps<{
    event: {
        id: number;
        name: string;
        cadastral_code: string;
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
        router.delete(route('events.destroy', selectedId.value), {
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

const { can } = usePermissions();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">{{ $t('ui.open_menu') }}</span>

                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end">
            <DropdownMenuLabel>{{ trans('ui.actions') }}</DropdownMenuLabel>

            <DropdownMenuItem v-if="can.read_event" class="p-0">
                <Link :href="route('events.show', props.event.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.view')" class="w-full justify-start">
                    <Eye class="h-4 w-4" /> {{ $t('ui.view') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <DropdownMenuItem v-if="can.update_event" class="p-0">
                <Link :href="route('events.edit', props.event.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.edit')" class="w-full justify-start">
                    <Edit class="h-4 w-4" /> {{ trans('ui.edit') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <DropdownMenuItem v-if="can.delete_event" class="p-0">
                <Button variant="ghost" size="sm" :title="trans('ui.delete')" @click="confirmDelete(props.event.id)"
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
                <AlertDialogTitle>{{ trans('ui.are_you_sure_question') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ trans('ui.action_cannot_be_undone') }}
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
