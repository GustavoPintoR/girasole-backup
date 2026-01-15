<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ArrowLeft, Save, AlertCircle, MapPin, Edit3, Upload, Layers, User, Building2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import axios from 'axios';
import { debounce } from 'lodash';
import { sqmToHa } from '@/utils/conversions';

import {
    MapboxMap,
    MapboxNavigationControl,
} from '@studiometa/vue-mapbox-gl';

import mapboxgl from 'mapbox-gl';
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import 'mapbox-gl/dist/mapbox-gl.css';
import { CadastralUnit } from '@/types';
import CultivarSelector from './partials/CultivarSelector.vue';
import PlantingSchemeSelector from './partials/PlantingSchemeSelector.vue';
import PlantDiseaseSelector from './partials/PlantDiseaseSelector.vue';
import IrrigationSelector from './partials/IrrigationSelector.vue';

interface UserOption {
    id: number;
    name: string;
    email: string;
}

interface CompanyOption {
    id: number;
    name: string;
    description?: string | null;
}

interface Props {
    availableUnits: CadastralUnit[];
    users: UserOption[] | null;
    companies: CompanyOption[] | null;
    isAdmin: boolean;
    ownedCompanyIds?: number[];
    cultivars: Array<{
        id: number;
        name: string;
        description?: string | null;
        cultivation_name?: string | null;
    }>;
    plantingSchemes: Array<{
        id: number;
        name: string;
        description?: string | null;
        distance?: string | null;
    }>;
    plantDiseases: Array<{
        id: number;
        name: string;
        description?: string | null;
    }>;
    irrigations: Array<{
        id: number;
        type: string;
        description?: string | null;
    }>;
}

const props = defineProps<Props>();

const breadcrumbs = [
    {
        title: trans('ui.cadastral_groups'),
        href: route('cadastral-groups.index'),
    },
    {
        title: trans('ui.create'),
        href: route('cadastral-groups.create'),
    },
];

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);
const mapboxMap = ref();
const map = computed(() => mapboxMap.value?.map);
const mapCenter = ref<[number, number]>([12.50, 42.50]);
const activeTab = ref<'units' | 'manual' | 'import'>('units');
const draw = ref<MapboxDraw | null>(null);
const currentPolygonId = ref<string | null>(null);
const polygonModified = ref(false);
const loadingUnits = ref(false);
const currentAvailableUnits = ref<CadastralUnit[]>(props.availableUnits);

const form = useForm({
    name: '',
    description: '',
    color: '#3B82F6',
    creation_method: 'units' as 'units' | 'manual' | 'import',
    user_id: currentUser.value?.id || null,
    company_id: null as number | null,
    unit_ids: [] as number[],
    geojson: null as string | null,
    cultivars: [] as Array<{
        id: number;
        name?: string;
        description?: string | null;
        cultivation_name?: string | null;
    }>,
    planting_schemes: [] as Array<{
        id: number;
        name: string;
        description?: string | null;
        distance?: string | null;
    }>,
    plant_diseases: [] as Array<{
        id: number;
        name: string;
        description?: string | null;
    }>,
    irrigations: [] as Array<{
        id: number;
        type: string;
        description?: string | null;
    }>,
});

const selectedUnits = ref<Set<number>>(new Set());
const adjacencyValid = ref<boolean | null>(null);
const checkingAdjacency = ref(false);
const totalArea = ref(0);

// Load units for a specific user
const loadUserUnits = async (userId: number) => {
    if (!props.isAdmin) return;

    loadingUnits.value = true;
    try {
        const response = await axios.get(route('api.internal.cadastral-units.get-available-units'), {
            params: {
                current_user_id: page.props.auth.user.id,
                user_id: userId
            }
        });

        currentAvailableUnits.value = response.data.units;

        // Reset selected units when user changes
        selectedUnits.value.clear();
        form.unit_ids = [];
        adjacencyValid.value = null;
        totalArea.value = 0;

        // Refresh map if in units mode
        if (activeTab.value === 'units' && map.value) {
            initUnitsMap();
        }
    } catch (error) {
        console.error('Error loading user units:', error);
        currentAvailableUnits.value = [];
    } finally {
        loadingUnits.value = false;
    }
};

