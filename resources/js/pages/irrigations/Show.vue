<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Grid, Edit, ArrowLeft } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { Irrigation } from '@/types';

const props = defineProps<{
    irrigation: Irrigation;
}>();

const breadcrumbs = [
    {
        title: trans('ui.irrigation'),
        href: route('irrigations.index'),
    },
    {
        title: props.irrigation.type,
        href: route('irrigations.show', props.irrigation.id),
    },
];

const handleBack = () => {
    router.visit(route('irrigations.index'));
};

const handleEdit = () => {
    router.visit(route('irrigations.edit', props.irrigation.id));
};

const { can } = usePermissions();
</script>

<template>
    <Head :title="trans('ui.irrigation_show_header', { irrigation: props.irrigation.type })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.irrigation_show_header', { irrigation: props.irrigation.type }) }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="can.update_irrigation" @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <!-- Plant Disease Info -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Grid class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.irrigation_info') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.type') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.irrigation.type }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.description') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.irrigation.description ?? '-' }}
                            </dd>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
