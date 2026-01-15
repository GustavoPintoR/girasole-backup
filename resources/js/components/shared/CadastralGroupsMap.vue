<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { MapPin, Cloud, CloudRain, CloudSnow, Sun, CloudDrizzle } from 'lucide-vue-next';
import { Card } from '@/components/ui/card';
import { sqmToHa } from '@/utils/conversions';
import {
    MapboxMap,
    MapboxNavigationControl,
    MapboxMarker,
    MapboxPopup
} from '@studiometa/vue-mapbox-gl';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

interface SensorData {
    id: number;
    name: string;
    serial: string;
    description: string | null;
    latitude: number;
    longitude: number;
    cadastral_group_id: number;
    sensor_type?: {
        id: number;
        name: string;
        description: string | null;
    };
    metadata?: any;
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
    sensors?: SensorData[];
}

interface SensorMarkerData {
    id: number;
    coordinates: [number, number];
    sensor: SensorData;
    groupColor: string;
}

interface WeatherData {
    temperature: number;
    precipitation: number;
    humidity: number;
    time: string;
    units: {
        temperature: string;
        precipitation: string;
        humidity: string;
    };
}

interface WeatherMarkerData {
    sensorId: number;
    sensorName: string;
    coordinates: [number, number];
    weather: WeatherData | null;
    loading: boolean;
    error: string | null;
}

const page = usePage();
const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);
const mapboxMap = ref();
const map = computed(() => mapboxMap.value?.map);
const mapCenter = ref([12.50, 42.50]);
const activeGroupId = ref<number | null>(null);
const hoveredGroupId = ref<number | null>(null);
const activeSensorId = ref<number | null>(null);
const activeWeatherId = ref<number | null>(null);
const cadastralGroups = ref<GroupData[]>([]);
const sensorMarkers = ref<SensorMarkerData[]>([]);
const weatherMarkers = ref<WeatherMarkerData[]>([]);
const isLoading = ref(false);

// Internal state for user toggles
const sensorsToggled = ref(true);
const weatherToggled = ref(false);

const props = withDefaults(defineProps<{
    groups?: GroupData[];
    height?: string;
    showSensors?: boolean;
    showWeather?: boolean;
    currentGroupId?: number | null;
}>(), {
    height: 'h-[31.25rem]',
    showSensors: true,
    showWeather: false,
    currentGroupId: null,
});

const emit = defineEmits<{
    groupSelected: [group: GroupData];
    sensorSelected: [sensor: SensorData];
    mapReady: [map: any];
}>();

// Computed Properties
const activeGroup = computed(() =>
    activeGroupId.value ? cadastralGroups.value.find(g => g.id === activeGroupId.value) : null
);

// const selectedGroup = computed(() =>
//     activeGroupId.value ? cadastralGroups.value.find(g => g.id === activeGroupId.value) : null
// );

const groupPopupPosition = computed(() =>
    activeGroup.value?.centroid_json ? JSON.parse(activeGroup.value.centroid_json).coordinates : null
);

// Visibility properties
const areSensorsVisible = computed(() => props.showSensors && sensorsToggled.value);
const isWeatherVisible = computed(() => props.showWeather && weatherToggled.value);

const dropdownVisible = computed(() =>
    cadastralGroups.value.length > 1 && (props.showSensors || props.showWeather)
);

watch(() => props.currentGroupId, (newId) => {
    if (newId !== activeGroupId.value) {
        if (newId) {
            selectGroup(newId);
        } else {
            activeGroupId.value = null;
            updateMapPaint();
        }
    }
});

// Helper Functions
const getCreationMethodLabel = (method: string): string => {
    const labels: Record<string, string> = {
        units: trans('ui.from_units'),
        manual: trans('ui.drawn'),
        import: trans('ui.imported')
    };
    return labels[method] || method;
};

