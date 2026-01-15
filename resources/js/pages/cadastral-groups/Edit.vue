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
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { ArrowLeft, Save, Trash2, AlertCircle, MapPin, Download, Edit3, Upload, Layers, User, Building2 } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import axios from 'axios';
import { debounce } from 'lodash';

import {
    MapboxMap,
    MapboxNavigationControl,
} from '@studiometa/vue-mapbox-gl';

import mapboxgl from 'mapbox-gl';
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import 'mapbox-gl/dist/mapbox-gl.css';

import { usePermissions } from '@/composables/usePermissions';
import { CadastralGroup, CadastralUnit } from '@/types';
import { sqmToHa } from '@/utils/conversions';
import CultivarSelector from './partials/CultivarSelector.vue';
import PlantingSchemeSelector from './partials/PlantingSchemeSelector.vue';
import PlantDiseaseSelector from './partials/PlantDiseaseSelector.vue';
import IrrigationSelector from './partials/IrrigationSelector.vue';

const { can } = usePermissions();

interface UserOption {
    id: number;
    name: string;
    email: string;
}

interface ExtendedCadastralGroup extends CadastralGroup {
    user_id: number;
    company_id: number | null
    cultivars: []
    planting_schemes: []
    plant_diseases: []
    irrigations: []
}

interface CompanyOption {
    id: number;
    name: string;
    description?: string | null;
}

const props = defineProps<{
    cadastralGroup: ExtendedCadastralGroup;
    availableUnits: CadastralUnit[];
    users: UserOption[] | null;
    companies: CompanyOption[] | null;
    isAdmin: boolean;
    ownedCompanyIds?: number[];
    cultivars?: Array<{
        id: number;
        name: string;
        description?: string | null;
        cultivation_name?: string | null;
    }>;
    plantingSchemes?: Array<{
        id: number;
        name: string;
        description?: string | null;
        distance?: string | null;
    }>;
    plantDiseases?: Array<{
        id: number;
        name: string;
        description?: string | null;
    }>;
    irrigations?: Array<{
        id: number;
        type: string;
        description?: string | null;
    }>;
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
    {
        title: trans('ui.edit'),
        href: route('cadastral-groups.edit', props.cadastralGroup.id),
    },
];

const page = usePage();
const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);
const mapboxMap = ref();
const map = computed(() => mapboxMap.value?.map);
const mapCenter = ref<[number, number]>([12.50, 42.50]);
const deleteDialogOpen = ref(false);
const activeTab = ref<'units' | 'manual' | 'import'>(props.cadastralGroup.creation_method);
const draw = ref<MapboxDraw | null>(null);
const currentPolygonId = ref<string | null>(null);
const polygonModified = ref(false);
const loadingUnits = ref(false);
const currentAvailableUnits = ref<CadastralUnit[]>(props.availableUnits);

const originalCultivars = JSON.stringify(props.cadastralGroup.cultivars || []);
const originalPlantingSchemes = JSON.stringify(props.cadastralGroup.planting_schemes || []);
const originalPlantDiseases = JSON.stringify(props.cadastralGroup.plant_diseases || []);
const originalIrrigations = JSON.stringify(props.cadastralGroup.irrigations || []);

// Initialize form with existing data
const form = useForm({
    name: props.cadastralGroup.name,
    description: props.cadastralGroup.description || '',
    color: props.cadastralGroup.color || '#3B82F6',
    creation_method: props.cadastralGroup.creation_method,
    user_id: props.cadastralGroup.user_id,
    company_id: props.cadastralGroup.company_id,
    unit_ids: props.cadastralGroup.creation_method === 'units'
        ? props.availableUnits.filter(unit => unit.selected).map(unit => unit.id)
        : [],
    geojson: props.cadastralGroup.original_geojson || null,
    cultivars: props.cadastralGroup.cultivars || [],
    planting_schemes: props.cadastralGroup.planting_schemes || [],
    plant_diseases: props.cadastralGroup.plant_diseases || [],
    irrigations: props.cadastralGroup.irrigations || [],
});

