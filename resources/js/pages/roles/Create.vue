<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Checkbox } from '@/components/ui/checkbox';
import { trans } from 'laravel-vue-i18n';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

const breadcrumbs = [
    { title: trans('ui.roles'), href: route('roles.index') },
    { title: trans('ui.create'), href: route('roles.create') }
];

const props = defineProps<{
    permissionGroups: { [key: string]: string[] };
}>();

const form = useForm({
    name: '',
    permissions: [] as string[]
});

const activeTab = ref(Object.keys(props.permissionGroups)[0] || 'address');

function submit() {
    form.post(route('roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('roles.index'));
        },
    });
}
const cancel = () => {
    router.visit(route('roles.index'));
};

const togglePermission = (permission: string) => {
    const index = form.permissions.indexOf(permission);
    if (index === -1) {
        form.permissions.push(permission);
    } else {
        form.permissions.splice(index, 1);
    }
};

const formatPermissionName = (name: string) => {
    return name
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>

<template>
    <Head :title="trans('ui.role_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.role_create_header') }}
                </h1>
            </div>

            <Card class="w-full">
                <CardHeader>
                    <CardTitle>{{ trans('ui.role_create_details') }}</CardTitle>
                    <CardDescription>{{ trans('ui.role_create_desc') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Role Name -->
                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-medium">
                                {{ trans('ui.name') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                v-model="form.name"
                                :placeholder="trans('ui.name')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.name }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

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
                                    <div class="text-sm font-medium">{{ trans(`ui.${model}`) }} {{trans('ui.permissions')}}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ trans('ui.select_permissions_for', { model: trans(`ui.${model}`) }) }}
                                    </div>
                                    <div
                                        v-for="permission in permissions"
                                        :key="permission"
                                        class="flex items-center space-x-2"
                                    >
                                        <Checkbox
                                            :id="permission"
                                            :model-value="form.permissions.includes(permission)"
                                            @update:model-value="togglePermission(permission)"
                                            :disabled="form.processing"
                                        />
                                        <Label :for="permission" class="text-sm">
                                            {{ formatPermissionName(permission) }}
                                        </Label>
                                    </div>
                                </TabsContent>
                            </Tabs>
                            <InputError :message="form.errors.permissions" class="mt-2" />
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                        {{ trans('ui.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing" @click="submit">
                        {{ form.processing ? trans('ui.creating') : trans('ui.create_role') }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
