<script setup lang="ts">
import { Eye, MoreHorizontal, Trash, CheckCheck } from 'lucide-vue-next';
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
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { Notification,  } from '@/types';

const props = defineProps<{
    notification: Notification
}>()

const isRead = computed(() => props.notification.read_at !== null);

function markAsRead(id: string) {
    router.get(route('notification.markAsRead', id), {}, { preserveScroll: true });
}

const deleteDialogOpen = ref(false);
const selectedId = ref<string| null>(null);

function confirmDelete(id: string) {
    selectedId.value = id;
    deleteDialogOpen.value = true;
}

function deleteItem() {
    if (selectedId.value) {
        router.delete(route('notifications.destroy', selectedId.value), {
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
                <span class="sr-only">{{ trans('ui.open_menu') }}</span>

                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end">
            <DropdownMenuLabel>{{ trans('ui.actions') }}</DropdownMenuLabel>

            <DropdownMenuSeparator />

            <DropdownMenuItem v-if="can.read_notification" class="p-0">
                <Link :href="route('notifications.show', props.notification.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.view')" class="w-full justify-start">
                    <Eye class="h-4 w-4" /> {{ trans('ui.view') }}
                </Button>
                </Link>
            </DropdownMenuItem>

            <Button v-if="can.delete_notification" variant="ghost" size="sm" :title="trans('ui.delete')" @click="confirmDelete(props.notification.id)"
                class="w-full justify-start">
                <Trash class="h-4 w-4 text-red-600" /> {{ trans('ui.delete') }}
            </Button>

            <DropdownMenuSeparator v-if="!isRead" />

            <DropdownMenuItem v-if="!isRead" class="p-0">
                <Button variant="ghost" size="sm" title="Mark as read" class="w-full justify-start" @click="markAsRead(props.notification.id)">
                    <CheckCheck class="h-4 w-4" /> {{ trans('ui.mark_as_read') }}
                </Button>
            </DropdownMenuItem>

        </DropdownMenuContent>
    </DropdownMenu>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('ui.delete_confirmation') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{trans('ui.delete_confirmation_text') }}
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