const selectedUnits = ref<Set<number>>(
    new Set(props.cadastralGroup.creation_method === 'units'
        ? props.availableUnits.filter(unit => unit.selected).map(unit => unit.id)
        : [])
);

const adjacencyValid = ref<boolean | null>(props.cadastralGroup.creation_method === 'units' ? true : null);
const checkingAdjacency = ref(false);
const totalArea = ref(props.cadastralGroup.total_area);
const originalCreationMethod = props.cadastralGroup.creation_method;
const originalUserId = props.cadastralGroup.user_id;
const originalCompanyId = props.cadastralGroup.company_id;

const hasChanges = computed(() => {
    if (form.creation_method !== originalCreationMethod) return true;

    if (form.name !== props.cadastralGroup.name ||
        form.description !== (props.cadastralGroup.description || '') ||
        form.color !== props.cadastralGroup.color ||
        form.user_id !== originalUserId || form.company_id !== originalCompanyId) {
        return true;
    }

    // Check for cultivar changes
    if (JSON.stringify(form.cultivars) !== originalCultivars) {
        return true;
    }

    // Check for planting scheme changes
    if (JSON.stringify(form.planting_schemes) !== originalPlantingSchemes) return true;

    // Check for plant disease changes
    if (JSON.stringify(form.plant_diseases) !== originalPlantDiseases) return true;

    // Check for irrigation changes
    if (JSON.stringify(form.irrigations) !== originalIrrigations) return true;

    if (form.creation_method === 'units') {
        const originalUnitIds = props.availableUnits
            .filter(unit => unit.selected)
            .map(unit => unit.id)
            .sort();
        const currentUnitIds = Array.from(selectedUnits.value).sort();
        return JSON.stringify(originalUnitIds) !== JSON.stringify(currentUnitIds);
    } else if (form.creation_method === 'manual' || form.creation_method === 'import') {
        return form.geojson !== props.cadastralGroup.original_geojson;
    }

    return false;
});

