<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Download, Users, Square, Layers, Edit3, Upload, Globe, User, Building2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { format, parseISO } from 'date-fns';
import { it, enUS } from 'date-fns/locale';

import {
    MapboxMap,
    MapboxNavigationControl,
} from '@studiometa/vue-mapbox-gl';

import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

import { usePermissions } from '@/composables/usePermissions';
import { CadastralGroup } from '@/types';
import { sqmToHa } from '@/utils/conversions';

const { can } = usePermissions();

const locale = computed(() => {
    return page.props.appLocale === 'it' ? it : enUS;
});

interface ExtendedCadastralGroup extends CadastralGroup {
    cultivars?: Array<{
        id: number;
        name: string;
        description?: string | null;
        cultivation_name?: string | null;
    }>;
    planting_schemes?: Array<{
        id: number;
        name: string;
        description?: string | null;
        distance?: string | null;
    }>;
    plant_diseases?: Array<{
        id: number;
        name: string;
        description?: string | null;
    }>;
    irrigations?: Array<{
        id: number;
        type: string;
        description?: string | null;
    }>;
}

const props = defineProps<{
    cadastralGroup: ExtendedCadastralGroup;
    isAdmin: boolean;
}>();

const breadcrumbs = [
    {
        title: trans('ui.cadastral_groups'),
        href: route('cadastral-groups.index'),
    },
    {
        title: props.cadastralGroup.name,
        href: route('cadastral-groups.show', props.cadastralGroup.id),
    },
];

const page = usePage();
const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);
const mapboxMap = ref();
const map = computed(() => mapboxMap.value?.map);
const mapCenter = ref([12.50, 42.50]);
const selectedUnitId = ref<number | null>(null);

const downloadGeojson = () => {
    window.open(route('cadastral-groups.export-geojson', props.cadastralGroup.id), '_blank');
};

const getCreationMethodIcon = (method: string) => {
    switch (method) {
        case 'units': return Layers;
        case 'manual': return Edit3;
        case 'import': return Upload;
        default: return Layers;
    }
};

const getCreationMethodLabel = (method: string) => {
    switch (method) {
        case 'units': return trans('ui.from_cadastral_units');
        case 'manual': return trans('ui.drawn_manually');
        case 'import': return trans('ui.imported_from_geojson');
        default: return method;
    }
};

