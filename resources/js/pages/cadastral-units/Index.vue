<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { columns } from './table/columns'
import DataTable from '@/components/DataTable/DataTable.vue'
import { Button } from '@/components/ui/button';
import { FilePlus2, MapPin } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';

import {
    MapboxMap,
    MapboxNavigationControl,
    MapboxMarker,
    MapboxPopup
} from '@studiometa/vue-mapbox-gl';

import mapboxgl from 'mapbox-gl';
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import StaticMode from '@mapbox/mapbox-gl-draw-static-mode';

import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import 'mapbox-gl/dist/mapbox-gl.css';
import { sqmToHa } from '@/utils/conversions';

const { can } = usePermissions();

const breadcrumbs = [
    {
        title: trans('ui.land_registry'),
        href: route('cadastral-units.index'),
    },
];

const props = defineProps<{
    cadastralUnits: {
        data: {
            id: number;
            sheet: string;
            parcel: string;
            cadastral_area: number;
            city: {
                id: number; name: string; cadastral_code: string;
                region: {
                    id: number; name: string; code: string
                };
                province: { id: number; name: string; code: string }
            };
            geometry_json: string;
            centroid_json: string;
            user: {
                id: number;
                email: string;
                first_name: string;
                last_name: string;
            } | null;
        }[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    allCadastralUnitsForMap: {
        id: number;
        sheet: string;
        parcel: string;
        cadastral_area: number;
        city: {
            name: string;
            province: string;
            province_code: string;
            region: string;
        };
        geometry_json: string | null;
        centroid_json: string | null;
        user: {
            id: number;
            email: string;
            first_name: string;
            last_name: string;
        } | null;
    }[];
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

const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);
const mapboxMap = ref();
const map = computed(() => mapboxMap.value?.map);
const mapCenter = ref([12.50, 42.50]);

// Mapbox Draw
const draw = ref<MapboxDraw | null>(null);
const currentPolygonId = ref<string | null>(null);

// Active/selected unit
const activeUnitId = ref<number | null>(null);

interface MarkerData {
    id: number;
    coordinates: [number, number];
    popupContent: {
        sheet: string;
        parcel: string;
        cadastral_area: number;
        city: string;
        province: string;
        province_code: string;
        region: string;
    };
}

const markers = ref<MarkerData[]>([]);
const isLoading = ref(false);

const computeMarkers = async () => {
    const markersList: MarkerData[] = [];

    for (const unit of props.allCadastralUnitsForMap) {
        let coordinates: [number, number] | null = null;

        // get coordinates from centroid
        if (unit.centroid_json) {
            try {
                const centroid = JSON.parse(unit.centroid_json);
                coordinates = centroid.coordinates as [number, number];
            } catch (e) {
                console.error('error parsing centroid:', e);
            }
        }

        // if no centroid, geocode the city
        if (!coordinates && unit.city) {
            const locationString = `${unit.city.name}, ${unit.city.province}, Italia`;
            try {
                const response = await fetch(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(locationString)}.json?access_token=${mapboxAccessToken.value}&limit=1`
                );
                const data = await response.json();

                if (data.features && data.features.length > 0) {
                    coordinates = data.features[0].center as [number, number];
                }
            } catch (error) {
                console.error('error geocoding location:', error);
            }
        }

        if (coordinates) {
            markersList.push({
                id: unit.id,
                coordinates,
                popupContent: {
                    sheet: unit.sheet,
                    parcel: unit.parcel,
                    cadastral_area: unit.cadastral_area,
                    city: unit.city.name,
                    province: unit.city.province,
                    province_code: unit.city.province_code,
                    region: unit.city.region,
                }
            });
        }
    }

    markers.value = markersList;

    // Fit map to show markers if
    if (markers.value.length > 0 && map.value) {
        const bounds = markers.value.reduce((bounds, marker) => {
            return bounds.extend(marker.coordinates);
        }, new mapboxgl.LngLatBounds(markers.value[0].coordinates, markers.value[0].coordinates));

        map.value.fitBounds(bounds, { padding: 200 });
    }
};

const initializeDrawing = () => {
    if (!map.value) return;

    const modes = MapboxDraw.modes;
    modes.static = StaticMode;

    draw.value = new MapboxDraw({
        displayControlsDefault: false,
        defaultMode: 'simple_select',
        modes: modes
    });

    map.value.addControl(draw.value);
    draw.value.changeMode('static');
};

const centerMapViaLocationName = async (locationString: string) => {
    try {
        isLoading.value = true;

        const response = await fetch(
            `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(locationString)}.json?access_token=${mapboxAccessToken.value}&limit=1`
        );

        const data = await response.json();

        if (data.features && data.features.length > 0) {
            const [lng, lat] = data.features[0].center;

            if (map.value) {
                map.value.flyTo({
                    center: [lng, lat],
                    zoom: 15,
                    essential: true,
                    duration: 2000
                });
            }
        } else {
            console.error('Location not found');
        }
    } catch (error) {
        console.error('Error geocoding location:', error);
    } finally {
        isLoading.value = false;
    }
};

const handleMarkerClick = (markerId: number) => {
    activeUnitId.value = markerId;

    const unit = props.allCadastralUnitsForMap.find(u => u.id === markerId);
    if (unit && unit.geometry_json && draw.value) {
        drawPolygon(JSON.parse(unit.geometry_json));
    }
};

const drawPolygon = (polygonGeoJson: any) => {
    if (!draw.value) return;

    const feature = {
        type: "Feature",
        geometry: polygonGeoJson,
        properties: {},
    };

    // delete previous polygon
    if (currentPolygonId.value) {
        draw.value.delete(currentPolygonId.value);
    }

    const featureIds = draw.value.add(feature);

    if (featureIds && featureIds.length > 0) {
        currentPolygonId.value = featureIds[0];
    }
};

const handleCenterMapEvent = (event: Event) => {
    const customEvent = event as CustomEvent<{
        id: number;
        geometry_json?: string;
        centroid_json?: string;
        city: {
            name: string;
            province: { name: string };
            region: { name: string };
        };
    }>;

    const cadastralUnit = customEvent.detail;
    const marker = markers.value.find(m => m.id === cadastralUnit.id);

    activeUnitId.value = cadastralUnit.id;

    if (marker) {
        // center on existing marker
        if (map.value) {
            map.value.flyTo({
                center: marker.coordinates,
                zoom: 16,
                essential: true,
                duration: 2000
            });
        }

        if (cadastralUnit.geometry_json) {
            drawPolygon(JSON.parse(cadastralUnit.geometry_json));
        }
    } else if (cadastralUnit?.centroid_json) {
        // center on centroid if no marker found
        const coordinates = JSON.parse(cadastralUnit.centroid_json).coordinates as [number, number];
        if (map.value) {
            map.value.flyTo({
                center: coordinates,
                zoom: 16,
                essential: true,
                duration: 2000
            });
        }

        if (cadastralUnit.geometry_json) {
            drawPolygon(JSON.parse(cadastralUnit.geometry_json));
        }
    } else if (cadastralUnit?.city) {
        // Fallback to city center
        const locationString = `${cadastralUnit.city.name}, ${cadastralUnit.city.province.name}, Italia`;
        centerMapViaLocationName(locationString);
    }
};

onMounted(() => {
    window.addEventListener('center-map-location', handleCenterMapEvent);

    nextTick(async () => {
        if (map.value) {
            initializeDrawing();
            isLoading.value = true;
            await computeMarkers();
            isLoading.value = false;
        }
    });
});

onUnmounted(() => {
    window.removeEventListener('center-map-location', handleCenterMapEvent);
});
</script>

<template>

    <Head :title="trans('ui.land_registry')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.land_registry') }}
                </h1>

                <Button v-if="can.create_cadastral_unit" @click="router.visit(route('cadastral-units.create'))">
                    <FilePlus2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.create_new') }}
                </Button>
            </div>

            <DataTable :route="route('cadastral-units.index')" :columns="columns" :data="props.cadastralUnits.data"
                :pagination="{
                    pageIndex: props.cadastralUnits.current_page - 1,
                    pageSize: props.cadastralUnits.per_page,
                    pageCount: props.cadastralUnits.last_page,
                    total: props.cadastralUnits.total,
                    from: props.cadastralUnits.from,
                    to: props.cadastralUnits.to,
                }" :sorting="sorting" :filtering="filtering"
                :search-placeholder="trans('ui.search_for_land_placeholder')" :striped="true" />

            <!-- Map -->
            <div
                class="relative h-[31.25rem] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border mt-6">

                <!-- Loading overlay -->
                <div v-if="isLoading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/50 dark:bg-gray-900/50 rounded-xl">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.loading') }}</p>
                    </div>
                </div>

                <MapboxMap :access-token="mapboxAccessToken" ref="mapboxMap" class="h-full w-full rounded-xl"
                    :center="mapCenter" map-style="mapbox://styles/mapbox/satellite-v9" :zoom="5">

                    <MapboxNavigationControl position="bottom-right" />

                    <!-- Render all markers -->
                    <template v-for="marker in markers" :key="marker.id">
                        <MapboxMarker :lng-lat="marker.coordinates">
                            <!-- Custom marker element -->
                            <div class="relative cursor-pointer" @click="handleMarkerClick(marker.id)">
                                <div class="flex items-center justify-center">
                                    <MapPin :fill="activeUnitId === marker.id ? 'red' : 'blue'" :class="[
                                        'h-6 w-6 text-white transition-all',
                                        activeUnitId === marker.id ? 'scale-125' : ''
                                    ]" />
                                </div>
                            </div>
                        </MapboxMarker>

                        <MapboxPopup v-if="activeUnitId === marker.id" :lng-lat="marker.coordinates" :offset="[0, -40]"
                            :close-button="true" :close-on-click="false" @close="activeUnitId = null">
                            <div class="p-2">
                                <h3 class="font-semibold text-gray-900 mb-1">{{ marker.popupContent.city }}
                                    ({{ marker.popupContent.province_code }})</h3>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p>
                                        <span class="font-medium">{{ trans('ui.sheet') }}:</span>
                                        {{ marker.popupContent.sheet }}
                                    </p>

                                    <p>
                                        <span class="font-medium">{{ trans('ui.parcel') }}:</span>
                                        {{ marker.popupContent.parcel }}
                                    </p>

                                    <p>
                                        <span class="font-medium">{{ trans('ui.area') }}:</span>
                                        {{ sqmToHa(marker.popupContent.cadastral_area) }}
                                        ha
                                    </p>

                                    <p class="pt-1 border-t">{{ marker.popupContent.region }}</p>
                                </div>
                            </div>
                        </MapboxPopup>
                    </template>
                </MapboxMap>
            </div>
        </div>
    </AppLayout>
</template>