const loadCompanyUnits = async (companyId: number) => {
    loadingUnits.value = true;
    try {
        const response = await axios.get(route('api.internal.cadastral-units.get-available-units'), {
            params: {
                current_user_id: page.props.auth.user.id,
                company_id: companyId
            }
        });

        currentAvailableUnits.value = response.data.units;

        // Reset selected units when company changes
        selectedUnits.value.clear();
        form.unit_ids = [];
        adjacencyValid.value = null;
        totalArea.value = 0;

        // Refresh map if in units mode
        if (activeTab.value === 'units' && map.value) {
            initUnitsMap();
        }
    } catch (error) {
        console.error('Error loading company units:', error);
        currentAvailableUnits.value = [];
    } finally {
        loadingUnits.value = false;
    }
};

// Determine whether the current user can assign a company when creating
const canEditCompany = computed(() => {
    if (props.isAdmin) return true;
    const owned = props.ownedCompanyIds || [];
    return owned.length > 0;
});

const availableCompanies = computed(() => {
    if (props.isAdmin) return props.companies || [];
    const owned = props.ownedCompanyIds || [];
    if (canEditCompany.value) {
        return (props.companies || []).filter(c => owned.includes(c.id));
    }
    return props.companies || [];
});

watch(() => form.company_id, (newCompanyId) => {
    if (newCompanyId && activeTab.value === 'units') {
        loadCompanyUnits(newCompanyId);
    } else if (!newCompanyId && activeTab.value === 'units') {
        // Reset to user's units
        if (form.user_id) {
            loadUserUnits(form.user_id);
        }
    }
});

// Watch for user changes
watch(() => form.user_id, (newUserId) => {
    if (props.isAdmin && newUserId) {
        if (form.company_id) {
            loadCompanyUnits(form.company_id);
        } else {
            loadUserUnits(newUserId);
        }
    } else if (props.isAdmin && newUserId === currentUser.value?.id) {
        // Reset to original units
        currentAvailableUnits.value = props.availableUnits;
        selectedUnits.value.clear();
        form.unit_ids = [];
        adjacencyValid.value = null;
        totalArea.value = 0;

        if (activeTab.value === 'units' && map.value) {
            initUnitsMap();
        }
    }
});


// Watch for tab changes to update creation method and clear relevant data
watch(activeTab, (newTab) => {
    form.creation_method = newTab;

    // Clear data from other methods
    if (newTab !== 'units') {
        selectedUnits.value.clear();
        form.unit_ids = [];
        adjacencyValid.value = null;
        totalArea.value = 0;
    }

    if (newTab !== 'manual' && newTab !== 'import') {
        form.geojson = null;
        clearDrawing();
    }

    // Redraw map based on new method
    if (map.value) {
        if (newTab === 'units') {
            initUnitsMap();
        } else if (newTab === 'manual') {
            initDrawingMap();
        } else {
            initImportMap();
        }
    }
});

const toggleUnit = (unitId: number) => {
    if (selectedUnits.value.has(unitId)) {
        selectedUnits.value.delete(unitId);
    } else {
        selectedUnits.value.add(unitId);
    }
    form.unit_ids = Array.from(selectedUnits.value);
    checkAdjacency();
};

const checkAdjacency = async () => {
    if (form.unit_ids.length < 1) {
        adjacencyValid.value = false;
        return;
    }

    if (form.unit_ids.length === 1) {
        adjacencyValid.value = true;
        return;
    }

    checkingAdjacency.value = true;
    try {
        const params: any = { unit_ids: form.unit_ids };
        if (props.isAdmin && form.user_id) {
            params.user_id = form.user_id;
        }

        const response = await axios.post(route('api.internal.cadastral-groups.check-adjacency'), params);
        adjacencyValid.value = response.data.adjacent;
    } catch (error) {
        console.error('Error checking adjacency:', error);
        adjacencyValid.value = false;
    } finally {
        checkingAdjacency.value = false;
    }
};

