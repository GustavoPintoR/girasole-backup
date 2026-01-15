<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { trans } from 'laravel-vue-i18n';
import { Label } from '@/components/ui/label';
import { AlertCircle, MapPin, ChevronDown } from 'lucide-vue-next';
import { LineChart } from '@/components/ui/chart-line';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectGroup, SelectItem } from '@/components/ui/select';
import 'mapbox-gl/dist/mapbox-gl.css';
import { Card, CardContent } from '@/components/ui/card';
import CadastralGroupsMap from '@/components/shared/CadastralGroupsMap.vue';
import MetricsCards from '@/components/dashboard/MetricsCards.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import ForecastDashboard from '@/components/forecast/ForecastDashboard.vue';

interface Sensor {
    id: number;
    sensor: string;
    longitude: number;
    latitude: number;
    serial: string;
    type: string;
    description: string;
    Mod: string;
}

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

interface Metrics {
    cadastralGroups: number;
    sensors: number;
}

interface GroupData {
    id: number;
    name: string;
    total_area: number;
    units_count: number;
    creation_method: 'units' | 'manual' | 'import';
    color: string;
    boundary_geometry_json: string | null;
    centroid_json: string | null;
    sensors?: any[];
    company?: {
        id: number;
        name: string;
    };
}

const page = usePage();

const props = defineProps<{
    sensors: Sensor[];
    chartData: ChartDataPoint[];
    categories: (keyof ChartDataPoint)[];
    metrics: Metrics;
    showSensors?: boolean;
    showWeather?: boolean;
    field?: string;
    selectedTime?: string;
    selectedSens?: string;
    selectedOp?: string;
    selectedField?: string;
}>();

// init state from props if available
const selectedTime = ref(props.selectedTime || '');
const selectedSens = ref(props.selectedSens || '');
const selectedOp = ref(props.selectedOp || '');
const selectedField = ref(props.selectedField || '');

onMounted(async () => {
    // load init ops and fields on preselected values
    if (selectedTime.value && selectedSens.value) {
        try {
            const opsResponse = await axios.get('/api/ops', {
                params: { time: selectedTime.value, sens: selectedSens.value }
            });
            ops.value = opsResponse.data.ops.map((op: { name: string, label: string }) => ({
                value: op.label,
                label: op.name
            }));

            if (selectedOp.value && ops.value.length > 0) {
                const fieldsResponse = await axios.get('/api/sensor-fields', {
                    params: { time: selectedTime.value, sens: selectedSens.value, op: selectedOp.value }
                });
                fields.value = fieldsResponse.data.fields.map((field: { name: string, label: string }) => ({
                    value: field.label,
                    label: field.name
                }));
            }
        } catch (error) {
            console.error('Failed to load initial operations/fields:', error);
        }
    }

    loadGroupsData();
    document.addEventListener('click', handleClickOutside);
});
const ops = ref<Operation[]>([]);
const fields = ref<Field[]>([]);

const cadastralGroups = ref<GroupData[]>([]);
const selectedGroupId = ref<number | null>(null);
const shouldShowCompany = ref(false);
const dropdownOpen = ref(false);
const selectedGroup = computed(() =>
    selectedGroupId.value ? cadastralGroups.value.find(g => g.id === selectedGroupId.value) : null
);

// keep page at top (temp: because mapbox steals focus, need to investigate further)
const enforceScroll = ref(true);
let _mutationObserver: MutationObserver | null = null;
let _enforceTimeout: number | null = null;

const forecastData = ref<any>(null);
const isLoadingForecast = ref(false);

const shouldShowForecast = computed(() => {
    return forecastData.value && forecastData.value.hasAnyData === true;
});

const loadForecastData = async (groupId: number) => {
    isLoadingForecast.value = true;
    forecastData.value = null;
    try {
        const response = await axios.get(route('api.internal.forecast-logs.get-data', { cadastralGroup: groupId, user: page.props.auth.user.id }));
        forecastData.value = response.data;
    } catch (error) {
        console.error('Error loading forecast data:', error);
    } finally {
        isLoadingForecast.value = false;
    }
};

watch(selectedGroupId, (newId) => {
    if (newId) {
        loadForecastData(newId);
    } else {
        forecastData.value = null;
    }
});

const loadGroupsData = async () => {
    try {
        const response = await fetch(route('api.internal.cadastral-groups.mapData', {
            user: page.props.auth.user.id
        }));
        const data = await response.json();
        cadastralGroups.value = data.groups || [];
        shouldShowCompany.value = data.shouldShowCompany || false;
        selectedGroupId.value = cadastralGroups.value.length > 0 ? cadastralGroups.value[0].id : null;
    } catch (error) {
        console.error('Error loading groups data:', error);
    }
};

