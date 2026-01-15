<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Check, Clock, Edit, MapPin, X } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { Button } from '@/components/ui/button';
import { format, parseISO } from 'date-fns';
import { usePermissions } from '@/composables/usePermissions';
import { it } from 'date-fns/locale';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        permissions: { id: number; name: string; guard_name: string; created_at: string; updated_at: string; pivot: { role_id: number; permission_id: number } }[];
    };
    permissionGroups: { [key: string]: { [key: string]: string } };
}>();

const breadcrumbs = [
    {
        title: trans('ui.roles'),
        href: route('roles.index'),
    },
    {
        title: trans(`ui.${props.role.name}`),
        href: route('roles.show', props.role.id),
    },
];

const rolePermissions = computed(() => new Set(props.role.permissions.map(perm => perm.name)));

const activeTab = ref(Object.keys(props.permissionGroups)[0] || 'others');

const formatPermissionName = (name: string) => {
    return name
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const handleBack = () => {
    router.visit(route('roles.index'));
};

const handleEdit = () => {
    router.visit(route('roles.edit', props.role.id));
};

const { can } = usePermissions();

</script>

<template>
    <Head :title="trans('ui.role_show_header', { role: props.role.name })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.role_show_header', { role: trans(`ui.${props.role.name}`) }) }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="can.update_role" @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <Card class="w-full mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <MapPin class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.role_info') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.role') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ trans(`ui.${props.role.name}`) }}
                            </dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="w-full mb-6">
                <CardHeader>
                    <CardTitle>{{ trans('ui.role_permissions') }}</CardTitle>
                    <CardDescription>{{ trans('ui.role_permissions_desc') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- Permissions -->
                    <div class="space-y-4 w-full">
                        <Label class="text-sm font-medium">{{ trans('ui.permissions') }}</Label>
                        <Tabs v-model="activeTab" class="w-full">
                            <TabsList class="flex overflow-x-auto gap-2 w-full h-11 justify-start scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-800">
                                <TabsTrigger
                                    v-for="model in Object.keys(permissionGroups)"
                                    :key="model"
                                    :value="model"
                                >
                                    {{ trans(`ui.${model}`) }}
                                </TabsTrigger>
                            </TabsList>
                            <TabsContent
                                v-for="(permissions, model) in permissionGroups"
                                :key="model"
                                :value="model"
                                class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-2"
                            >
                                <div class="text-sm font-medium">{{ trans(`ui.${model}`) }} {{ trans('ui.permissions') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ trans('ui.permissions_in_group', { group: trans(`ui.${model}`) }) }}
                                </div>
                                <div v-if="Object.values(permissions).length">
                                    <div
                                        v-for="permission in Object.values(permissions)"
                                        :key="permission"
                                        class="flex items-center space-x-2 py-1"
                                    >
                                        <Badge
                                            v-if="rolePermissions.has(permission)"
                                            variant="default"
                                            class="flex items-center gap-1"
                                        >
                                            <Check class="h-4 w-4" />
                                            {{ formatPermissionName(permission) }}
                                        </Badge>
                                        <Badge
                                            v-else
                                            variant="secondary"
                                            class="flex items-center gap-1"
                                        >
                                            <X class="h-4 w-4" />
                                            {{ formatPermissionName(permission) }}
                                        </Badge>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ trans('ui.no_permissions_in_group') }}
                                </div>
                            </TabsContent>
                        </Tabs>
                    </div>
                </CardContent>
            </Card>

            <!-- Metadata -->
            <Card class="w-full">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.metadata') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.created_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.role.created_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.role.updated_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.record_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                #{{ props.role.id }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure consistent spacing and readability */
.card-content div {
    word-break: break-word;
}
</style>
