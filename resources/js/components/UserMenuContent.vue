<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

interface Props {
    user: User;
}

const page = usePage();
const isImpersonating = computed(() => Boolean(page.props.auth?.isImpersonating));

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                {{ trans('ui.settings') }}
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem v-if="!isImpersonating" :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            {{ trans('ui.logout') }}
        </Link>
    </DropdownMenuItem>
    <DropdownMenuItem v-else :as-child="true">
        <Link class="block w-full" method="get" :href="route('impersonate.leave')" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            {{ trans('ui.leave_impersonating') }}
        </Link>
    </DropdownMenuItem>
</template>
