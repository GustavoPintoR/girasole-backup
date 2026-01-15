<script setup lang="ts">
import { Eye, MoreHorizontal, Trash, Edit, LockKeyholeOpen, CircleDollarSign, RotateCcw, Trash2 } from 'lucide-vue-next'
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
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

interface Subscription {
    stripe_price: string;
    stripe_status: string;
    ends_at: string | null;
    type: string  | null;
    on_grace_period: boolean;
}

const props = defineProps<{
    user: {
        id: number;
        first_name: string,
        last_name: string,
        email: string,
        active: number,
        mobile_phone: string,
        subscriptions: Subscription[],
        deleted_at: string | null
    }
}>()

const deleteDialogOpen = ref(false);
const restoreDialogOpen = ref(false);
const forceDeleteDialogOpen = ref(false);
const selectedId = ref<number | null>(null);

const page = usePage();
const canImpersonate = computed(() => Boolean(page.props.auth?.canImpersonate));
const isSelf = computed(() => page.props.auth?.user?.id === props.user.id);
const isTrashed = computed(() => !!props.user.deleted_at);

function impersonate(id: number) {
    router.get(route('impersonate', id), {}, { preserveScroll: true });
}

function confirmDelete(id: number) {
    selectedId.value = id;
    deleteDialogOpen.value = true;
}

function confirmRestore(id: number) {
    selectedId.value = id;
    restoreDialogOpen.value = true;
}

function confirmForceDelete(id: number) {
    selectedId.value = id;
    forceDeleteDialogOpen.value = true;
}

function deleteItem() {
    if (selectedId.value) {
        router.delete(route('users.destroy', selectedId.value), {
            preserveScroll: true,
            onSuccess: () => {
                deleteDialogOpen.value = false;
                selectedId.value = null;
            }
        });
    }
}

function restoreItem() {
    if (selectedId.value) {
        router.post(route('users.restore', selectedId.value), {}, {
            preserveScroll: true,
            onSuccess: () => {
                restoreDialogOpen.value = false;
                selectedId.value = null;
            }
        });
    }
}

function forceDeleteItem() {
    if (selectedId.value) {
        router.delete(route('users.force-delete', selectedId.value), {
            preserveScroll: true,
            onSuccess: () => {
                forceDeleteDialogOpen.value = false;
                selectedId.value = null;
            }
        });
    }
}

function cancelDelete() {
    deleteDialogOpen.value = false;
    selectedId.value = null;
}

function cancelRestore() {
    restoreDialogOpen.value = false;
    selectedId.value = null;
}

function cancelForceDelete() {
    forceDeleteDialogOpen.value = false;
    selectedId.value = null;
}

const { can } = usePermissions();

const isManualSubscription = computed(() => {
    // Check if there are subscriptions, and if the first one's type is 'manual'
    return props.user.subscriptions.length == 0 || props.user.subscriptions[0]?.type === 'manual';
});

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

            <!-- Actions for non-trashed users -->
            <template v-if="!isTrashed">
                <DropdownMenuItem v-if="can.read_user" class="p-0">
                    <Link :href="route('users.show', user.id)" class="w-full">
                    <Button variant="ghost" size="sm" :title="trans('ui.view')" class="w-full justify-start">
                        <Eye class="h-4 w-4" /> {{ trans('ui.view') }}
                    </Button>
                    </Link>
                </DropdownMenuItem>

                <DropdownMenuItem v-if="can.update_user" class="p-0">
                    <Link :href="route('users.edit', user.id)" class="w-full">
                    <Button variant="ghost" size="sm" :title="trans('ui.edit')" class="w-full justify-start">
                        <Edit class="h-4 w-4" /> {{ trans('ui.edit') }}
                    </Button>
                    </Link>
                </DropdownMenuItem>

                <Button v-if="can.delete_user" variant="ghost" size="sm" :title="trans('ui.delete')" @click="confirmDelete(user.id)"
                    class="w-full justify-start">
                    <Trash class="h-4 w-4 text-red-600" /> {{ trans('ui.delete') }}
                </Button>

                <DropdownMenuSeparator />

                <DropdownMenuItem v-if="can.update_user" class="p-0">
                    <Link :href="route('users.view-plan', user.id)" class="w-full">
                    <Button variant="ghost" size="sm" :title="trans('ui.view')" class="w-full justify-start">
                        <Eye class="h-4 w-4" /> {{ trans('ui.view_plan') }}
                    </Button>
                    </Link>
                </DropdownMenuItem>

                <DropdownMenuItem v-if="can.update_user && isManualSubscription"  class="p-0">
                    <Link :href="route('users.manage-plan', user.id)" class="w-full">
                    <Button variant="ghost" size="sm" title="Manage" class="w-full justify-start">
                        <CircleDollarSign class="h-4 w-4" /> {{ trans('ui.manual_plan') }}
                    </Button>
                    </Link>
                </DropdownMenuItem>

                <DropdownMenuSeparator />

                <DropdownMenuItem v-if="canImpersonate && !isSelf" class="p-0">
                    <Button variant="ghost" size="sm" title="Impersonate" class="w-full justify-start" @click="impersonate(user.id)">
                        <LockKeyholeOpen class="h-4 w-4" /> {{ trans('ui.impersonate') }}
                    </Button>
                </DropdownMenuItem>
            </template>

            <!-- Actions for trashed users -->
            <template v-else>
                <DropdownMenuItem v-if="can.restore_user" class="p-0">
                    <Button variant="ghost" size="sm" :title="trans('ui.restore')" @click="confirmRestore(user.id)"
                        class="w-full justify-start">
                        <RotateCcw class="h-4 w-4 text-green-600" /> {{ trans('ui.restore') }}
                    </Button>
                </DropdownMenuItem>

                <DropdownMenuItem v-if="can.force_delete_user" class="p-0">
                    <Button variant="ghost" size="sm" :title="trans('ui.permanent_delete')" @click="confirmForceDelete(user.id)"
                        class="w-full justify-start">
                        <Trash2 class="h-4 w-4 text-red-600" /> {{ trans('ui.permanent_delete') }}
                    </Button>
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('ui.delete_confirmation') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{trans('ui.can_be_undone') }}
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

    <!-- Restore Confirmation Dialog -->
    <AlertDialog v-model:open="restoreDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('ui.restore_confirmation') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ trans('ui.restore_confirmation_text') }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="cancelRestore">{{ trans('ui.cancel') }}</AlertDialogCancel>
                <AlertDialogAction class="bg-green-600 text-white hover:bg-green-700" @click="restoreItem">
                    {{ trans('ui.restore') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <!-- Force Delete Confirmation Dialog -->
    <AlertDialog v-model:open="forceDeleteDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('ui.permanent_delete_confirmation') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ trans('ui.permanent_delete_confirmation_text') }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="cancelForceDelete">{{ trans('ui.cancel') }}</AlertDialogCancel>
                <AlertDialogAction class="bg-destructive text-white hover:bg-destructive-90" @click="forceDeleteItem">
                    {{ trans('ui.permanent_delete') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