const drawGroupOnMap = () => {
    if (!map.value) return;

    // Remove existing layers and sources
    ['group-fill', 'group-outline', 'units-fill', 'units-outline'].forEach(layer => {
        if (map.value.getLayer(layer)) {
            map.value.removeLayer(layer);
        }
    });
    ['group', 'units'].forEach(source => {
        if (map.value.getSource(source)) {
            map.value.removeSource(source);
        }
    });

    // Draw group boundary if exists
    if (props.cadastralGroup.boundary_geometry_json) {
        map.value.addSource('group', {
            type: 'geojson',
            data: {
                type: 'Feature',
                properties: {
                    name: props.cadastralGroup.name,
                    total_area: props.cadastralGroup.total_area,
                    units_count: props.cadastralGroup.units_count,
                    creation_method: props.cadastralGroup.creation_method
                },
                geometry: JSON.parse(props.cadastralGroup.boundary_geometry_json)
            }
        });

        // Group fill
        map.value.addLayer({
            id: 'group-fill',
            type: 'fill',
            source: 'group',
            paint: {
                'fill-color': props.cadastralGroup.color,
                'fill-opacity': 0.4
            }
        });

        // Group outline
        map.value.addLayer({
            id: 'group-outline',
            type: 'line',
            source: 'group',
            paint: {
                'line-color': props.cadastralGroup.color,
                'line-width': 3
            }
        });
    }

    // Only draw individual units if creation_method is 'units'
    if (props.cadastralGroup.creation_method === 'units' && props.cadastralGroup.cadastral_units.length > 0) {
        const unitFeatures = props.cadastralGroup.cadastral_units
            .filter(unit => unit.geometry_json)
            .map(unit => ({
                type: 'Feature',
                properties: {
                    id: unit.id,
                    sheet: unit.sheet,
                    parcel: unit.parcel,
                    area: unit.cadastral_area,
                    city: unit.city.name,
                    province: unit.city.province,
                    region: unit.city.region
                },
                geometry: JSON.parse(unit.geometry_json!)
            }));

        if (unitFeatures.length > 0) {
            map.value.addSource('units', {
                type: 'geojson',
                data: {
                    type: 'FeatureCollection',
                    features: unitFeatures
                }
            });

            // Units fill (subtle)
            map.value.addLayer({
                id: 'units-fill',
                type: 'fill',
                source: 'units',
                paint: {
                    'fill-color': props.cadastralGroup.color,
                    'fill-opacity': [
                        'case',
                        ['==', ['get', 'id'], selectedUnitId.value],
                        0.8,
                        0.2
                    ]
                }
            });

            // Units outline
            map.value.addLayer({
                id: 'units-outline',
                type: 'line',
                source: 'units',
                paint: {
                    'line-color': '#475569',
                    'line-width': [
                        'case',
                        ['==', ['get', 'id'], selectedUnitId.value],
                        2,
                        1
                    ],
                    'line-opacity': 0.8
                }
            });

            // Add click handler for units
            map.value.on('click', 'units-fill', (e: any) => {
                if (e.features[0]) {
                    const properties = e.features[0].properties;
                    selectedUnitId.value = properties.id;

                    // Update paint properties to highlight selected unit
                    map.value.setPaintProperty('units-fill', 'fill-opacity', [
                        'case',
                        ['==', ['get', 'id'], selectedUnitId.value],
                        0.8,
                        0.2
                    ]);
                    map.value.setPaintProperty('units-outline', 'line-width', [
                        'case',
                        ['==', ['get', 'id'], selectedUnitId.value],
                        2,
                        1
                    ]);

                    // Show popup
                    new mapboxgl.Popup()
                        .setLngLat(e.lngLat)
                        .setHTML(`
                            <div class="p-3">
                                <h4 class="font-semibold text-gray-900 mb-2">${properties.city}</h4>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p><span class="font-medium">${trans('ui.sheet')}:</span> ${properties.sheet}</p>
                                    <p><span class="font-medium">${trans('ui.parcel')}:</span> ${properties.parcel}</p>
                                    <p><span class="font-medium">${trans('ui.area')}:</span> ${properties.area ? sqmToHa(properties.area) : 'N/A'} ha</p>
                                    <p class="pt-1 border-t">${properties.province}, ${properties.region}</p>
                                </div>
                            </div>
                        `)
                        .addTo(map.value);
                }
            });

            // Change cursor on hover
            map.value.on('mouseenter', 'units-fill', () => {
                map.value.getCanvas().style.cursor = 'pointer';
            });

            map.value.on('mouseleave', 'units-fill', () => {
                map.value.getCanvas().style.cursor = '';
            });
        }
    }

    // Add click handler for group area (not on units)
    map.value.on('click', 'group-fill', (e: any) => {
        // Check if click is not on a unit (only relevant for unit-based groups)
        if (props.cadastralGroup.creation_method === 'units') {
            const features = map.value.queryRenderedFeatures(e.point, { layers: ['units-fill'] });
            if (features.length > 0) return;
        }

        // Show group info popup
        new mapboxgl.Popup()
            .setLngLat(e.lngLat)
            .setHTML(`
                <div class="p-3">
                    <h4 class="font-semibold text-gray-900 mb-2">${props.cadastralGroup.name}</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><span class="font-medium">${trans('ui.total_area')}:</span> ${sqmToHa(props.cadastralGroup.total_area)} ha</p>
                        <p><span class="font-medium">${trans('ui.creation_method')}:</span> ${getCreationMethodLabel(props.cadastralGroup.creation_method)}</p>
                        ${props.cadastralGroup.creation_method === 'units' ? `<p><span class="font-medium">${trans('ui.units_count')}:</span> ${props.cadastralGroup.units_count}</p>` : ''}
                    </div>
                </div>
            `)
            .addTo(map.value);
    });

    // Fit bounds to show the entire group
    if (props.cadastralGroup.boundary_geometry_json) {
        const bounds = new mapboxgl.LngLatBounds();
        const geometry = JSON.parse(props.cadastralGroup.boundary_geometry_json);

        if (geometry.type === 'MultiPolygon') {
            geometry.coordinates.forEach((polygon: any) => {
                polygon[0].forEach((coord: [number, number]) => {
                    bounds.extend(coord);
                });
            });
        } else if (geometry.type === 'Polygon') {
            geometry.coordinates[0].forEach((coord: [number, number]) => {
                bounds.extend(coord);
            });
        }

        map.value.fitBounds(bounds, { padding: 100 });
    } else if (props.cadastralGroup.centroid_json) {
        // Center on group centroid if no boundary
        const centroid = JSON.parse(props.cadastralGroup.centroid_json);
        mapCenter.value = centroid.coordinates;
        map.value.flyTo({
            center: centroid.coordinates,
            zoom: 14,
            essential: true
        });
    }
};

// Format area for display
const formatArea = (area: number) => {
    return `${sqmToHa(area)} ha`;
};

onMounted(() => {
    nextTick(() => {
        if (map.value) {
            map.value.on('load', drawGroupOnMap);
        }
    });
});
</script>

