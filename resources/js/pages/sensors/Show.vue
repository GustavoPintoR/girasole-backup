<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Edit, MapPin, AlertCircle } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { computed, ref, nextTick, onMounted, watch } from 'vue';
import { Sensor } from '@/types';
import {
    MapboxMap,
    MapboxNavigationControl,
    MapboxMarker,
    MapboxPopup
} from '@studiometa/vue-mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import axios from 'axios';
import { LineChart } from '@/components/ui/chart-line';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

interface Operation {
    value: string;
    label: string;
}

interface Field {
    value: string;
    label: string;
}

interface ChartDataPoint {
    time: string;
    value: number;
}

const props = defineProps<{
    sensor: Sensor;
    selectedUserId?: number | null;
    chartData?: ChartDataPoint[];
    categories?: (keyof ChartDataPoint)[];
    selectedTime?: string;
    selectedOp?: string;
    selectedField?: string;
    errors?: Record<string, string>;
}>();

const breadcrumbs = [
    {
        title: trans('ui.sensors'),
        href: route('sensors.index'),
    },
    {
        title: props.sensor.name,
        href: route('sensors.show', props.sensor.id),
    },
];

const handleBack = () => {
    router.visit(route('sensors.index'));
};

const handleEdit = () => {
    router.visit(route('sensors.edit', props.sensor.id));
};

const { can } = usePermissions();
const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

// Mapbox state
const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);
const mapboxMap = ref();
const showPopup = ref(false);
const showMap = ref(false);

const mapCenter = computed(() => {
    if (props.sensor.latitude && props.sensor.longitude) {
        return [props.sensor.longitude, props.sensor.latitude];
    }
    return [12.50, 42.50];
});

const hasCoordinates = computed(() => {
    return props.sensor.latitude && props.sensor.longitude;
});

const companyName = computed(() => {
    return (props as any).sensor.company?.name ?? '-';
});

const toggleMap = () => {
    showMap.value = !showMap.value;
    if (showMap.value) {
        nextTick(() => {
            setTimeout(() => {
                showPopup.value = true;
            }, 500);
        });
    }
};

// Chart state
const selectedTime = ref(props.selectedTime || '-7d');
const selectedOp = ref(props.selectedOp || '');
const selectedField = ref(props.selectedField || '');
const ops = ref<Operation[]>([]);
const fields = ref<Field[]>([]);

const timeRanges = [
    { value: '-7d', label: trans('ui.range_days', { range: '7' }) },
    { value: '-15d', label: trans('ui.range_days', { range: '15' }) },
    { value: '-30d', label: trans('ui.range_days', { range: '30' }) },
];

const safeChartData = computed(() => {
    return Array.isArray(props.chartData) ? props.chartData : [];
});

const reactiveCategories = computed(() => {
    return props.categories || (['value'] as (keyof ChartDataPoint)[]);
});

onMounted(async () => {
    if (selectedTime.value && props.sensor.name) {
        try {
            const opsResponse = await axios.get('/api/ops', {
                params: { time: selectedTime.value, sens: props.sensor.name }
            });
            ops.value = opsResponse.data.ops.map((op: { name: string, label: string }) => ({
                value: op.label,
                label: op.name
            }));

            if (selectedOp.value && ops.value.length > 0) {
                const fieldsResponse = await axios.get('/api/sensor-fields', {
                    params: { time: selectedTime.value, sens: props.sensor.name, op: selectedOp.value }
                });
                fields.value = fieldsResponse.data.fields.map((field: { name: string, label: string }) => ({
                    value: field.label,
                    label: field.name
                }));
            }
        } catch (error) {
            console.error('Failed to load initial data:', error);
        }
    }
});

watch(() => props.selectedOp, (newVal) => {
    selectedOp.value = newVal || '';
});

watch(() => props.selectedField, (newVal) => {
    selectedField.value = newVal || '';
});

watch(selectedTime, async () => {
    if (selectedTime.value && props.sensor.name) {
        try {
            const response = await axios.get('/api/ops', {
                params: { time: selectedTime.value, sens: props.sensor.name }
            });
            ops.value = response.data.ops.map((op: { name: string, label: string }) => ({
                value: op.label,
                label: op.name
            }));
        } catch (error) {
            ops.value = [];
            console.error('Failed to fetch operations:', error);
        }
    }

    selectedOp.value = '';
    selectedField.value = '';
    fields.value = [];

    router.get(
        route('sensors.show', props.sensor.id),
        { time: selectedTime.value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['chartData', 'categories', 'selectedTime', 'selectedOp', 'selectedField', 'errors']
        }
    );
});

watch(selectedOp, async () => {
    if (selectedTime.value && props.sensor.name && selectedOp.value) {
        try {
            const response = await axios.get('/api/sensor-fields', {
                params: { time: selectedTime.value, sens: props.sensor.name, op: selectedOp.value }
            });
            fields.value = response.data.fields.map((field: { name: string, label: string }) => ({
                value: field.label,
                label: field.name
            }));
        } catch (error) {
            console.error('Failed to fetch fields:', error);
            fields.value = [];
        }
    } else {
        fields.value = [];
    }

    if (selectedOp.value !== props.selectedOp) {
        selectedField.value = '';
        router.get(
            route('sensors.show', props.sensor.id),
            { time: selectedTime.value, op: selectedOp.value },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                only: ['chartData', 'categories', 'selectedTime', 'selectedOp', 'selectedField', 'errors']
            }
        );
    }
});