const calculateTotalArea = debounce(async () => {
    if (form.unit_ids.length === 0) {
        totalArea.value = 0;
        return;
    }

    try {
        const params: any = { unit_ids: form.unit_ids };
        if (props.isAdmin && form.user_id) {
            params.user_id = form.user_id;
        }

        const response = await axios.post(route('api.internal.cadastral-groups.calculate-area'), params);
        totalArea.value = response.data.total_area;
    } catch (error) {
        console.error('Error calculating area:', error);
        totalArea.value = currentAvailableUnits.value
            .filter(unit => selectedUnits.value.has(unit.id))
            .reduce((sum, unit) => sum + (unit.cadastral_area || 0), 0);
    }
}, 500);

const initDrawingMap = () => {
    if (!map.value) return;

    // Clear any existing layers
    clearMapLayers();

    if (draw.value) {
        map.value.removeControl(draw.value);
    }

    draw.value = new MapboxDraw({
        displayControlsDefault: false,
        controls: {
            polygon: true,
            trash: true
        },
        defaultMode: 'draw_polygon'
    });

    map.value.addControl(draw.value, 'top-left');

    // Load existing geometry if available
    if (form.geojson && activeTab.value === 'manual') {
        try {
            const geometry = JSON.parse(form.geojson);
            const feature = {
                type: "Feature",
                geometry: geometry,
                properties: {}
            };

            const featureIds = draw.value.add(feature);
            if (featureIds && featureIds.length > 0) {
                currentPolygonId.value = featureIds[0];
            }
        } catch (e) {
            console.error('Error loading existing geometry:', e);
        }
    }

    map.value.on('draw.create', handleDrawCreate);
    map.value.on('draw.update', handleDrawUpdate);
    map.value.on('draw.delete', handleDrawDelete);
};

const handleDrawCreate = (e: any) => {
    if (!draw.value) return;
    // Delete any existing polygons before setting the new one
    const allFeatures = draw.value.getAll();
    allFeatures.features.forEach((feature: any) => {
        // Delete all features except the newly created one
        if (feature.id !== e.features[0].id) {
            draw.value.delete(feature.id);
        }
    });

    const feature = e.features[0];
    currentPolygonId.value = feature.id;
    polygonModified.value = true;
    updateGeojsonField();
};

const handleDrawUpdate = () => {
    polygonModified.value = true;
    updateGeojsonField();
};

const handleDrawDelete = () => {
    currentPolygonId.value = null;
    polygonModified.value = true;
    form.geojson = null;
};

const updateGeojsonField = () => {
    if (!draw.value) return;

    const data = draw.value.getAll();
    if (data.features.length > 0) {
        form.geojson = JSON.stringify(data.features[0].geometry);
    } else {
        form.geojson = null;
    }
};

const clearDrawing = () => {
    if (!draw.value) return;

    draw.value.deleteAll();
    currentPolygonId.value = null;
    polygonModified.value = false;
    form.geojson = null;
};

const initImportMap = () => {
    if (!map.value) return;

    clearMapLayers();

    if (draw.value) {
        map.value.removeControl(draw.value);
        draw.value = null;
    }

    // If there's imported GeoJSON, display it
    if (form.geojson && activeTab.value === 'import') {
        try {
            const geometry = JSON.parse(form.geojson);

            map.value.addSource('import-preview', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    properties: {},
                    geometry: geometry
                }
            });

            map.value.addLayer({
                id: 'import-preview-fill',
                type: 'fill',
                source: 'import-preview',
                paint: {
                    'fill-color': form.color,
                    'fill-opacity': 0.6
                }
            });

            map.value.addLayer({
                id: 'import-preview-outline',
                type: 'line',
                source: 'import-preview',
                paint: {
                    'line-color': form.color,
                    'line-width': 3
                }
            });

            // Fit bounds to imported geometry
            const bounds = new mapboxgl.LngLatBounds();
            if (geometry.type === 'Polygon') {
                geometry.coordinates[0].forEach((coord: [number, number]) => {
                    bounds.extend(coord);
                });
            } else if (geometry.type === 'MultiPolygon') {
                geometry.coordinates.forEach((polygon: any) => {
                    polygon[0].forEach((coord: [number, number]) => {
                        bounds.extend(coord);
                    });
                });
            }
            map.value.fitBounds(bounds, { padding: 100 });
        } catch (e) {
            console.error('Error displaying imported geometry:', e);
        }
    }
};