<template>

    <Head :title="cadastralGroup.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-3">
                        {{ cadastralGroup.name }}
                        <div class="w-6 h-6 rounded border border-gray-300"
                            :style="{ backgroundColor: cadastralGroup.color }">
                        </div>
                    </h1>
                    <p v-if="cadastralGroup.description" class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ cadastralGroup.description }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <Button variant="outline" @click="downloadGeojson">
                        <Download class="mr-2 h-4 w-4" />
                        {{ trans('ui.export_geojson') }}
                    </Button>
                    <Button v-if="can.update_cadastral_group"
                        @click="router.visit(route('cadastral-groups.edit', cadastralGroup.id))">
                        <Edit class="mr-2 h-4 w-4" />
                        {{ trans('ui.edit_group') }}
                    </Button>
                    <Button variant="outline" @click="router.visit(route('cadastral-groups.index'))">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        {{ trans('ui.back_to_list') }}
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Group Statistics -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ trans('ui.group_statistics') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <!-- Owner Information -->
                                <div class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <User class="h-4 w-4" />
                                        <span>{{ trans('ui.owner') || 'Owner' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold">{{ cadastralGroup.user?.full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ cadastralGroup.user?.email }}</p>
                                    </div>
                                </div>

                                <!-- Company -->
                                <div v-if="cadastralGroup.company"
                                    class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <Building2 class="h-4 w-4" />
                                        <span>{{ trans('ui.company') }}</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold">{{ cadastralGroup.company.name }}</p>
                                        <p v-if="cadastralGroup.company.description" class="text-sm text-gray-500">
                                            {{ cadastralGroup.company.description }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <Square class="h-4 w-4" />
                                        <span>{{ trans('ui.total_area') }}</span>
                                    </div>
                                    <span class="font-semibold text-lg">
                                        {{ formatArea(cadastralGroup.total_area) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <component :is="getCreationMethodIcon(cadastralGroup.creation_method)"
                                            class="h-4 w-4" />
                                        <span>{{ trans('ui.creation_method') }}</span>
                                    </div>
                                    <Badge variant="outline" class="capitalize">
                                        {{ getCreationMethodLabel(cadastralGroup.creation_method) }}
                                    </Badge>
                                </div>

                                <div v-if="cadastralGroup.creation_method === 'units'"
                                    class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <Users class="h-4 w-4" />
                                        <span>{{ trans('ui.cadastral_units') }}</span>
                                    </div>
                                    <span class="font-semibold text-lg">
                                        {{ cadastralGroup.units_count }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <Globe class="h-4 w-4" />
                                        <span>{{ trans('ui.centroid_latitude') }}</span>
                                    </div>
                                    <span class="font-semibold text-lg">
                                        {{ cadastralGroup.latitude?.toFixed(8) }}°
                                    </span>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <Globe class="h-4 w-4" />
                                        <span>{{ trans('ui.centroid_longitude') }}</span>
                                    </div>
                                    <span class="font-semibold text-lg">
                                        {{ cadastralGroup.longitude?.toFixed(8) }}°
                                    </span>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b">
                                    <span class="text-gray-600">{{ trans('ui.created_at') }}</span>
                                    <span class="text-sm">
                                        {{ format(parseISO(cadastralGroup.created_at), 'PPP', { locale: locale }) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between py-2">
                                    <span class="text-gray-600">{{ trans('ui.last_updated') }}</span>
                                    <span class="text-sm">
                                        {{ format(parseISO(cadastralGroup.updated_at), 'PPP', { locale: locale }) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Units List - Only show if creation_method is 'units' -->
                    <Card
                        v-if="cadastralGroup.creation_method === 'units' && cadastralGroup.cadastral_units.length > 0">
                        <CardHeader>
                            <CardTitle>{{ trans('ui.units_in_group') }}</CardTitle>
                            <CardDescription>
                                {{ trans('ui.click_unit_on_map') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <div v-for="unit in cadastralGroup.cadastral_units" :key="unit.id"
                                    class="p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer"
                                    :class="{ 'bg-blue-50 dark:bg-blue-900/20 border-blue-400': selectedUnitId === unit.id }"
                                    @click="selectedUnitId = unit.id">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">
                                                {{ trans('ui.sheet') }}: {{ unit.sheet }},
                                                {{ trans('ui.parcel') }}: {{ unit.parcel }}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ unit.city.name }}
                                            </p>
                                        </div>
                                        <Badge variant="outline">
                                            {{ sqmToHa(unit.cadastral_area) || 0 }} ha
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Info card for non-unit based groups -->
                    <Card v-else-if="cadastralGroup.creation_method !== 'units'">
                        <CardHeader>
                            <CardTitle>{{ trans('ui.geometry_info') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-sm text-gray-600 space-y-2">
                                <p v-if="cadastralGroup.creation_method === 'manual'">
                                    {{ trans('ui.group_geometry_manually_drawn') }}
                                </p>
                                <p v-else-if="cadastralGroup.creation_method === 'import'">
                                    {{ trans('ui.group_geometry_imported') }}
                                </p>
                                <p class="mt-4">
                                    {{ trans('ui.no_cadastral_units_associated') }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Cultivars Card -->
                    <Card v-if="cadastralGroup.cultivars && cadastralGroup.cultivars.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                {{ trans('ui.field_cultivars') || 'Field Cultivars' }}
                            </CardTitle>
                            <CardDescription>
                                {{ trans('ui.cultivars_in_field') || 'Cultivars grown in this field' }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="cultivar in cadastralGroup.cultivars" :key="cultivar.id"
                                    class="p-3 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-semibold">{{ cultivar.name }}</h4>
                                                <Badge v-if="cultivar.cultivation_name" variant="outline"
                                                    class="text-xs">
                                                    {{ cultivar.cultivation_name }}
                                                </Badge>
                                            </div>

                                            <p v-if="cultivar.description" class="text-sm text-gray-500 mt-1">
                                                {{ cultivar.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Empty Cultivars Card -->
                    <Card v-else>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                {{ trans('ui.field_cultivars') || 'Field Cultivars' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center py-6">
                                <p class="text-gray-500">
                                    {{ trans('ui.no_cultivars_assigned') || 'No cultivars assigned to this field' }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Irrigations -->
                    <Card v-if="cadastralGroup.irrigations && cadastralGroup.irrigations.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                {{ trans('ui.irrigations') }}
                            </CardTitle>
                            <CardDescription>
                                {{ trans('ui.irrigations_in_field') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="irrigation in cadastralGroup.irrigations" :key="irrigation.id"
                                    class="p-3 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-semibold">{{ irrigation.type }}</h4>
                                            <p v-if="irrigation.description" class="text-sm text-gray-500 mt-1">
                                                {{ irrigation.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Map -->
                <div class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ trans('ui.group_map') }}</CardTitle>
                            <CardDescription v-if="cadastralGroup.creation_method === 'units'">
                                {{ trans('ui.click_units_for_details') }}
                            </CardDescription>
                            <CardDescription v-else>
                                {{ trans('ui.group_boundary_display') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="relative h-[600px] rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <MapboxMap :access-token="mapboxAccessToken" ref="mapboxMap" class="h-full w-full"
                                    :center="mapCenter" map-style="mapbox://styles/mapbox/satellite-streets-v12"
                                    :zoom="14">
                                    <MapboxNavigationControl position="bottom-right" />
                                </MapboxMap>
                            </div>

                            <!-- Map Legend -->
                            <div class="mt-4 flex items-center gap-6 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 border-2" :style="{
                                        backgroundColor: cadastralGroup.color + '66',
                                        borderColor: cadastralGroup.color
                                    }">
                                    </div>
                                    <span>{{ trans('ui.group_boundary') }}</span>
                                </div>
                                <div v-if="cadastralGroup.creation_method === 'units'" class="flex items-center gap-2">
                                    <div class="w-4 h-4 border border-gray-600"
                                        :style="{ backgroundColor: cadastralGroup.color + '33' }">
                                    </div>
                                    <span>{{ trans('ui.cadastral_units') }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Planting Schemes -->
                    <Card v-if="cadastralGroup.planting_schemes && cadastralGroup.planting_schemes.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                {{ trans('ui.planting_schemes') }}
                            </CardTitle>
                            <CardDescription>
                                {{ trans('ui.planting_schemes_in_field') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="scheme in cadastralGroup.planting_schemes" :key="scheme.id"
                                    class="p-3 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-semibold">{{ scheme.name }}</h4>
                                                <Badge v-if="scheme.distance" variant="outline" class="text-xs">
                                                    {{ scheme.distance }}
                                                </Badge>
                                            </div>

                                            <p v-if="scheme.description" class="text-sm text-gray-500 mt-1">
                                                {{ scheme.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Plant Diseases -->
                    <Card v-if="cadastralGroup.plant_diseases && cadastralGroup.plant_diseases.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                {{ trans('ui.plant_diseases') }}
                            </CardTitle>
                            <CardDescription>
                                {{ trans('ui.plant_diseases_in_field') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="disease in cadastralGroup.plant_diseases" :key="disease.id"
                                    class="p-3 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-semibold">{{ disease.name }}</h4>
                                            <p v-if="disease.description" class="text-sm text-gray-500 mt-1">
                                                {{ disease.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
