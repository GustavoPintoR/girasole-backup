<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Grid, Edit, ArrowLeft, Clock } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { PlantingScheme } from '@/types';
import { it } from 'date-fns/locale';

const props = defineProps<{
    plantingScheme: PlantingScheme;
}>();

const breadcrumbs = [
    {
        title: trans('ui.planting_schemes'),
        href: route('planting-schemes.index'),
    },
    {
        title: props.plantingScheme.name,
        href: route('planting-schemes.show', props.plantingScheme.id),
    },
];

const handleBack = () => {
    router.visit(route('planting-schemes.index'));
};

const handleEdit = () => {
    router.visit(route('planting-schemes.edit', props.plantingScheme.id));
};

const { can } = usePermissions();

</script>

<template>
    <Head :title="trans('ui.planting_scheme_show_header', { planting_scheme: props.plantingScheme.name })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{trans('ui.planting_scheme_show_header', { planting_scheme: props.plantingScheme.name })}}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="can.update_planting_scheme" @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Grid class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.planting_scheme_info') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.name') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.plantingScheme.name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.pattern') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ trans(`ui.${props.plantingScheme.pattern}`) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.row_spacing') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.plantingScheme.row_spacing }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.plant_spacing') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.plantingScheme.plant_spacing }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.distance') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.plantingScheme.distance }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.description') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.plantingScheme.description ?? '-' }}
                            </dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Metadata -->
            <Card class="max-w-4xl">
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
                                {{ format(parseISO(props.plantingScheme.created_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.plantingScheme.updated_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.record_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                #{{ props.plantingScheme.id }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