const initUnitsMap = () => {
    if (!map.value) return;

    clearMapLayers();

    if (draw.value) {
        map.value.removeControl(draw.value);
        draw.value = null;
    }

    const features = currentAvailableUnits.value
        .filter(unit => unit.geometry_json)
        .map(unit => ({
            type: 'Feature',
            properties: {
                id: unit.id,
                sheet: unit.sheet,
                parcel: unit.parcel,
                selected: selectedUnits.value.has(unit.id),
                city: unit.city.name,
                area: unit.cadastral_area,
                region: unit.city.region,
                province_code: unit.city.province_code
            },
            geometry: JSON.parse(unit.geometry_json!)
        }));

    if (features.length === 0) return;

    map.value.addSource('units', {
        type: 'geojson',
        data: {
            type: 'FeatureCollection',
            features
        }
    });

    // Unselected units
    map.value.addLayer({
        id: 'units-fill',
        type: 'fill',
        source: 'units',
        filter: ['!=', ['get', 'selected'], true],
        paint: {
            'fill-color': '#94a3b8',
            'fill-opacity': 0.3
        }
    });

    map.value.addLayer({
        id: 'units-outline',
        type: 'line',
        source: 'units',
        filter: ['!=', ['get', 'selected'], true],
        paint: {
            'line-color': '#64748b',
            'line-width': 1
        }
    });

    // Selected units
    map.value.addLayer({
        id: 'units-selected-fill',
        type: 'fill',
        source: 'units',
        filter: ['==', ['get', 'selected'], true],
        paint: {
            'fill-color': form.color,
            'fill-opacity': 0.6
        }
    });

    map.value.addLayer({
        id: 'units-selected-outline',
        type: 'line',
        source: 'units',
        filter: ['==', ['get', 'selected'], true],
        paint: {
            'line-color': form.color,
            'line-width': 3
        }
    });

    // Add click handlers with popup
    ['units-fill', 'units-selected-fill'].forEach(layerId => {
        map.value.on('click', layerId, (e: any) => {
            if (e.features[0]) {
                const properties = e.features[0].properties;
                toggleUnit(properties.id);

                // Show popup
                new mapboxgl.Popup()
                    .setLngLat(e.lngLat)
                    .setHTML(`
                        <div class="p-2">
                            <h4 class="font-semibold">${properties.city} (${properties.province_code})</h4>
                            <p class="text-sm">
                                ${trans('ui.sheet')}: ${properties.sheet}<br>
                                ${trans('ui.parcel')}: ${properties.parcel}<br>
                                ${trans('ui.area')}: ${sqmToHa(properties.area) || 'N/A'} ha
                            </p>
                            <p class="pt-1 border-t text-sm">${properties.region}</p>
                        </div>
                    `)
                    .addTo(map.value);
            }
        });

        map.value.on('mouseenter', layerId, () => {
            map.value.getCanvas().style.cursor = 'pointer';
        });

        map.value.on('mouseleave', layerId, () => {
            map.value.getCanvas().style.cursor = '';
        });
    });

    // Fit bounds
    if (features.length > 0) {
        const bounds = new mapboxgl.LngLatBounds();
        features.forEach(feature => {
            if (feature.geometry.type === 'Polygon') {
                feature.geometry.coordinates[0].forEach((coord: [number, number]) => {
                    bounds.extend(coord);
                });
            }
        });
        map.value.fitBounds(bounds, { padding: 100 });
    }
};

const clearMapLayers = () => {
    if (!map.value) return;

    const layers = [
        'units-fill', 'units-outline', 'units-selected-fill', 'units-selected-outline',
        'import-preview-fill', 'import-preview-outline'
    ];

    layers.forEach(layer => {
        if (map.value.getLayer(layer)) {
            map.value.removeLayer(layer);
        }
    });

    ['units', 'import-preview'].forEach(source => {
        if (map.value.getSource(source)) {
            map.value.removeSource(source);
        }
    });

    // Remove draw events
    map.value.off('draw.create', handleDrawCreate);
    map.value.off('draw.update', handleDrawUpdate);
    map.value.off('draw.delete', handleDrawDelete);
};

