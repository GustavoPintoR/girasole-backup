<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { columns } from './Table/columns';
import DataTable from '@/components/DataTable/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from "@/components/ui/dialog"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage, } from "@/components/ui/form"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, } from "@/components/ui/select"
import { FilePlus2, MapPin, RefreshCw } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { MarkerData, Sensor } from '@/types';

// Mapbox components
import {
    MapboxMap,
    MapboxNavigationControl,
    MapboxMarker,
    MapboxPopup
} from '@studiometa/vue-mapbox-gl';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import { toTypedSchema } from '@vee-validate/zod';
import { z } from 'zod';

const props = defineProps<{
    sensors: {
        data: Sensor[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    sorting: {
        sortBy: string | null;
        sortOrder: 'asc' | 'desc' | null;
    };
    filtering: {
        search?: string;
        searchableColumns: string[];
    };
}>();

const page = usePage();
const { can } = usePermissions();

const breadcrumbs = [
    {
        title: trans('ui.sensors'),
        href: route('sensors.index'),
    },
];

// Mapbox state
const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);
const mapboxMap = ref();
const map = computed(() => mapboxMap.value?.map);
const mapCenter = ref([12.50, 42.50]);

const syncDialogOpen = ref(false);
const activeSensorId = ref<number | null>(null);
const isLoading = ref(false);
const isSyncing = ref(false);

const markers = ref<MarkerData[]>([]);

const computeMarkers = async () => {
    isLoading.value = true;
    const markersList: MarkerData[] = [];

    for (const sensor of props.sensors.data) {
        if (sensor.latitude && sensor.longitude) {
            markersList.push({
                id: sensor.id,
                coordinates: [sensor.longitude as any, sensor.latitude as any] as [number, number],
                popupContent: {
                    name: sensor.name,
                    type: sensor.type,
                    serial: sensor.serial_number as string,
                    description: sensor.description,
                    latitude: sensor.latitude as any as number,
                    longitude: sensor.longitude as any as number,
                }
            });
        }
    }

    markers.value = markersList;

    // Fit map to show all markers
    if (markers.value.length > 0 && map.value) {
        const bounds = markers.value.reduce((bounds, marker) => {
            return bounds.extend(marker.coordinates);
        }, new mapboxgl.LngLatBounds(markers.value[0].coordinates, markers.value[0].coordinates));

        map.value.fitBounds(bounds, { padding: 50 });
    }
    isLoading.value = false;
};

const handleMarkerClick = (markerId: number) => {
    activeSensorId.value = markerId;
};

const handleCenterMapEvent = (event: Event) => {
    const customEvent = event as CustomEvent<Sensor>;

    const sensor = customEvent.detail;
    const marker = markers.value.find(m => m.id === sensor.id);

    activeSensorId.value = sensor.id;

    if (marker) {
        // center on existing marker
        if (map.value) {
            map.value.flyTo({
                center: marker.coordinates,
                zoom: 18,
                essential: true,
                duration: 2000
            });
        }
    }
};

const formSchema = toTypedSchema(z.object({
    range: z.string().nonempty(trans('ui.select_range')),
}))

const timeRanges = [
    { value: '-7d', label: trans('ui.range_days', {range: '7'}) },
    { value: '-15d', label: trans('ui.range_days', {range: '12'})},
    { value: '-30d', label: trans('ui.range_days', {range: '30'}) },
]

const onSubmit = (values: { range: string }) => {
    isSyncing.value = true;
    router.post(route('sensors.sync'), { range: values.range }, {
        preserveScroll: true,
        onSuccess: () => {
            syncDialogOpen.value = false;
            isSyncing.value = false;
        },
        onError: () => {
            isSyncing.value = false;
        },
    });
};

const confirmSync = () => {
    syncDialogOpen.value = true;
}

onMounted(() => {
    window.addEventListener('center-map-location', handleCenterMapEvent);

    nextTick(async () => {
        if (map.value) {
            await computeMarkers();
        }
    });
});

onUnmounted(() => {
    window.removeEventListener('center-map-location', handleCenterMapEvent);
});
</script>

<template>
    <Head :title="trans('ui.sensors')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.sensors') }}
                </h1>
                <div>
                    <Dialog v-if="can.create_sensor" v-model:open="syncDialogOpen">
                        <DialogTrigger as-child>
                            <Button class="mr-2" @click="confirmSync()">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                {{ trans('ui.sync_sensors') }}
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[464px]">
                            <DialogHeader>
                                <DialogTitle>{{ trans('ui.sync_sensors') }}</DialogTitle>
                                <DialogDescription>
                                    {{ trans('ui.select_time_range_for_sync') }}
                                </DialogDescription>
                            </DialogHeader>

                            <Form v-slot="{ handleSubmit }" as="" keep-values :validation-schema="formSchema">
                                <form id="syncForm" @submit="handleSubmit($event, onSubmit)">
                                    <FormField v-slot="{ componentField }" name="range">
                                        <FormItem>
                                            <FormLabel>{{ trans('ui.time_range') }}</FormLabel>
                                            <Select v-bind="componentField">
                                                <FormControl>
                                                    <SelectTrigger class="h-11 w-[413px]" :data-size="11">
                                                        <SelectValue :placeholder="trans('ui.select_range_for_sync')" />
                                                    </SelectTrigger>
                                                </FormControl>
                                                <SelectContent>
                                                    <SelectItem v-for="range in timeRanges" :key="range.value" :value="range.value">
                                                        {{ range.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </form>

                                <DialogFooter>
                                    <Button type="submit" form="syncForm" :disabled="isSyncing">
                                        {{ isSyncing ? trans('ui.syncing') : trans('ui.continue') }}
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                    <Button v-if="can.create_sensor" @click="router.visit(route('sensors.create'))">
                        <FilePlus2 class="mr-2 h-4 w-4" />
                        {{ trans('ui.create_new') }}
                    </Button>
                </div>

            </div>


            <DataTable
                :route="route('sensors.index')"
                :columns="columns"
                :data="sensors.data"
                :pagination="{
                    pageIndex: sensors.current_page - 1,
                    pageSize: sensors.per_page,
                    pageCount: sensors.last_page,
                    total: sensors.total,
                    from: sensors.from,
                    to: sensors.to
                }"
                :sorting="sorting"
                :filtering="filtering"
                :search-placeholder="trans('ui.search')"
            />

            <div class="relative h-[31.25rem] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border mt-6">
                <div v-if="isLoading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/50 dark:bg-gray-900/50 rounded-xl">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.loading') }}</p>
                    </div>
                </div>

                <MapboxMap :access-token="mapboxAccessToken" ref="mapboxMap" class="h-full w-full rounded-xl"
                    :center="mapCenter" map-style="mapbox://styles/mapbox/satellite-streets-v12" :zoom="5">

                    <MapboxNavigationControl position="bottom-right" />

                    <template v-for="marker in markers" :key="marker.id">
                        <MapboxMarker :lng-lat="marker.coordinates">
                            <div class="relative cursor-pointer" @click="handleMarkerClick(marker.id)">
                                <div class="flex items-center justify-center">
                                    <MapPin :fill="activeSensorId === marker.id ? 'red' : 'blue'" :class="[
                                        'h-6 w-6 text-white transition-all',
                                        activeSensorId === marker.id ? 'scale-125' : ''
                                    ]" />
                                </div>
                            </div>
                        </MapboxMarker>

                        <MapboxPopup v-if="activeSensorId === marker.id" :lng-lat="marker.coordinates" :offset="[0, -40]"
                            :close-button="true" :close-on-click="false" @close="activeSensorId = null">
                            <div class="p-2">
                                <h3 class="font-semibold text-gray-900 mb-1">{{ marker.popupContent.type }}</h3>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p>
                                        <span class="font-medium">{{ trans('ui.name') }}:</span> {{ marker.popupContent.name }}
                                    </p>
                                    <p v-if="marker.popupContent.serial">
                                        <span class="font-medium">{{ trans('ui.serial') }}:</span> {{ marker.popupContent.serial }}
                                    </p>
                                    <p v-if="marker.popupContent.description">
                                        <span class="font-medium">{{ trans('ui.description') }}:</span> {{ marker.popupContent.description }}
                                    </p>
                                     <p>
                                        <span class="font-medium">{{ trans('ui.latitude') }}:</span> {{ marker.popupContent.latitude }}
                                    </p>
                                     <p>
                                        <span class="font-medium">{{ trans('ui.longitude') }}:</span> {{ marker.popupContent.longitude }}
                                    </p>
                                </div>
                            </div>
                        </MapboxPopup>
                    </template>
                </MapboxMap>
            </div>
        </div>
    </AppLayout>
</template>