// const getCreationMethodVariant = (method: string): string => {
//     const variants: Record<string, string> = {
//         units: 'default',
//         manual: 'secondary',
//         import: 'outline'
//     };
//     return variants[method] || 'outline';
// };

const getWeatherIcon = (weather: WeatherData | null) => {
    if (!weather) return Cloud;

    const { temperature, precipitation } = weather;

    // Determine icon based on precipitation and temperature
    if (precipitation > 5) {
        return temperature < 0 ? CloudSnow : CloudRain;
    } else if (precipitation > 0) {
        return CloudDrizzle;
    } else if (temperature > 20) {
        return Sun;
    }

    return Cloud;
};

const isClickNearMarker = (clickPoint: { x: number; y: number }, tolerance = 20): boolean => {
    const markers = areSensorsVisible.value ? sensorMarkers.value :
        isWeatherVisible.value ? weatherMarkers.value : [];

    return markers.some(marker => {
        const markerPoint = map.value.project(marker.coordinates);
        const distance = Math.sqrt(
            Math.pow(markerPoint.x - clickPoint.x, 2) +
            Math.pow(markerPoint.y - clickPoint.y, 2)
        );
        return distance < tolerance;
    });
};

// Weather Functions
const fetchWeatherForSensor = (() => {
    let lastCall = 0;
    return async (sensor: SensorData): Promise<WeatherData | null> => {
        const now = Date.now();
        const timeSinceLastCall = now - lastCall;
        if (timeSinceLastCall < 5000) {
            return new Promise<WeatherData | null>((resolve) => {
                const timeoutId = setTimeout(() => {
                    resolve(fetchWeatherForSensor(sensor));
                }, 5000 - timeSinceLastCall);
                return () => clearTimeout(timeoutId);
            });
        }

        try {
            const response = await fetch(route('api.internal.weather.get-current-weather'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    latitude: sensor.latitude,
                    longitude: sensor.longitude,
                    timezone: 'auto'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            lastCall = now;
            // console.log('weather success');

            return data;
        } catch (error) {
            console.error(`Error fetching weather for sensor ${sensor.id}:`, error);
            lastCall = now;
            return null;
        }
    };
})();

const loadWeatherData = async () => {
    // Initialize weather markers from sensor data
    weatherMarkers.value = sensorMarkers.value.map(marker => ({
        sensorId: marker.sensor.id,
        sensorName: marker.sensor.name,
        coordinates: marker.coordinates,
        weather: null,
        loading: true,
        error: null
    }));

    // Fetch weather data for each sensor
    const weatherPromises = sensorMarkers.value.map(async (marker, index) => {
        const weather = await fetchWeatherForSensor(marker.sensor);

        if (weatherMarkers.value[index]) {
            weatherMarkers.value[index].weather = weather;
            weatherMarkers.value[index].loading = false;

            if (!weather) {
                weatherMarkers.value[index].error = 'Failed to fetch weather data';
            }
        }
    });

    await Promise.all(weatherPromises);
};

// Data Loading
const loadGroupsData = async () => {
    if (props.groups) {
        cadastralGroups.value = props.groups || [];
        computeSensorMarkers();
        return;
    }

    isLoading.value = true;
    try {
        const response = await fetch(route('api.internal.cadastral-groups.mapData', {
            user: usePage().props.auth.user.id
        }));
        const data = await response.json();
        cadastralGroups.value = data.groups || [];
        computeSensorMarkers();
    } catch (error) {
        console.error('Error loading groups data:', error);
    } finally {
        isLoading.value = false;
    }
};

const computeSensorMarkers = () => {
    sensorMarkers.value = cadastralGroups.value.flatMap(group =>
        (group.sensors || [])
            .filter(sensor => sensor.latitude && sensor.longitude)
            .map(sensor => ({
                id: sensor.id,
                coordinates: [sensor.longitude, sensor.latitude] as [number, number],
                sensor,
                groupColor: group.color
            }))
    );

    nextTick(() => {
        if (sensorMarkers.value.length > 0 && map.value?.loaded()) {
            fitToAllContent();
        }
    });
};

// Map Layer Management
const MAP_LAYERS = ['groups-fill', 'groups-outline', 'groups-hover'];
const MAP_SOURCE = 'groups';

const removeMapLayers = () => {
    if (!map.value) return;

    MAP_LAYERS.forEach(layer => {
        if (map.value.getLayer(layer)) {
            map.value.removeLayer(layer);
        }
    });

    if (map.value.getSource(MAP_SOURCE)) {
        map.value.removeSource(MAP_SOURCE);
    }
};

const createGeoJsonFeatures = () => {
    return cadastralGroups.value
        .filter(group => group.boundary_geometry_json)
        .map(group => ({
            type: 'Feature',
            properties: {
                id: group.id,
                name: group.name,
                total_area: group.total_area,
                units_count: group.units_count,
                creation_method: group.creation_method,
                color: group.color,
                sensors_count: group.sensors?.length || 0
            },
            geometry: JSON.parse(group.boundary_geometry_json!)
        }));
};

const updateMapPaint = () => {
    if (!map.value?.getLayer('groups-fill')) return;

    map.value.setPaintProperty('groups-fill', 'fill-opacity', [
        'case',
        ['==', ['get', 'id'], activeGroupId.value ?? -1],
        0.6,
        ['==', ['get', 'id'], hoveredGroupId.value ?? -1],
        0.4,
        0.2
    ]);

    map.value.setPaintProperty('groups-outline', 'line-width', [
        'case',
        ['==', ['get', 'id'], activeGroupId.value ?? -1],
        3,
        2
    ]);
};

const addMapLayers = (features: any[]) => {
    if (!map.value) return;

    map.value.addSource(MAP_SOURCE, {
        type: 'geojson',
        data: { type: 'FeatureCollection', features }
    });

    // Fill layer
    map.value.addLayer({
        id: 'groups-fill',
        type: 'fill',
        source: MAP_SOURCE,
        paint: {
            'fill-color': ['get', 'color'],
            'fill-opacity': 0.2
        }
    });

    // Outline layer
    map.value.addLayer({
        id: 'groups-outline',
        type: 'line',
        source: MAP_SOURCE,
        paint: {
            'line-color': ['get', 'color'],
            'line-width': 2,
            'line-opacity': 0.8
        }
    });
};

// Map Interactions
const setupMapInteractions = () => {
    if (!map.value) return;

    // Hover effects
    map.value.on('mouseenter', 'groups-fill', (e: any) => {
        map.value.getCanvas().style.cursor = 'pointer';
        if (e.features?.[0]) {
            hoveredGroupId.value = e.features[0].properties.id;
            updateMapPaint();
        }
    });

    map.value.on('mouseleave', 'groups-fill', () => {
        map.value.getCanvas().style.cursor = '';
        hoveredGroupId.value = null;
        updateMapPaint();
    });

    // Group polygon clicks
    map.value.on('click', 'groups-fill', (e: any) => {
        if (!isClickNearMarker(e.point) && e.features?.[0]) {
            activeGroupId.value = e.features[0].properties.id;
            activeSensorId.value = null;
            activeWeatherId.value = null;
            updateMapPaint();
        }
    });

    // Empty area clicks - close popups
    map.value.on('click', (e: any) => {
        const features = map.value.queryRenderedFeatures(e.point, {
            layers: ['groups-fill']
        });

        if (features.length === 0 && !isClickNearMarker(e.point)) {
            activeGroupId.value = null;
            activeSensorId.value = null;
            activeWeatherId.value = null;
            updateMapPaint();
        }
    });
};

const drawGroupsOnMap = () => {
    if (!map.value || cadastralGroups.value.length === 0) return;

    removeMapLayers();
    const features = createGeoJsonFeatures();

    if (features.length === 0) return;

    addMapLayers(features);
    setupMapInteractions();
};

// Map Navigation
const getGeometryBounds = (geometry: any): mapboxgl.LngLatBounds => {
    const bounds = new mapboxgl.LngLatBounds();

    const addCoordinates = (coords: [number, number][]) => {
        coords.forEach(coord => bounds.extend(coord));
    };

    if (geometry.type === 'MultiPolygon') {
        geometry.coordinates.forEach((polygon: any) => addCoordinates(polygon[0]));
    } else if (geometry.type === 'Polygon') {
        addCoordinates(geometry.coordinates[0]);
    }

    return bounds;
};

const selectGroup = (groupId: number | string) => {
    const id = typeof groupId === 'string' ? parseInt(groupId) : groupId;
    const group = cadastralGroups.value.find(g => g.id === id);

    if (!group) return;

    activeGroupId.value = id;
    activeSensorId.value = null;
    activeWeatherId.value = null;
    updateMapPaint();

    // Center on group
    if (group.boundary_geometry_json && map.value) {
        const geometry = JSON.parse(group.boundary_geometry_json);
        const bounds = getGeometryBounds(geometry);

        if (!bounds.isEmpty()) {
            map.value.fitBounds(bounds, { padding: 100, duration: 1500 });
        }
    } else if (group.centroid_json && map.value) {
        const centroid = JSON.parse(group.centroid_json);
        map.value.flyTo({ center: centroid.coordinates, zoom: 14, duration: 1500 });
    }

    emit('groupSelected', group);
};

const fitToAllContent = () => {
    if (!map.value) return;

    const bounds = new mapboxgl.LngLatBounds();
    let hasValidBounds = false;

    // Add group boundaries
    cadastralGroups.value.forEach(group => {
        if (group.boundary_geometry_json) {
            const geometry = JSON.parse(group.boundary_geometry_json);
            const groupBounds = getGeometryBounds(geometry);
            if (!groupBounds.isEmpty()) {
                bounds.extend(groupBounds.getNorthEast());
                bounds.extend(groupBounds.getSouthWest());
                hasValidBounds = true;
            }
        }
    });

    // Add sensor positions
    sensorMarkers.value.forEach(marker => {
        bounds.extend(marker.coordinates);
        hasValidBounds = true;
    });

    if (hasValidBounds) {
        map.value.fitBounds(bounds, { padding: 100 });
    }
};

// Event Handlers
const handleSensorClick = (sensor: SensorData) => {
    activeSensorId.value = sensor.id;
    activeGroupId.value = null;
    activeWeatherId.value = null;
    emit('sensorSelected', sensor);
};

const handleWeatherClick = (weatherMarker: WeatherMarkerData) => {
    activeWeatherId.value = weatherMarker.sensorId;
    activeGroupId.value = null;
    activeSensorId.value = null;
};

const toggleSensors = () => {
    sensorsToggled.value = !sensorsToggled.value;
    if (sensorsToggled.value) {
        weatherToggled.value = false;
        activeWeatherId.value = null;
    }
};

const toggleWeather = async () => {
    weatherToggled.value = !weatherToggled.value;
    if (weatherToggled.value) {
        sensorsToggled.value = false;
        activeSensorId.value = null;

        // Load weather data if not already loaded
        if (weatherMarkers.value.length === 0 || weatherMarkers.value.every(m => !m.weather)) {
            await loadWeatherData();
        }
    }
};

const initializeMap = async () => {
    await nextTick();
    if (map.value) {
        map.value.on('load', async () => {
            await loadGroupsData();
            drawGroupsOnMap();
            emit('mapReady', map.value);
        });
    }
};

watch(() => props.groups, (newGroups) => {
    if (newGroups) {
        cadastralGroups.value = newGroups;
        computeSensorMarkers();
        if (map.value?.loaded()) {
            drawGroupsOnMap();
        }
    }
});

onMounted(() => {
    initializeMap();
});

onUnmounted(() => {
});

defineExpose({
    selectGroup,
    fitToAllContent,
    refresh: loadGroupsData
});
</script>

<template>
    <Card class="relative overflow-hidden py-0" :class="height">
        <!-- Controls overlay -->
        <div v-if="dropdownVisible" class="absolute top-1 left-4 right-4 z-10 flex gap-2 w-min">
            <!-- Toggle sensors button -->
            <button v-if="showSensors" @click="toggleSensors"
                class="px-3 py-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm shadow-md rounded-md hover:bg-gray-50 transition-colors"
                :title="sensorsToggled ? trans('ui.hide_sensors') : trans('ui.show_sensors')">
                <MapPin class="h-4 w-4" :class="sensorsToggled ? 'text-blue-600' : 'text-gray-400'" />
            </button>

            <!-- Toggle weather button -->
            <button v-if="showWeather" @click="toggleWeather"
                class="px-3 py-2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm shadow-md rounded-md hover:bg-gray-50 transition-colors"
                :title="weatherToggled ? trans('ui.hide_weather') : trans('ui.show_weather')">
                <Cloud class="h-4 w-4" :class="weatherToggled ? 'text-orange-500' : 'text-gray-400'" />
            </button>
        </div>

        <!-- Loading overlay -->
        <div v-if="isLoading"
            class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 dark:bg-gray-900/70">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.loading') }}</p>
            </div>
        </div>

        <!-- Map -->
        <MapboxMap :access-token="mapboxAccessToken" ref="mapboxMap" class="h-full w-full" :center="mapCenter"
            map-style="mapbox://styles/mapbox/satellite-v9" :zoom="5">
            <MapboxNavigationControl position="bottom-right" />

            <!-- Sensor Markers -->
            <template v-if="areSensorsVisible">
                <template v-for="marker in sensorMarkers" :key="`sensor-${marker.id}`">
                    <MapboxMarker :lng-lat="marker.coordinates">
                        <div class="relative cursor-pointer" @click="handleSensorClick(marker.sensor)">
                            <MapPin :fill="activeSensorId === marker.sensor.id ? 'red' : 'blue'"
                                class="h-6 w-6 text-white transition-all"
                                :class="{ 'scale-125': activeSensorId === marker.sensor.id }" />
                        </div>
                    </MapboxMarker>

                    <!-- Sensor Popup -->
                    <MapboxPopup v-if="activeSensorId === marker.sensor.id" :lng-lat="marker.coordinates"
                        :offset="[0, -20]" :close-button="true" :close-on-click="false" @close="activeSensorId = null">
                        <div class="p-2 min-w-[200px]">
                            <h3 class="font-semibold text-gray-900 mb-1">{{ marker.sensor.name }}</h3>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p><span class="font-medium">{{ trans('ui.serial') }}:</span> {{ marker.sensor.serial }}
                                </p>
                                <p v-if="marker.sensor.sensor_type">
                                    <span class="font-medium">{{ trans('ui.type') }}:</span>
                                    {{ marker.sensor.sensor_type.name }}
                                </p>
                                <p v-if="marker.sensor.description">
                                    <span class="font-medium">{{ trans('ui.description') }}:</span>
                                    {{ marker.sensor.description }}
                                </p>
                                <p>
                                    <span class="font-medium">{{ trans('ui.coordinates') }}:</span>
                                    {{ marker.sensor.latitude.toFixed(6) }}, {{ marker.sensor.longitude.toFixed(6) }}
                                </p>
                                <p>
                                    <span class="font-medium">{{ trans('ui.field') }}:</span>
                                    {{cadastralGroups.find(g => g.id === marker.sensor.cadastral_group_id)?.name}}
                                </p>
                            </div>
                        </div>
                    </MapboxPopup>
                </template>
            </template>

            <!-- Weather Markers -->
            <template v-if="isWeatherVisible">
                <template v-for="marker in weatherMarkers" :key="`weather-${marker.sensorId}`">
                    <MapboxMarker :lng-lat="marker.coordinates">
                        <div class="relative cursor-pointer bg-white rounded-full p-1.5 shadow-lg border-2 border-orange-400 transition-all"
                            :class="{ 'scale-125 border-orange-600': activeWeatherId === marker.sensorId }"
                            @click="handleWeatherClick(marker)">
                            <component :is="getWeatherIcon(marker.weather)" class="h-5 w-5 text-orange-600"
                                :class="{ 'animate-pulse': marker.loading }" />
                        </div>
                    </MapboxMarker>

                    <!-- Weather Popup -->
                    <MapboxPopup v-if="activeWeatherId === marker.sensorId" :lng-lat="marker.coordinates"
                        :offset="[0, -30]" :close-button="true" :close-on-click="false" @close="activeWeatherId = null"
                        max-width="400">
                        <div class="p-2 min-w-[240px]">
                            <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                                <component :is="getWeatherIcon(marker.weather)" class="h-5 w-5 text-orange-600" />
                                {{ marker.sensorName }}
                            </h3>

                            <div v-if="marker.loading" class="text-sm text-gray-500 py-2">
                                {{ trans('ui.loading_weather_data') }}
                            </div>

                            <div v-else-if="marker.error" class="text-sm text-red-600 py-2">
                                {{ marker.error }}
                            </div>

                            <div v-else-if="marker.weather" class="text-sm text-gray-600 space-y-1.5">
                                <p class="flex items-center justify-between">
                                    <span class="font-medium">{{ trans('ui.temperature') }}:</span>
                                    <span class="text-lg font-semibold text-orange-600">
                                        {{ marker.weather.temperature }}{{ marker.weather.units.temperature }}
                                    </span>
                                </p>
                                <p class="flex items-center justify-between">
                                    <span class="font-medium">{{ trans('ui.humidity') }}:</span>
                                    <span>{{ marker.weather.humidity }}{{ marker.weather.units.humidity }}</span>
                                </p>
                                <p class="flex items-center justify-between">
                                    <span class="font-medium">{{ trans('ui.precipitation') }}:</span>
                                    <span>{{ marker.weather.precipitation }}{{ marker.weather.units.precipitation }}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-2 pt-2 border-t border-gray-200">
                                    {{ trans('ui.updated') }}: {{ new Date(marker.weather.time).toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </MapboxPopup>
                </template>
            </template>

            <!-- Group Popup -->
            <MapboxPopup v-if="activeGroup && !activeSensorId && !activeWeatherId && groupPopupPosition"
                :lng-lat="groupPopupPosition" :offset="[0, 0]" :close-button="true" :close-on-click="false"
                @close="activeGroupId = null">
                <div class="p-2 min-w-[200px]">
                    <h3 class="font-semibold text-gray-900 mb-1">{{ activeGroup.name }}</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p>
                            <span class="font-medium">{{ trans('ui.creation_method') }}:</span>
                            {{ getCreationMethodLabel(activeGroup.creation_method) }}
                        </p>
                        <p v-if="activeGroup.creation_method === 'units'">
                            <span class="font-medium">{{ trans('ui.units_count') }}:</span>
                            {{ activeGroup.units_count }}
                        </p>
                        <p>
                            <span class="font-medium">{{ trans('ui.total_area') }}:</span>
                            {{ sqmToHa(activeGroup.total_area) }} ha
                        </p>
                        <p v-if="activeGroup.sensors">
                            <span class="font-medium">{{ trans('ui.sensors') }}:</span>
                            {{ activeGroup.sensors.length }}
                        </p>
                    </div>
                </div>
            </MapboxPopup>
        </MapboxMap>
    </Card>
</template>

<style scoped>
.custom-dropdown {
    position: relative;
}
</style>