// Load units for a specific user
const loadUserUnits = async (userId: number) => {
    if (!props.isAdmin) return;

    loadingUnits.value = true;
    try {
        const response = await axios.get(route('api.internal.cadastral-units.get-available-units'), {
            params: {
                current_user_id: page.props.auth.user.id,
                user_id: userId,
                exclude_group_id: props.cadastralGroup.id
            }
        });

        currentAvailableUnits.value = response.data.units;

        // Reset selected units if user changed
        if (userId !== originalUserId) {
            selectedUnits.value.clear();
            form.unit_ids = [];
            adjacencyValid.value = null;
            totalArea.value = 0;
        } else {
            // Restore original selection
            selectedUnits.value = new Set(
                response.data.units
                    .filter((unit: CadastralUnit) => unit.selected)
                    .map((unit: CadastralUnit) => unit.id)
            );
            form.unit_ids = Array.from(selectedUnits.value);
            if (selectedUnits.value.size > 0) {
                checkAdjacency();
            }
        }

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

const canEditCompany = computed(() => {
    if (props.isAdmin) return true;
    const owned = props.ownedCompanyIds || [];
    if (originalCompanyId === null) {
        return owned.length > 0;
    }

    return owned.includes(originalCompanyId);
});

const availableCompanies = computed(() => {
    if (props.isAdmin) return props.companies || [];
    const owned = props.ownedCompanyIds || [];
    if (canEditCompany.value) {
        return (props.companies || []).filter(c => owned.includes(c.id));
    }
    return props.companies || [];
});

// Watch for user changes
watch(() => form.user_id, (newUserId) => {
    if (props.isAdmin && newUserId) {
        loadUserUnits(newUserId);
    }
});

watch(() => form.company_id, (newCompanyId) => {
    if (newCompanyId !== originalCompanyId && activeTab.value === 'units') {
        loadCompanyUnits(newCompanyId);
    }
});

const loadCompanyUnits = async (companyId: number | null) => {
    if (!props.isAdmin && !companyId) return;

    loadingUnits.value = true;
    try {
        const response = await axios.get(route('api.internal.cadastral-units.get-available-units'), {
            params: {
                current_user_id: page.props.auth.user.id,
                company_id: companyId,
                exclude_group_id: props.cadastralGroup.id
            }
        });

        currentAvailableUnits.value = response.data.units;

        // If company changed, reset selected units
        if (companyId !== originalCompanyId) {
            selectedUnits.value.clear();
            form.unit_ids = [];
            adjacencyValid.value = null;
            totalArea.value = 0;
        }

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

// watch tab to update creation method and clear relevant data
watch(activeTab, (newTab) => {
    if (newTab === form.creation_method) return; // No change

    const previousTab = form.creation_method;
    form.creation_method = newTab;

    // Clear data from other methods
    if (newTab !== 'units') {
        selectedUnits.value.clear();
        form.unit_ids = [];
        adjacencyValid.value = null;
    }

    // clear geojson when switching between manual and import modes
    if ((previousTab === 'manual' && newTab === 'import') ||
        (previousTab === 'import' && newTab === 'manual')) {
        form.geojson = null;
        clearDrawing();
    }

    // clear geojson when switching from units to manual/import
    if (previousTab === 'units' && (newTab === 'manual' || newTab === 'import')) {
        form.geojson = null;
        clearDrawing();
    }

    // Initialize new method
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
        if (props.isAdmin) {
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
    if (activeTab.value === 'units' && form.unit_ids.length === 0) {
        totalArea.value = 0;
        return;
    }

    if (activeTab.value === 'units') {
        try {
            const params: any = { unit_ids: form.unit_ids };
            if (props.isAdmin) {
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
    }
}, 500);

const initDrawingMap = () => {
    if (!map.value) return;

    clearMapLayers();

    if (draw.value) {
        map.value.removeControl(draw.value);
    }

    draw.value = new MapboxDraw({
        displayControlsDefault: false,
        controls: {
            polygon: true,
            trash: true
        }
    });

    map.value.addControl(draw.value, 'top-left');

    // Load existing geometry
    let geojsonToLoad = form.geojson;

    // If no geojson in form and we're in manual mode, use the boundary geometry
    if (!geojsonToLoad && activeTab.value === 'manual' && props.cadastralGroup.boundary_geometry_json) {
        geojsonToLoad = props.cadastralGroup.boundary_geometry_json;
    }

    if (geojsonToLoad) {
        try {
            const geometry = typeof geojsonToLoad === 'string' ? JSON.parse(geojsonToLoad) : geojsonToLoad;

            // Convert MultiPolygon to Polygon for drawing
            let drawGeometry;
            if (geometry.type === 'MultiPolygon') {
                drawGeometry = {
                    type: 'Polygon',
                    coordinates: geometry.coordinates[0]
                };
            } else {
                drawGeometry = geometry;
            }

            const feature = {
                type: "Feature",
                geometry: drawGeometry,
                properties: {}
            };

            const featureIds = draw.value.add(feature);
            if (featureIds && featureIds.length > 0) {
                currentPolygonId.value = featureIds[0];
            }

            // Center map on the loaded geometry
            const bounds = new mapboxgl.LngLatBounds();
            if (drawGeometry.type === 'Polygon') {
                drawGeometry.coordinates[0].forEach((coord: [number, number]) => {
                    bounds.extend(coord);
                });
            }

            if (!bounds.isEmpty()) {
                map.value.fitBounds(bounds, { padding: 100 });
            }
        } catch (e) {
            console.error('Error loading existing geometry:', e);
        }
    } else if (props.cadastralGroup.centroid_json) {
        // If no geometry but have centroid, center on it
        const centroid = JSON.parse(props.cadastralGroup.centroid_json);
        map.value.flyTo({
            center: centroid.coordinates,
            zoom: 14,
            essential: true
        });
    }

    map.value.on('draw.create', handleDrawCreate);
    map.value.on('draw.update', handleDrawUpdate);
    map.value.on('draw.delete', handleDrawDelete);

    draw.value.changeMode('simple_select');
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
    if (activeTab.value === 'manual') {
        form.geojson = null;
    }
};

const initImportMap = () => {
    if (!map.value) return;

    clearMapLayers();

    if (draw.value) {
        map.value.removeControl(draw.value);
        draw.value = null;
    }

    // display geometry if there's geojson in the form & its in import mode or this was an import group and we don't have new geojson
    let geojsonToDisplay = null;

    if (activeTab.value === 'import') {
        if (form.geojson) {
            geojsonToDisplay = form.geojson;
        } else if (props.cadastralGroup.creation_method === 'import' && props.cadastralGroup.original_geojson) {
            geojsonToDisplay = props.cadastralGroup.original_geojson;
            form.geojson = props.cadastralGroup.original_geojson;
        }
    }

    if (geojsonToDisplay) {
        try {
            const geometry = typeof geojsonToDisplay === 'string' ? JSON.parse(geojsonToDisplay) : geojsonToDisplay;

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

            // Fit bounds to geometry
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
            console.error('Error displaying geometry:', e);
        }
    } else if (props.cadastralGroup.centroid_json) {
        // If no geometry but have centroid, center on it
        const centroid = JSON.parse(props.cadastralGroup.centroid_json);
        map.value.flyTo({
            center: centroid.coordinates,
            zoom: 14,
            essential: true
        });
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
                area: unit.cadastral_area
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

    // Click handlers
    ['units-fill', 'units-selected-fill'].forEach(layerId => {
        map.value.on('click', layerId, (e: any) => {
            if (e.features[0]) {
                const properties = e.features[0].properties;
                toggleUnit(properties.id);

                new mapboxgl.Popup()
                    .setLngLat(e.lngLat)
                    .setHTML(`
                        <div class="p-2">
                            <h4 class="font-semibold">${properties.city}</h4>
                            <p class="text-sm">
                                ${trans('ui.sheet')}: ${properties.sheet}<br>
                                ${trans('ui.parcel')}: ${properties.parcel}<br>
                                ${trans('ui.area')}: ${sqmToHa(properties.area) || 'N/A'} ha
                            </p>
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

    // Remove draw events if they exist
    map.value.off('draw.create', handleDrawCreate);
    map.value.off('draw.update', handleDrawUpdate);
    map.value.off('draw.delete', handleDrawDelete);
};

watch([selectedUnits], () => {
    if (activeTab.value === 'units') {
        calculateTotalArea();
        if (map.value) {
            initUnitsMap();
        }
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
    if (activeTab.value === 'manual') {
        updateGeojsonField();
    }

    form.put(route('cadastral-groups.update', props.cadastralGroup.id), {
        preserveScroll: true,
    });
};

const confirmDelete = () => {
    deleteDialogOpen.value = true;
};

const deleteGroup = () => {
    router.delete(route('cadastral-groups.destroy', props.cadastralGroup.id), {
        preserveScroll: false,
    });
};

const downloadGeojson = () => {
    window.open(route('cadastral-groups.export-geojson', props.cadastralGroup.id), '_blank');
};

const cancel = () => {
    router.visit(route('cadastral-groups.show', props.cadastralGroup.id));
};

const isFormValid = computed(() => {
    if (!form.name) return false;

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

const handleCultivarsUpdate = (cultivars: any[]) => {
    form.cultivars = cultivars as [];
};

const handlePlantingSchemesUpdate = (schemes: any[]) => {
    form.planting_schemes = schemes as [];
};

const handlePlantDiseasesUpdate = (diseases: any[]) => {
    form.plant_diseases = diseases as [];
};

const handleIrrigationsUpdate = (irrigations: any[]) => {
    form.irrigations = irrigations as [];
};

// Initialize on mount
onMounted(() => {
    if (props.cadastralGroup.creation_method === 'units' && currentAvailableUnits.value.length > 0) {
        calculateTotalArea();
    }

    nextTick(() => {
        if (map.value) {
            map.value.on('load', () => {
                if (activeTab.value === 'units') {
                    initUnitsMap();
                } else if (activeTab.value === 'manual') {
                    initDrawingMap();
                } else {
                    initImportMap();
                }
            });
        }
    });
});
</script>

<template>

    <Head :title="`${trans('ui.edit')} - ${cadastralGroup.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.edit_cadastral_group') }}: {{ cadastralGroup.name }}
                </h1>

                <div class="flex gap-2">
                    <Button variant="outline" @click="downloadGeojson">
                        <Download class="mr-2 h-4 w-4" />
                        {{ trans('ui.export_geojson') }}
                    </Button>
                    <Button variant="outline" @click="cancel">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        {{ trans('ui.back_to_group') }}
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Form Fields -->
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.group_details') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.update_group_details') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Owner Selection (Admin only) -->
                                <div v-if="isAdmin && users" class="space-y-2">
                                    <Label for="user">
                                        <User class="inline h-4 w-4 mr-1" />
                                        {{ trans('ui.owner') || 'Owner' }}
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

                                    <Alert v-if="form.user_id !== originalUserId"
                                        class="mt-2 border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-900/20">
                                        <AlertCircle class="h-4 w-4 text-orange-600" />
                                        <AlertDescription class="text-orange-700 dark:text-orange-300">
                                            {{ trans('ui.changing_owner_warning') || 'Changing the owner will update available units' }}
                                        </AlertDescription>
                                    </Alert>
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

                                    <Alert v-if="!canEditCompany"
                                        class="mt-2 border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/20">
                                        <Building2 class="h-4 w-4 text-slate-600" />
                                        <AlertDescription class="text-slate-700 dark:text-slate-300">
                                            {{ trans('ui.company_edit_restricted') }}
                                        </AlertDescription>
                                    </Alert>

                                    <Alert v-if="form.company_id !== originalCompanyId"
                                        class="mt-2 border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-900/20">
                                        <AlertCircle class="h-4 w-4 text-orange-600" />
                                        <AlertDescription class="text-orange-700 dark:text-orange-300">
                                            {{ trans('ui.changing_company_warning') }}
                                        </AlertDescription>
                                    </Alert>

                                    <p v-if="form.errors.company_id" class="text-sm text-red-500">
                                        {{ form.errors.company_id }}
                                    </p>
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

                                <!-- Group Statistics -->
                                <div class="pt-4 border-t">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500">{{ trans('ui.creation_method') }}</p>
                                            <p class="font-semibold capitalize">{{ originalCreationMethod }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">{{ trans('ui.current_area') }}</p>
                                            <p class="text-xl font-semibold">
                                                {{ sqmToHa(cadastralGroup.total_area) }} ha</p>
                                        </div>
                                    </div>

                                    <!-- Warning if changing method -->
                                    <Alert v-if="form.creation_method !== originalCreationMethod"
                                        class="mt-4 border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-900/20">
                                        <AlertCircle class="h-4 w-4 text-orange-600" />
                                        <AlertDescription class="text-orange-700 dark:text-orange-300">
                                            {{ trans('ui.changing_creation_method_warning') }}
                                        </AlertDescription>
                                    </Alert>

                                    <!-- Current vs Original Stats -->
                                    <div v-if="hasChanges"
                                        class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            <AlertCircle class="inline h-4 w-4 mr-1" />
                                            {{ trans('ui.unsaved_changes') }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.update_geometry') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.change_group_geometry_method') }}
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
                                            <div v-if="currentAvailableUnits.length === 0" class="text-center py-8">
                                                <p class="text-gray-500">{{ trans('ui.no_available_units') }}</p>
                                            </div>

                                            <template v-else>
                                                <Alert v-if="selectedUnits.size > 1 && adjacencyValid !== null"
                                                    :class="adjacencyValid ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
                                                    class="mb-4">
                                                    <AlertCircle class="h-4 w-4"
                                                        :class="adjacencyValid ? 'text-green-600' : 'text-red-600'" />
                                                    <AlertDescription
                                                        :class="adjacencyValid ? 'text-green-800' : 'text-red-800'">
                                                        {{ adjacencyValid ? trans('ui.units_are_adjacent') : trans('ui.units_not_adjacent') }}
                                                    </AlertDescription>
                                                </Alert>

                                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                                    <div v-for="unit in currentAvailableUnits" :key="unit.id"
                                                        class="flex items-center space-x-3 p-2 border rounded hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors"
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
                                                                    {{ sqmToHa(unit.cadastral_area) }} ha</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                {{ trans('ui.selected_units') }}</p>
                                                            <p class="text-2xl font-semibold">{{ selectedUnits.size }}
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                {{ trans('ui.total_area') }}
                                                            </p>
                                                            <p class="text-2xl font-semibold">
                                                                {{ sqmToHa(totalArea) }} ha</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </template>

                                        <p v-if="form.errors.unit_ids" class="text-sm text-red-500 mt-2">
                                            {{ form.errors.unit_ids }}
                                        </p>
                                    </TabsContent>

                                    <TabsContent value="manual" class="mt-4">
                                        <Alert>
                                            <MapPin class="h-4 w-4" />
                                            <AlertDescription>
                                                {{ trans('ui.draw_polygon_on_map_edit') }}
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

                                        <div v-if="form.geojson && activeTab === 'import'"
                                            class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                            <p class="text-sm text-green-800 dark:text-green-200">
                                                {{ trans('ui.geojson_loaded_successfully') }}
                                            </p>
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
                                <CultivarSelector :available-cultivars="cultivars as any"
                                    :selected-cultivars="form.cultivars" :disabled="form.processing"
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
                                <IrrigationSelector :available-irrigations="props.irrigations || []"
                                    :selected-irrigations="form.irrigations as any[]" :disabled="form.processing"
                                    @update:selected-irrigations="handleIrrigationsUpdate" />
                            </CardContent>
                        </Card>

                        <!-- Action Buttons -->
                        <div class="flex justify-between">
                            <Button v-if="can.delete_cadastral_group" type="button" variant="destructive"
                                @click="confirmDelete" :disabled="form.processing">
                                <Trash2 class="mr-2 h-4 w-4" />
                                {{ trans('ui.delete_group') }}
                            </Button>

                            <div class="flex gap-3">
                                <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                    {{ trans('ui.cancel') }}
                                </Button>
                                <Button type="submit" :disabled="form.processing || !isFormValid || !hasChanges">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ form.processing ? trans('ui.saving') : trans('ui.save_changes') }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Map -->
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
                                <CardTitle>{{ trans('ui.planting_schemes') }}</CardTitle>
                                <CardDescription>
                                    {{ trans('ui.planting_schemes_description') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <PlantingSchemeSelector :available-schemes="plantingSchemes as any"
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
                                <PlantDiseaseSelector :available-plant-diseases="plantDiseases || []"
                                    :selected-plant-diseases="form.plant_diseases"
                                    @update:selected-plant-diseases="handlePlantDiseasesUpdate"
                                    :disabled="form.processing" />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog v-if="can.delete_cadastral_group" v-model:open="deleteDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ trans('ui.confirm_delete_group') }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ trans('ui.delete_group_description', { name: cadastralGroup.name }) }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="deleteDialogOpen = false">
                        {{ trans('ui.cancel') }}
                    </AlertDialogCancel>
                    <AlertDialogAction class="bg-destructive text-white hover:bg-destructive/90" @click="deleteGroup">
                        {{ trans('ui.delete') }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