watch(selectedField, () => {
    if (selectedTime.value && props.sensor.name && selectedOp.value && selectedField.value) {
        if (selectedField.value !== props.selectedField) {
            router.get(
                route('sensors.show', props.sensor.id),
                {
                    time: selectedTime.value,
                    op: selectedOp.value,
                    field: selectedField.value
                },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                    only: ['chartData', 'categories', 'selectedTime', 'selectedOp', 'selectedField', 'errors']
                }
            );
        }
    }
});
</script>

<template>

    <Head :title="props.sensor.type" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ props.sensor.name }} - {{ trans('ui.sensor') }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="hasCoordinates" variant="outline" @click="toggleMap">
                        <MapPin class="h-4 w-4 mr-2" />
                        {{ showMap ? trans('ui.hide_location') : trans('ui.view_location') }}
                    </Button>
                    <Button
                        v-if="can.update_sensor && currentUser && (currentUser.id === props.sensor.owner?.id || currentUser.is_admin)"
                        @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <!-- Main content grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                <!-- Left Column: Graphs -->
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ trans('ui.sensor_data') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <!-- Filters -->
                            <div class="flex justify-start items-center gap-4 mb-6">
                                <!-- Time Range -->
                                <div class="space-y-2">
                                    <Label>{{ trans('ui.time_range') }}</Label>
                                    <Select v-model="selectedTime">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_time_range')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="range in timeRanges" :key="range.value"
                                                :value="range.value">
                                                {{ range.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Operation -->
                                <div class="space-y-2">
                                    <Label>{{ trans('ui.operation') }}</Label>
                                    <Select v-model="selectedOp" :disabled="!selectedTime || ops.length === 0">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_sensor_operation')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="op in ops" :key="op.value" :value="op.value">
                                                {{ op.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Field -->
                                <div class="space-y-2">
                                    <Label>{{ trans('ui.field') }}</Label>
                                    <Select v-model="selectedField" :disabled="!selectedOp || fields.length === 0">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_sensor_field')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="field in fields" :key="field.value" :value="field.value">
                                                {{ field.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <!-- Error Alert -->
                            <Alert v-if="props.errors?.chart" variant="destructive" class="mb-6">
                                <AlertCircle class="h-4 w-4" />
                                <AlertTitle>{{ trans('ui.error') }}</AlertTitle>
                                <AlertDescription>{{ props.errors.chart }}</AlertDescription>
                            </Alert>

                            <!-- Chart -->
                            <div class="h-[400px] w-full">
                                <LineChart v-if="safeChartData.length > 0" :data="safeChartData" index="time"
                                    :categories="reactiveCategories" :colors="['green']"
                                    :y-formatter="(tick) => `${tick}`" />
                                <div v-else class="h-full flex items-center justify-center text-gray-500">
                                    {{ trans('ui.no_data_available') }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <!-- Map Card -->
                    <Card v-if="showMap && hasCoordinates" class="h-max">
                        <CardHeader>
                            <CardTitle>{{ trans('ui.sensor_location') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div class="relative h-[300px]">
                                <MapboxMap :access-token="mapboxAccessToken" ref="mapboxMap" class="h-full w-full"
                                    :center="mapCenter" map-style="mapbox://styles/mapbox/satellite-streets-v12"
                                    :zoom="15">
                                    <MapboxNavigationControl position="bottom-right" />

                                    <MapboxMarker :lng-lat="mapCenter">
                                        <div class="relative cursor-pointer">
                                            <div class="flex items-center justify-center">
                                                <MapPin fill="blue"
                                                    class="h-6 w-6 text-white transition-all scale-125" />
                                            </div>
                                        </div>
                                    </MapboxMarker>

                                    <MapboxPopup v-if="showPopup" :lng-lat="mapCenter" :offset="[0, -32]"
                                        :close-button="true" :close-on-click="false" @close="showPopup = false">
                                        <div class="p-2">
                                            <h3 class="font-semibold text-gray-900 mb-1">
                                                {{ props.sensor.sensor_type?.name || props.sensor.type }}
                                            </h3>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <p>
                                                    <span class="font-medium">{{ trans('ui.name') }}:</span>
                                                    {{ props.sensor.name }}
                                                </p>
                                                <p v-if="props.sensor.serial_number">
                                                    <span class="font-medium">{{ trans('ui.serial') }}:</span>
                                                    {{ props.sensor.serial_number }}
                                                </p>
                                                <p v-if="props.sensor.description">
                                                    <span class="font-medium">{{ trans('ui.description') }}:</span>
                                                    {{ props.sensor.description }}
                                                </p>
                                                <p>
                                                    <span class="font-medium">{{ trans('ui.latitude') }}:</span>
                                                    {{ props.sensor.latitude }}
                                                </p>
                                                <p>
                                                    <span class="font-medium">{{ trans('ui.longitude') }}:</span>
                                                    {{ props.sensor.longitude }}
                                                </p>
                                            </div>
                                        </div>
                                    </MapboxPopup>
                                </MapboxMap>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- Right Column: Sensor Info -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Sensor Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                {{ trans('ui.sensor_info') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.urn') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.urn ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.name') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.name ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.sensor_type') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor?.sensor_type?.name ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.serial') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.serial_number ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.iccid') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.iccid ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.transmission_module_identification') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100 max-w-full truncate"
                                        :title="props.sensor.transmission_module_identification">
                                        {{ props.sensor.transmission_module_identification ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.firmware') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.firmware ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.latitude') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.latitude ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.longitude') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.longitude ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.description') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.description || trans('ui.no_description') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.owner') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.owner?.full_name || props.sensor.owner?.first_name || trans('ui.no_owner') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.company') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ companyName }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ trans('ui.cadastral_group') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ props.sensor.cadastral_group?.name ?? '-' }}
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