const selectGroup = (id: number) => {
    selectedGroupId.value = id;
    dropdownOpen.value = false;
};

const getCreationMethodLabel = (method: string): string => {
    const labels: Record<string, string> = {
        units: trans('ui.from_units'),
        manual: trans('ui.drawn'),
        import: trans('ui.imported')
    };
    return labels[method] || method;
};

const getCreationMethodVariant = (method: string): string => {
    const variants: Record<string, string> = {
        units: 'default',
        manual: 'secondary',
        import: 'outline'
    };
    return variants[method] || 'outline';
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.custom-dropdown')) {
        dropdownOpen.value = false;
    }
};

onMounted(() => {
    loadGroupsData();
    document.addEventListener('click', handleClickOutside);

    try {
        window.scrollTo(0, 0);

        enforceScroll.value = true;
        _mutationObserver = new MutationObserver(() => {
            if (enforceScroll.value) {
                window.scrollTo(0, 0);
            }
        });
        _mutationObserver.observe(document.body, { childList: true, subtree: true, attributes: true, characterData: true });

        // quit enforcing after 2 sexs
        _enforceTimeout = window.setTimeout(() => {
            enforceScroll.value = false;
            if (_mutationObserver) {
                _mutationObserver.disconnect();
                _mutationObserver = null;
            }
            if (_enforceTimeout) {
                _enforceTimeout = null;
            }
        }, 2000);
    } catch (e) {
        // ignore if window/document not available in some environments
        console.error('scroll error:', e);
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);

    if (_mutationObserver) {
        _mutationObserver.disconnect();
        _mutationObserver = null;
    }
    if (_enforceTimeout) {
        clearTimeout(_enforceTimeout);
        _enforceTimeout = null;
    }
});

const safeChartData = computed(() => {
    return Array.isArray(props.chartData) ? props.chartData : [];
});

// Debug categories to verify updates
const reactiveCategories = computed(() => {
    console.log('Categories updated:', props.categories);
    return props.categories;
});

// Key to force chart re-render
const chartKey = computed(() => `${selectedField.value}-${props.categories.join('-')}`);

const timeRanges = [
    { value: '-7d', label: trans('ui.range_days', { range: '7' }) },
    { value: '-15d', label: trans('ui.range_days', { range: '15' }) },
    { value: '-30d', label: trans('ui.range_days', { range: '30' }) },
];

const sensorOptions = computed(() => {
    return props.sensors.map((sensor) => ({
        value: sensor.sensor,
        label: sensor.sensor
    }));
});

const canViewDashboardData = computed(() => { return props.sensors.length > 0 || page.props.auth.isSuperAdmin; });

watch(selectedTime, () => {
    selectedSens.value = '';
    selectedOp.value = '';
    selectedField.value = '';
    ops.value = [];
    fields.value = [];
    router.get(
        '/dashboard',
        { time: selectedTime.value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['sensors', 'chartData', 'categories']
        }
    );
});

watch([selectedTime, selectedSens], async () => {
    if (selectedTime.value && selectedSens.value) {
        try {
            const response = await axios.get('/api/ops', {
                params: { time: selectedTime.value, sens: selectedSens.value }
            });
            ops.value = response.data.ops.map((op: { name: string, label: string }) => ({
                value: op.label,
                label: op.name
            }));
        } catch (error) {
            console.error('Failed to fetch operations:', error);
            ops.value = [];
        }
    } else {
        ops.value = [];
    }
    selectedOp.value = '';
    selectedField.value = '';
    fields.value = [];
    router.get(
        '/dashboard',
        { time: selectedTime.value, sens: selectedSens.value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['sensors', 'chartData', 'categories']
        }
    );
});

