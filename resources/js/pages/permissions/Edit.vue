<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { Permission } from '@/types';


const props = defineProps<{
    permission: Permission;
}>();

const breadcrumbs = [
    {
        title: trans('ui.roles'),
        href: route('roles.index'),
    },
    {
        title: trans('ui.edit'),
        href: route('roles.edit', props.permission.id),
    },
];

const form = useForm({
    name: props.permission.name,
});

const submit = () => {
    form.put(route('roles.update', props.permission.id), {
        preserveScroll: true,
        onSuccess: () => {
            //
        },
    });
};

const cancel = () => {
    router.visit(route('roles.index'));
};
</script>

<template>

    <Head :title="trans('ui.permission_edit_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.permission_edit_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.permission_edit_header_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.permission_edit_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Version -->
                        <div class="space-y-2">
                            <Label for="name">
                                {{ trans('ui.name') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input id="version" v-model="form.name" class="h-11 w-[462px]" type="text" :placeholder="trans('ui.name')"
                                :class="{ 'border-red-500': form.errors.name }" :disabled="form.processing" />
                            <p v-if="form.errors.name" class="text-sm text-red-500">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? trans('ui.terms_updating') : trans('ui.update_terms') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Information -->
            <Card class="max-w-4xl mt-4">
                <CardHeader>
                    <CardTitle class="text-base">{{ trans('ui.information') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.created_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.permission.created_at), 'PPP p') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.permission.updated_at), 'PPP p') }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
