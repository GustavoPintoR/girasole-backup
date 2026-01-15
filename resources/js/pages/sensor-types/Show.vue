<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { TrafficCone, Edit, ArrowLeft, Clock, Shapes } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { SensorType } from '@/types';
import { it } from 'date-fns/locale';

const props = defineProps<{
    sensorType: SensorType;
}>();

const breadcrumbs = [
    {
        title: trans('ui.sensor_type'),
        href: route('sensor-types.index'),
    },
    {
        title: props.sensorType.name,
        href: route('sensor-types.show', props.sensorType.id),
    },
];

const handleBack = () => {
    router.visit(route('sensor-types.index'));
};

const handleEdit = () => {
    router.visit(route('sensor-types.edit', props.sensorType.id));
};

const { can } = usePermissions();
</script>

<template>
    <Head :title="trans('ui.sensor_type_show_header', { sensor_type: props.sensorType.name })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.sensor_type_show_header', { sensor_type: props.sensorType.name }) }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="can.update_sensor_type" @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <!-- Plant Disease Info -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Shapes class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.sensor_type_info') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.name') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.sensorType.name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.description') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.sensorType.description ?? '-' }}
                            </dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Sensor operations -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <TrafficCone class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.sensor_operations') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-2">
                        <template v-if="props.sensorType.sensor_operations?.length">
                            <Badge
                                v-for="operation in props.sensorType.sensor_operations"
                                :key="operation.id"
                                class="text-xs"
                            >
                                {{ operation.label }}
                            </Badge>
                        </template>
                        <template v-else>
                            <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                        </template>
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
                                {{ format(parseISO(props.sensorType.created_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.sensorType.updated_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.record_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                #{{ props.sensorType.id }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