const handleCultivarsUpdate = (cultivars: any[]) => {
    form.cultivars = cultivars;
};

const handlePlantingSchemesUpdate = (schemes: any[]) => {
    form.planting_schemes = schemes;
};

const handlePlantDiseasesUpdate = (diseases: any[]) => {
    form.plant_diseases = diseases;
};

const handleIrrigationsUpdate = (irrigations: any[]) => {
    form.irrigations = irrigations;
};

watch([selectedUnits], () => {
    calculateTotalArea();
    if (map.value && activeTab.value === 'units') {
        initUnitsMap();
    }
}, { deep: true });

watch(() => form.color, () => {
    if (map.value) {
        if (activeTab.value === 'units') {
            initUnitsMap();
        } else if (activeTab.value === 'import') {
            initImportMap();
        }
    }
});

const submit = () => {
    if (activeTab.value === 'manual' || activeTab.value === 'import') {
        updateGeojsonField();
    }

    form.post(route('cadastral-groups.store'), {
        preserveScroll: true,
    });
};

const cancel = () => {
    router.visit(route('cadastral-groups.index'));
};

const isFormValid = computed(() => {
    if (!form.name || !form.user_id) return false;

    switch (activeTab.value) {
        case 'units':
            return selectedUnits.value.size > 0 &&
                (selectedUnits.value.size === 1 || adjacencyValid.value === true);
        case 'manual':
        case 'import':
            return !!form.geojson;
        default:
            return false;
    }
});

onMounted(() => {
    nextTick(() => {
        if (map.value) {
            map.value.on('load', () => {
                initUnitsMap();
            });
        }
    });
});
</script>

