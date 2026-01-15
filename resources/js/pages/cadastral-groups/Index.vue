<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DataTable from '@/components/DataTable/DataTable.vue';
import { Button } from '@/components/ui/button';
import { FilePlus2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { columns } from './table/columns'

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

const breadcrumbs = [
    {
        title: trans('ui.cadastral_groups'),
        href: route('cadastral-groups.index'),
    },
];

interface GroupForMap {
    id: number;
    name: string;
    total_area: number;
    units_count: number;
    creation_method: string;
    color: string;
    boundary_geometry_json: string | null;
    centroid_json: string | null;
}

const props = defineProps<{
    cadastralGroups: {
        data: CadastralGroup[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    allGroupsForMap: GroupForMap[];
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
const activeGroupId = ref<number | null>(null);
const hoveredGroupId = ref<number | null>(null);

const drawGroupsOnMap = () => {
    if (!map.value) return;

    // Remove existing layers
    if (map.value.getLayer('groups-fill')) {
        map.value.removeLayer('groups-fill');
    }
    if (map.value.getLayer('groups-outline')) {
        map.value.removeLayer('groups-outline');
    }
    if (map.value.getSource('groups')) {
        map.value.removeSource('groups');
    }

    // Create features from groups
    const features = props.allGroupsForMap
        .filter(group => group.boundary_geometry_json)
        .map(group => ({
            type: 'Feature',
            properties: {
                id: group.id,
                name: group.name,
                total_area: group.total_area,
                units_count: group.units_count,
                creation_method: group.creation_method,
                color: group.color
            },
            geometry: JSON.parse(group.boundary_geometry_json!)
        }));

    if (features.length === 0) return;

    // Add source
    map.value.addSource('groups', {
        type: 'geojson',
        data: {
            type: 'FeatureCollection',
            features
        }
    });

    // Add fill layer
    map.value.addLayer({
        id: 'groups-fill',
        type: 'fill',
        source: 'groups',
        paint: {
            'fill-color': [
                'case',
                ['==', ['get', 'id'], activeGroupId.value],
                ['get', 'color'],
                ['==', ['get', 'id'], hoveredGroupId.value],
                ['get', 'color'],
                ['get', 'color']
            ],
            'fill-opacity': [
                'case',
                ['==', ['get', 'id'], activeGroupId.value],
                0.7,
                ['==', ['get', 'id'], hoveredGroupId.value],
                0.5,
                0.3
            ]
        }
    });

    // Add outline layer
    map.value.addLayer({
        id: 'groups-outline',
        type: 'line',
        source: 'groups',
        paint: {
            'line-color': ['get', 'color'],
            'line-width': [
                'case',
                ['==', ['get', 'id'], activeGroupId.value],
                3,
                2
            ]
        }
    });

    // Add hover effect
    map.value.on('mouseenter', 'groups-fill', (e: any) => {
        map.value.getCanvas().style.cursor = 'pointer';
        if (e.features[0]) {
            hoveredGroupId.value = e.features[0].properties.id;
            updateMapPaint();
        }
    });

    map.value.on('mouseleave', 'groups-fill', () => {
        map.value.getCanvas().style.cursor = '';
        hoveredGroupId.value = null;
        updateMapPaint();
    });

    // Add click handler
    map.value.on('click', 'groups-fill', (e: any) => {
        if (e.features[0]) {
            activeGroupId.value = e.features[0].properties.id;
            updateMapPaint();

            // Show popup
            const coordinates = e.lngLat;
            const properties = e.features[0].properties;

            new mapboxgl.Popup()
                .setLngLat(coordinates)
                .setHTML(`
                    <div class="p-2">
                        <h3 class="font-semibold text-gray-900 mb-1">${properties.name}</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>
                                <span class="font-medium">${trans('ui.creation_method')}:</span>
                                ${properties.creation_method}
                            </p>
                            <p>
                                <span class="font-medium">${trans('ui.units_count')}:</span>
                                ${properties.units_count}
                            </p>
                            <p>
                                <span class="font-medium">${trans('ui.total_area')}:</span>
                                ${sqmToHa(properties.total_area)} ha
                            </p>
                        </div>
                    </div>
                `)
                .addTo(map.value);
        }
    });

    // Fit map to show all groups
    const bounds = new mapboxgl.LngLatBounds();
    features.forEach(feature => {
        if (feature.geometry.type === 'MultiPolygon') {
            feature.geometry.coordinates.forEach((polygon: any) => {
                polygon[0].forEach((coord: [number, number]) => {
                    bounds.extend(coord);
                });
            });
        } else if (feature.geometry.type === 'Polygon') {
            feature.geometry.coordinates[0].forEach((coord: [number, number]) => {
                bounds.extend(coord);
            });
        }
    });

    if (!bounds.isEmpty()) {
        map.value.fitBounds(bounds, { padding: 100 });
    }
};

const updateMapPaint = () => {
    if (!map.value || !map.value.getLayer('groups-fill')) return;

    map.value.setPaintProperty('groups-fill', 'fill-opacity', [
        'case',
        ['==', ['get', 'id'], activeGroupId.value],
        0.7,
        ['==', ['get', 'id'], hoveredGroupId.value],
        0.5,
        0.3
    ]);

    map.value.setPaintProperty('groups-outline', 'line-width', [
        'case',
        ['==', ['get', 'id'], activeGroupId.value],
        3,
        2
    ]);
};

const handleCenterMapEvent = (event: Event) => {
    const customEvent = event as CustomEvent<{
        id: number;
        boundary_geometry_json?: string;
        centroid_json?: string;
    }>;

    const cadastralGroup = customEvent.detail;
    const groupForMap = props.allGroupsForMap.find(g => g.id === cadastralGroup.id);

    activeGroupId.value = cadastralGroup.id;

    if (groupForMap?.boundary_geometry_json) {
        // Calculate bounds for the group
        const geometry = JSON.parse(groupForMap.boundary_geometry_json);
        const bounds = new mapboxgl.LngLatBounds();

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

        if (!bounds.isEmpty() && map.value) {
            map.value.fitBounds(bounds, {
                padding: 100,
                duration: 2000
            });
        }
    } else if (groupForMap?.centroid_json) {
        // Center on centroid if no boundary
        const centroid = JSON.parse(groupForMap.centroid_json);
        if (map.value) {
            map.value.flyTo({
                center: centroid.coordinates,
                zoom: 14,
                essential: true,
                duration: 2000
            });
        }
    }

    // Update map paint to highlight the group
    updateMapPaint();
};

onMounted(() => {
    window.addEventListener('center-map-on-group', handleCenterMapEvent);

    nextTick(() => {
        if (map.value) {
            map.value.on('load', drawGroupsOnMap);
        }
    });
});

onUnmounted(() => {
    window.removeEventListener('center-map-on-group', handleCenterMapEvent);
});
</script>

<template>

    <Head :title="trans('ui.cadastral_groups')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.cadastral_groups') }}
                </h1>

                <div class="flex gap-2" v-if="can.create_cadastral_group">
                    <Button @click="router.visit(route('cadastral-groups.create'))">
                        <FilePlus2 class="mr-2 h-4 w-4" />
                        {{ trans('ui.create_group') }}
                    </Button>
                </div>
            </div>

            <!-- Data Table -->
            <DataTable :route="route('cadastral-groups.index')" :columns="columns" :data="props.cadastralGroups.data"
                :pagination="{
                    pageIndex: props.cadastralGroups.current_page - 1,
                    pageSize: props.cadastralGroups.per_page,
                    pageCount: props.cadastralGroups.last_page,
                    total: props.cadastralGroups.total,
                    from: props.cadastralGroups.from,
                    to: props.cadastralGroups.to,
                }" :sorting="sorting" :filtering="filtering"
                :search-placeholder="trans('ui.search_groups_placeholder')" :striped="false" />

            <!-- Map -->
            <div
                class="relative h-[31.25rem] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border mt-6">
                <MapboxMap :access-token="mapboxAccessToken" ref="mapboxMap" class="h-full w-full rounded-xl"
                    :center="mapCenter" map-style="mapbox://styles/mapbox/satellite-streets-v12" :zoom="5">
                    <MapboxNavigationControl position="bottom-right" />
                </MapboxMap>
            </div>
        </div>
    </AppLayout>
</template>