watch([selectedSens, selectedOp], async () => {
    if (selectedTime.value && selectedSens.value && selectedOp.value) {
        try {
            const response = await axios.get('/api/sensor-fields', {
                params: { time: selectedTime.value, sens: selectedSens.value, op: selectedOp.value }
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
    selectedField.value = '';
    router.get(
        '/dashboard',
        { time: selectedTime.value, sens: selectedSens.value, op: selectedOp.value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['sensors', 'chartData', 'categories']
        }
    );
});

watch(selectedField, () => {
    if (selectedTime.value && selectedSens.value && selectedOp.value && selectedField.value) {
        router.get(
            '/dashboard',
            {
                time: selectedTime.value,
                sens: selectedSens.value,
                op: selectedOp.value,
                field: selectedField.value
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                only: ['chartData', 'categories'],
                onSuccess: () => {
                }
            }
        );
    } else {
        router.get(
            '/dashboard',
            {
                time: selectedTime.value,
                sens: selectedSens.value,
                op: selectedOp.value,
                field: ''
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                only: ['chartData', 'categories']
            }
        );
    }
});

const breadcrumbs = [
    {
        title: trans('ui.dashboard'),
        href: '/dashboard',
    },
];
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="content">
            <Alert v-if="$page.props.errors.sensor" variant="destructive">
                <AlertCircle class="w-4 h-4" />
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>
                    {{ $page.props.errors.sensor }}
                </AlertDescription>
            </Alert>

            <Alert v-if="$page.props.errors.chart" variant="destructive">
                <AlertCircle class="w-4 h-4" />
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>
                    {{ $page.props.errors.chart }}
                </AlertDescription>
            </Alert>
        </div>

        <!-- Cadastral Group Selection -->
        <div class="bg-white dark:bg-black border border-gray-200 dark:border-black p-6 mb-6">
             <Label class="mb-2 font-medium text-gray-700 dark:text-gray-300 block">{{ trans('ui.cadastral_group') }}</Label>
             <!-- Custom dropdown selector -->
            <div class="custom-dropdown relative w-full md:w-[300px]">
                <button @click="dropdownOpen = !dropdownOpen"
                    class="w-auto px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors text-left">
                    <span v-if="selectedGroup" class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded flex-shrink-0" :style="{ backgroundColor: selectedGroup.color }" />
                        <span class="truncate">
                            {{ selectedGroup.name }}
                            <span v-if="shouldShowCompany && selectedGroup['company']?.name" class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ selectedGroup['company'].name }})</span>
                        </span>
                    </span>
                    <span v-else class="text-gray-500 dark:text-gray-400">{{ trans('ui.select_cadastral_group') }}</span>
                    <ChevronDown class="h-4 w-4 text-gray-400 flex-shrink-0" />
                </button>

                <!-- Dropdown menu -->
                <div v-if="dropdownOpen"
                    class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 max-h-[300px] overflow-y-auto z-50">
                    <div v-for="group in cadastralGroups" :key="group.id" @click="selectGroup(group.id)"
                        class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div class="w-3 h-3 rounded flex-shrink-0" :style="{ backgroundColor: group.color }" />
                        <span class="flex-1 truncate">
                            {{ group.name }}
                            <span v-if="shouldShowCompany && group['company']?.name" class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ group['company'].name }})</span>
                        </span>
                        <div class="flex items-center gap-1">
                            <Badge v-if="group.sensors?.length" variant="outline" class="text-xs">
                                <MapPin class="h-3 w-3 mr-1" />
                                {{ group.sensors.length }}
                            </Badge>
                            <Badge :variant="getCreationMethodVariant(group.creation_method) as any" class="text-xs">
                                {{ getCreationMethodLabel(group.creation_method) }}
                            </Badge>
                        </div>
                    </div>
                                        <div v-if="cadastralGroups.length === 0" class="px-3 py-2 text-gray-500 text-sm">
                        {{ trans('ui.no_groups_available') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Forecast Dashboard -->
        <div v-if="shouldShowForecast" class="p-6 pt-0 mb-6">
            <ForecastDashboard
                :header="forecastData.header"
                :water-balance="forecastData.waterBalance"
                :growth-risks="forecastData.growthRisks"
                :evolution-timeline="forecastData.evolutionTimeline"
                :weather-list="forecastData.weatherList"
                :soil-list="forecastData.soilList"
                :has-water-balance-data="forecastData.hasWaterBalanceData"
                :has-growth-risks-data="forecastData.hasGrowthRisksData"
                :has-evolution-timeline-data="forecastData.hasEvolutionTimelineData"
                :has-weather-list-data="forecastData.hasWeatherListData"
                :has-soil-list-data="forecastData.hasSoilListData"
            />
        </div>
        <div v-else-if="isLoadingForecast" class="mb-6 p-6 bg-white dark:bg-black border border-gray-200 dark:border-black flex justify-center">
             <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white"></div>
        </div>

        <!-- Metrics Cards -->
        <div v-if="canViewDashboardData" class="p-6">
            <MetricsCards :metrics="props.metrics" />
        </div>

        <!-- Controls or No Sensors Message -->
        <div v-if="canViewDashboardData" class="bg-white dark:bg-black border border-gray-200 dark:border-black p-6">
            <div v-if="props.sensors.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <div class="flex flex-col">
                    <Label for="time" class="mb-2 font-medium text-gray-700 dark:text-gray-300">{{ trans('ui.time_range') }}</Label>
                    <Select v-model="selectedTime">
                        <SelectTrigger :data-size="11"
                                       class="w-full border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 focus:ring-2 duration-150">
                            <SelectValue :placeholder="trans('ui.time_range')" />
                        </SelectTrigger>
                        <SelectContent class="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow-lg rounded-md">
                            <SelectGroup>
                                <SelectItem v-for="range in timeRanges" :key="range.value" :value="range.value"
                                            class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ range.label }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex flex-col">
                    <Label for="sensor" class="mb-2 font-medium text-gray-700 dark:text-gray-300">{{ trans('ui.sensor') }}</Label>
                    <Select v-model="selectedSens" :disabled="!selectedTime">
                        <SelectTrigger :data-size="11"
                                       class="w-full border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 focus:ring-2 transition duration-150 disabled:bg-gray-100 dark:disabled:bg-gray-600 disabled:cursor-not-allowed">
                            <SelectValue :placeholder="trans('ui.select_sensor')" />
                        </SelectTrigger>
                        <SelectContent class="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow-lg rounded-md">
                            <SelectGroup>
                                <SelectItem v-for="sensor in sensorOptions" :key="sensor.value" :value="sensor.value"
                                            class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ sensor.label }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex flex-col">
                    <Label for="op" class="mb-2 font-medium text-gray-700 dark:text-gray-300">{{ trans('ui.select_sensor_operation') }}</Label>
                    <Select v-model="selectedOp" :disabled="!selectedTime || !selectedSens">
                        <SelectTrigger :data-size="11"
                                       class="w-full border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 focus:ring-2 transition duration-150 disabled:bg-gray-100 dark:disabled:bg-gray-600 disabled:cursor-not-allowed">
                            <SelectValue :placeholder="trans('ui.sensor_operation')" />
                        </SelectTrigger>
                        <SelectContent class="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow-lg rounded-md">
                            <SelectGroup>
                                <SelectItem v-for="op in ops" :key="op.value" :value="op.value" class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ op.label }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex flex-col">
                    <Label for="field" class="mb-2 font-medium text-gray-700 dark:text-gray-300">{{ trans('ui.select_sensor_field') }}</Label>
                    <Select v-model="selectedField" :disabled="!selectedTime || !selectedSens || !selectedOp">
                        <SelectTrigger :data-size="11"
                                       class="w-full border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 focus:ring-2 transition duration-150 disabled:bg-gray-100 dark:disabled:bg-gray-600 disabled:cursor-not-allowed">
                            <SelectValue :placeholder="trans('ui.sensor_field')" />
                        </SelectTrigger>
                        <SelectContent class="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow-lg rounded-md">
                            <SelectGroup>
                                <SelectItem v-for="field in fields" :key="field.value" :value="field.value"
                                            class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ field.label }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <Alert variant="destructive" v-else>
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>{{ trans('ui.no_sensors_title') }}</AlertTitle>
                <AlertDescription>
                    {{ trans('ui.no_sensors_message') }}
                </AlertDescription>
            </Alert>
        </div>

        <div class="p-6">
            <Card v-if="safeChartData.length > 0" class="my-6">
                <CardContent class="p-4">
                    <!-- Line Chart -->
                    <div class="flex flex-col gap-y-1.5 p-6">
                        <h3 class="font-semibold leading-none tracking-tight">{{ trans('ui.line_chart') }}</h3>
                    </div>
                    <LineChart :data="safeChartData" index="time" :categories="reactiveCategories as (keyof ChartDataPoint)[]" :colors="['green']"
                        :key="chartKey" :y-formatter="(tick) => {
                            return typeof tick === 'number' ? `${tick}` : '';
                        }" :options="{
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                        unit: 'hour',
                                        parser: 'MMM d, HH:mm',
                                        displayFormats: {
                                            hour: 'MMM d, HH:mm'
                                        },
                                        tooltipFormat: 'MMM d, yyyy HH:mm'
                                    },
                                    title: {
                                        display: true,
                                        text: trans('ui.time')
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: trans('ui.value')
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true
                                }
                            }
                        }" />
                </CardContent>
            </Card>

            <CadastralGroupsMap
                :groups="cadastralGroups"
                :current-group-id="selectedGroupId"
                :show-sensors="props.showSensors"
                :show-weather="props.showWeather"
                @groupSelected="(group) => selectedGroupId = group.id"
            />
        </div>
    </AppLayout>
</template>