<template>

    <Head :title="trans('ui.create_cadastral_group')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.create_cadastral_group') }}
                </h1>

                <Button variant="outline" @click="cancel">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    {{ trans('ui.back_to_list') }}
                </Button>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Form Fields -->
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.group_details') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.group_details_description') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Owner Selection (Admin only) -->
                                <div v-if="isAdmin && users" class="space-y-2">
                                    <Label for="user">
                                        <User class="inline h-4 w-4 mr-1" />
                                        {{ trans('ui.owner') || 'Owner' }} <span class="text-red-500">*</span>
                                    </Label>
                                    <Select v-model="form.user_id" :disabled="form.processing || loadingUnits">
                                        <SelectTrigger id="user">
                                            <SelectValue placeholder="Select an owner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                                                {{ user.name }} ({{ user.email }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.user_id" class="text-sm text-red-500">
                                        {{ form.errors.user_id }}
                                    </p>
                                </div>

                                <!-- Company Selection -->
                                <div v-if="companies && companies.length > 0" class="space-y-2">
                                    <Label for="company">
                                        <Building2 class="inline h-4 w-4 mr-1" />
                                        {{ trans('ui.company') }}
                                    </Label>
                                    <Select v-model="form.company_id"
                                        :disabled="form.processing || loadingUnits || !canEditCompany">
                                        <SelectTrigger id="company">
                                            <SelectValue :placeholder="trans('ui.select_company')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="null">
                                                {{ trans('ui.none') }}
                                            </SelectItem>
                                            <SelectItem v-for="company in availableCompanies" :key="company.id"
                                                :value="company.id">
                                                {{ company.name }}
                                                <!-- <span v-if="company.description" class="text-xs text-gray-500 ml-2">
                                                    - {{ company.description }}
                                                </span> -->
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p class="text-sm text-gray-500">
                                        {{ trans('ui.company_field_help') }}
                                    </p>
                                    <p v-if="form.errors.company_id" class="text-sm text-red-500">
                                        {{ form.errors.company_id }}
                                    </p>

                                    <Alert v-if="!canEditCompany"
                                        class="mt-2 border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/20">
                                        <Building2 class="h-4 w-4 text-slate-600" />
                                        <AlertDescription class="text-slate-700 dark:text-slate-300">
                                            {{ trans('ui.company_edit_restricted') }}
                                        </AlertDescription>
                                    </Alert>
                                </div>

                                <div class="space-y-2">
                                    <Label for="name">
                                        {{ trans('ui.group_name') }} <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" type="text"
                                        :placeholder="trans('ui.enter_group_name')"
                                        :class="{ 'border-red-500': form.errors.name }" :disabled="form.processing" />
                                    <p v-if="form.errors.name" class="text-sm text-red-500">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="description">
                                        {{ trans('ui.description') }}
                                    </Label>
                                    <Textarea id="description" v-model="form.description"
                                        :placeholder="trans('ui.enter_group_description')" :rows="3"
                                        :disabled="form.processing" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="color">
                                        {{ trans('ui.map_color') }}
                                    </Label>
                                    <div class="flex items-center gap-2">
                                        <Input id="color" v-model="form.color" type="color"
                                            class="w-20 h-10 cursor-pointer" :disabled="form.processing" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.creation_method') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.choose_how_to_create_group') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Tabs v-model="activeTab" class="w-full">
                                    <TabsList class="grid w-full grid-cols-3">
                                        <TabsTrigger value="units" :disabled="loadingUnits">
                                            <Layers class="mr-2 h-4 w-4" />
                                            {{ trans('ui.from_units') }}
                                        </TabsTrigger>
                                        <TabsTrigger value="manual">
                                            <Edit3 class="mr-2 h-4 w-4" />
                                            {{ trans('ui.draw_manually') }}
                                        </TabsTrigger>
                                        <TabsTrigger value="import">
                                            <Upload class="mr-2 h-4 w-4" />
                                            {{ trans('ui.import_geojson') }}
                                        </TabsTrigger>
                                    </TabsList>

                                    <TabsContent value="units" class="mt-4">
                                        <!-- Loading indicator -->
                                        <div v-if="loadingUnits" class="text-center py-8">
                                            <p class="text-gray-500">
                                                {{ trans('ui.loading_units') || 'Loading units...' }}</p>
                                        </div>

                                        <!-- Units selection -->
                                        <template v-else>
                                            <Alert v-if="currentAvailableUnits.length === 0" class="mb-4">
                                                <AlertCircle class="h-4 w-4" />
                                                <AlertDescription>
                                                    {{ isAdmin && form.user_id !== currentUser?.id
                                                        ? (trans('ui.selected_user_no_units') || 'The selected user has no available cadastral units.')
                                                        : trans('ui.no_available_units') }}
                                                </AlertDescription>
                                            </Alert>

                                            <template v-else>
                                                <Alert v-if="selectedUnits.size > 1 && adjacencyValid !== null"
                                                    :class="adjacencyValid ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
                                                    <AlertCircle class="h-4 w-4"
                                                        :class="adjacencyValid ? 'text-green-600' : 'text-red-600'" />
                                                    <AlertDescription
                                                        :class="adjacencyValid ? 'text-green-800' : 'text-red-800'">
                                                        {{ adjacencyValid ? trans('ui.units_are_adjacent') : trans('ui.units_not_adjacent') }}
                                                    </AlertDescription>
                                                </Alert>

                                                <div class="space-y-2 max-h-96 overflow-y-auto mt-4">
                                                    <div v-for="unit in currentAvailableUnits" :key="unit.id"
                                                        class="flex items-center space-x-3 p-2 border rounded hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
                                                        :class="{ 'bg-blue-50 dark:bg-blue-900/20 border-blue-300': selectedUnits.has(unit.id) }"
                                                        @click="toggleUnit(unit.id)">
                                                        <input type="checkbox" :checked="selectedUnits.has(unit.id)"
                                                            :disabled="form.processing || loadingUnits"
                                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                                            @click.stop @change="toggleUnit(unit.id)" />
                                                        <div class="flex-1">
                                                            <p class="font-medium">
                                                                {{ trans('ui.sheet') }}: {{ unit.sheet }},
                                                                {{ trans('ui.parcel') }}: {{ unit.parcel }}
                                                            </p>
                                                            <p class="text-sm text-gray-500">
                                                                {{ unit.city.name }}, {{ unit.city.province }}
                                                                <span v-if="unit.cadastral_area"> -
                                                                    {{ sqmToHa(unit.cadastral_area) }}
                                                                    ha</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <p v-if="form.errors.unit_ids" class="text-sm text-red-500 mt-2">
                                                {{ form.errors.unit_ids }}
                                            </p>

                                            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            {{ trans('ui.selected_units') }}
                                                        </p>
                                                        <p class="text-2xl font-semibold">{{ selectedUnits.size }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm text-gray-500">{{ trans('ui.total_area') }}
                                                        </p>
                                                        <p class="text-2xl font-semibold">{{ sqmToHa(totalArea) }}
                                                            ha</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </TabsContent>

                                    <TabsContent value="manual" class="mt-4">
                                        <Alert>
                                            <MapPin class="h-4 w-4" />
                                            <AlertDescription>
                                                {{ trans('ui.draw_polygon_on_map') }}
                                            </AlertDescription>
                                        </Alert>

                                        <div v-if="form.geojson && activeTab === 'manual'"
                                            class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                            <p class="text-sm text-green-800 dark:text-green-200">
                                                {{ trans('ui.polygon_drawn_successfully') }}
                                            </p>
                                        </div>

                                        <p v-if="form.errors.geojson && activeTab === 'manual'"
                                            class="text-sm text-red-500 mt-2">
                                            {{ form.errors.geojson }}
                                        </p>
                                    </TabsContent>

                                    <TabsContent value="import" class="mt-4">
                                        <div class="space-y-2">
                                            <Label for="geojson-import">
                                                {{ trans('ui.paste_geojson') }}
                                            </Label>
                                            <Textarea id="geojson-import" v-model="form.geojson as string"
                                                :placeholder="trans('ui.paste_geojson_here')" :rows="8"
                                                class="font-mono text-sm" :disabled="form.processing" />
                                        </div>

                                        <p v-if="form.errors.geojson && activeTab === 'import'"
                                            class="text-sm text-red-500 mt-2">
                                            {{ form.errors.geojson }}
                                        </p>
                                    </TabsContent>
                                </Tabs>
                            </CardContent>
                        </Card>

                        <!-- Cultivars Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.field_cultivars') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.campi_cultivars_description') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <CultivarSelector :available-cultivars="cultivars"
                                    :selected-cultivars="form.cultivars as any[]" :disabled="form.processing"
                                    @update:selected-cultivars="handleCultivarsUpdate" />
                            </CardContent>
                        </Card>

                        <!-- Irrigation Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.irrigations') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.manage_irrigations') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <IrrigationSelector :available-irrigations="props.irrigations"
                                    :selected-irrigations="form.irrigations as any[]" :disabled="form.processing"
                                    @update:selected-irrigations="handleIrrigationsUpdate" />
                            </CardContent>
                        </Card>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing || !isFormValid || loadingUnits">
                                <Save class="mr-2 h-4 w-4" />
                                {{ form.processing ? trans('ui.creating') : trans('ui.create_group') }}
                            </Button>
                        </div>
                    </div>

                    <!-- other section -->
                    <div class="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.map_preview') }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div
                                    class="relative h-[600px] rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    <MapboxMap :access-token="mapboxAccessToken" ref="mapboxMap" class="h-full w-full"
                                        :center="mapCenter" map-style="mapbox://styles/mapbox/satellite-streets-v12"
                                        :zoom="12">
                                        <MapboxNavigationControl position="bottom-right" />
                                    </MapboxMap>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Planting Schemes Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.planting_schemes') || 'Planting Schemes' }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.planting_schemes_description') || 'Manage planting schemes for this field.' }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <PlantingSchemeSelector :available-schemes="plantingSchemes"
                                    :selected-schemes="form.planting_schemes as any[]" :disabled="form.processing"
                                    @update:selected-schemes="handlePlantingSchemesUpdate" />
                            </CardContent>
                        </Card>

                        <!-- Plant Diseases Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.plant_diseases') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.manage_plant_diseases') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <PlantDiseaseSelector :available-plant-diseases="plantDiseases"
                                    :selected-plant-diseases="form.plant_diseases"
                                    @update:selected-plant-diseases="handlePlantDiseasesUpdate"
                                    :disabled="form.processing" />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
