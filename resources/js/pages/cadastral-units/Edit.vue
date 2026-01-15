<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { ArrowLeft, Save, AlertCircle, Lock, ExternalLink, MapPin, Copy, FilePlus2, Trash2, User as UserIcon } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { toast } from 'vue-sonner'
import { usePermissions } from '@/composables/usePermissions';

import {
    MapboxMap,
    MapboxNavigationControl,
} from '@studiometa/vue-mapbox-gl';

import 'mapbox-gl/dist/mapbox-gl.css';
import 'vue-sonner/style.css'

const { can } = usePermissions();

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
}

const props = defineProps<{
    cadastralUnit: {
        id: number;
        user_id?: number;
        city_id: number;
        section?: string;
        sheet: string;
        parcel: string;
        sub?: string;
        category?: string;
        class?: string;
        cadastral_area?: number;
        dominical_income?: number;
        agrarian_income?: number;
        notes?: string;
        geometry_json?: string;
        centroid_json?: string;
        city: {
            id: number;
            name: string;
            cadastral_code: string;
            province: {
                id: number;
                name: string;
                code: string;
                region: {
                    id: number;
                    name: string;
                    code: string;
                };
            };
        };
    };
    regions: Array<{
        id: number;
        name: string;
        code: string;
    }>;
    isGrouped: boolean;
    cadastralGroup?: {
        id: number;
        name: string;
    } | null;
    users?: User[] | null;
}>();

const page = usePage();
const mapboxAccessToken = computed(() => page.props.mapBox.accessToken);

const breadcrumbs = [
    {
        title: trans('ui.land_registry'),
        href: route('cadastral-units.index'),
    },
    {
        title: trans('ui.edit'),
        href: route('cadastral-units.edit', props.cadastralUnit.id),
    },
];

const isReadOnly = computed(() => props.isGrouped);
const deleteDialogOpen = ref(false);

// Form setup
const form = useForm({
    user_id: props.cadastralUnit.user_id || null,
    city_id: props.cadastralUnit.city_id || null,
    section: props.cadastralUnit.section || null,
    sheet: props.cadastralUnit.sheet,
    parcel: props.cadastralUnit.parcel,
    sub: props.cadastralUnit.sub || '',
    category: props.cadastralUnit.category || '',
    class: props.cadastralUnit.class || '',
    cadastral_area: props.cadastralUnit.cadastral_area || null,
    dominical_income: props.cadastralUnit.dominical_income || null,
    agrarian_income: props.cadastralUnit.agrarian_income || null,
    notes: props.cadastralUnit.notes || '',
});

const selectedRegion = ref<number | null>(props.cadastralUnit.city.province.region.id);
const selectedProvince = ref<number | null>(props.cadastralUnit.city.province.id);
const provinces = ref<Array<{ id: number; name: string; code: string }>>([]);
const cities = ref<Array<{ id: number; name: string; cadastral_code: string }>>([]);

const mapboxMap = ref();
const mapKey = ref(0)
const map = computed(() => mapboxMap.value?.map);
const mapCenter = ref<[number, number]>([12.50, 42.50]);
const hasGeometry = ref(!!props.cadastralUnit.geometry_json);

const initializeMap = () => {
    if (!map.value) return;

    // Load existing geometry if available
    if (props.cadastralUnit.geometry_json) {
        const geometry = JSON.parse(props.cadastralUnit.geometry_json);

        // Add the polygon to the map
        map.value.on('load', () => {
            map.value.addSource('cadastral-unit', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: geometry,
                    properties: {}
                }
            });

            map.value.addLayer({
                id: 'cadastral-unit-fill',
                type: 'fill',
                source: 'cadastral-unit',
                layout: {},
                paint: {
                    'fill-color': '#088',
                    'fill-opacity': 0.5
                }
            });

            map.value.addLayer({
                id: 'cadastral-unit-outline',
                type: 'line',
                source: 'cadastral-unit',
                layout: {},
                paint: {
                    'line-color': '#088',
                    'line-width': 2
                }
            });
        });

        // Center map on the polygon
        if (props.cadastralUnit.centroid_json) {
            const centroid = JSON.parse(props.cadastralUnit.centroid_json);
            mapCenter.value = centroid.coordinates;
            map.value.flyTo({
                center: centroid.coordinates,
                zoom: 16,
                essential: true
            });
        }
    } else {
        centerMapOnCity();
    }
};

const centerMapOnCity = async () => {
    const locationString = `${props.cadastralUnit.city.name}, ${props.cadastralUnit.city.province.name}, Italia`;

    try {
        const response = await fetch(
            `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(locationString)}.json?access_token=${mapboxAccessToken.value}&limit=1`
        );

        const data = await response.json();

        if (data.features && data.features.length > 0) {
            const [lng, lat] = data.features[0].center;
            mapCenter.value = [lng, lat];

            if (map.value) {
                map.value.flyTo({
                    center: [lng, lat],
                    zoom: 14,
                    essential: true
                });
            }
        }
    } catch (error) {
        console.error('Error geocoding location:', error);
    }
};

const loadProvinces = async (regionId: number) => {
    try {
        const response = await fetch(route('api.internal.regions.get.provinces', { region: regionId }));
        provinces.value = await response.json();
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
};

const loadCities = async (provinceId: number) => {
    try {
        const response = await fetch(route('api.internal.provinces.get.cities', { province: provinceId }));
        cities.value = await response.json();
    } catch (error) {
        console.error('Error loading cities:', error);
    }
};

watch(selectedRegion, async (newRegion) => {
    if (newRegion && !isReadOnly.value) {
        await loadProvinces(newRegion);
        selectedProvince.value = null;
        cities.value = [];
        form.city_id = null;
    }
});

watch(selectedProvince, async (newProvince) => {
    if (newProvince && !isReadOnly.value) {
        await loadCities(newProvince);
        form.city_id = null;
    }
});

watch(map, (newMap) => {
    if (newMap) {
        nextTick(() => {
            initializeMap()
        })
    }
})

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    toast.info(trans('ui.copied_to_clipboard'));
};

const duplicateCadastralUnit = () => {
    const queryParams = new URLSearchParams({
        region_id: props.cadastralUnit.city.province.region.id.toString(),
        province_id: props.cadastralUnit.city.province.id.toString(),
        city_id: props.cadastralUnit.city_id.toString(),
        sheet: props.cadastralUnit.sheet,
    });

    router.visit(route('cadastral-units.create') + '?' + queryParams.toString());
};

const submit = () => {
    if (isReadOnly.value) return;

    form.put(route('cadastral-units.update', props.cadastralUnit.id), {
        preserveScroll: true,
        onSuccess: () => {
            mapKey.value++
        }
    });
};

const confirmDelete = () => {
    deleteDialogOpen.value = true;
};

const deleteCadastralUnit = () => {
    router.delete(route('cadastral-units.destroy', props.cadastralUnit.id), {
        preserveScroll: false,
    });
};

onMounted(async () => {
    await loadProvinces(selectedRegion.value!);
    await loadCities(selectedProvince.value!);
});
</script>

<template>

    <Head :title="trans('ui.edit_cadastral_unit')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.edit_cadastral_unit') }}
                </h1>

                <div class="flex gap-2">
                    <Button v-if="can.create_cadastral_unit" @click="router.visit(route('cadastral-units.create'))">
                        <FilePlus2 class="mr-2 h-4 w-4" />
                        {{ trans('ui.create_new') }}
                    </Button>

                    <Button v-if="can.create_cadastral_unit" variant="outline" @click="duplicateCadastralUnit"
                        :disabled="!cadastralUnit.city_id">
                        <Copy class="mr-2 h-4 w-4" />
                        {{ trans('ui.duplicate') }}
                    </Button>

                    <Button variant="outline" @click="router.visit(route('cadastral-units.index'))">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        {{ trans('ui.back_to_list') }}
                    </Button>
                </div>
            </div>

            <!-- Warning Alert for Grouped Units -->
            <Alert v-if="isReadOnly"
                class="mb-6 border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-900/20">
                <Lock class="h-5 w-5 text-orange-600" />
                <AlertTitle class="text-orange-800 dark:text-orange-200">
                    {{ trans('ui.cadastral_unit_locked') || 'Cadastral Unit Locked' }}
                </AlertTitle>
                <AlertDescription class="text-orange-700 dark:text-orange-300">
                    <p class="mb-2">
                        {{ trans('ui.cadastral_unit_locked_description') || 'This cadastral unit cannot be modified because it is part of a cadastral group.' }}
                    </p>
                    <p v-if="cadastralGroup">
                        {{ trans('ui.cadastral_group') || 'Cadastral Group' }}:
                        <Link :href="route('cadastral-groups.show', cadastralGroup.id)"
                            class="font-semibold text-orange-900 dark:text-orange-100 underline inline-flex items-center gap-1 hover:text-orange-700 dark:hover:text-orange-200">
                        {{ cadastralGroup.name }}
                        <ExternalLink class="h-3 w-3" />
                        </Link>
                    </p>
                    <p class="mt-2 text-sm">
                        {{ trans('ui.remove_from_group_first') || 'To edit this unit, you must first remove it from the cadastral group.' }}
                    </p>
                </AlertDescription>
            </Alert>

            <!-- Error Alert for Parcel -->
            <Alert v-if="form.errors.parcel"
                class="mb-6 border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                <AlertCircle class="h-5 w-5 text-red-600" />
                <AlertDescription class="text-red-700 dark:text-red-300">
                    {{ form.errors.parcel }}
                </AlertDescription>
            </Alert>

            <div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Form area -->
                    <div class="space-y-6">
                        <!-- User Selection (Super Admin only) -->
                        <Card v-if="props.users">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <UserIcon class="h-4 w-4" />
                                    {{ trans('ui.owner') }}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-2">
                                    <Label for="user_id">
                                        {{ trans('ui.select_owner') }}
                                    </Label>
                                    <Select :model-value="form.user_id?.toString()"
                                        @update:model-value="(v) => form.user_id = v ? parseInt(v as string) : null"
                                        :disabled="form.processing || isReadOnly">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_user')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="user in props.users" :key="user.id"
                                                :value="user.id.toString()">
                                                {{ user.last_name }} {{ user.first_name }} ({{ user.email }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.user_id" class="text-sm text-red-500">
                                        {{ form.errors.user_id }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.location_information') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="region">{{ trans('ui.region') }}</Label>
                                    <Select v-model="selectedRegion" :disabled="isReadOnly">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_region')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="region in regions" :key="region.id" :value="region.id">
                                                {{ region.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label for="province">{{ trans('ui.province') }}</Label>
                                    <Select v-model="selectedProvince" :disabled="!provinces.length || isReadOnly">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_province')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="province in provinces" :key="province.id"
                                                :value="province.id">
                                                {{ province.name }} ({{ province.code }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label for="city">{{ trans('ui.city') }} *</Label>
                                    <Select v-model="form.city_id" :disabled="!cities.length || isReadOnly">
                                        <SelectTrigger :class="{ 'border-red-500': form.errors.city_id }">
                                            <SelectValue :placeholder="trans('ui.select_city')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="city in cities" :key="city.id" :value="city.id">
                                                {{ city.name }} ({{ city.cadastral_code }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span v-if="form.errors.city_id"
                                        class="text-red-500 text-sm">{{ form.errors.city_id }}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.cadastral_data') }}</CardTitle>
                            </CardHeader>

                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="section">{{ trans('ui.section') }}</Label>
                                        <Input v-model="form.section as string" type="text" id="section"
                                            :disabled="isReadOnly" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="sheet">{{ trans('ui.sheet') }} *</Label>
                                        <Input v-model="form.sheet" type="text" maxlength="6" id="sheet"
                                            :class="{ 'border-red-500': form.errors.sheet }" :disabled="isReadOnly" />
                                        <span v-if="form.errors.sheet"
                                            class="text-red-500 text-sm">{{ form.errors.sheet }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="parcel">{{ trans('ui.parcel') }} *</Label>
                                        <Input v-model="form.parcel" type="text" maxlength="10" id="parcel"
                                            :class="{ 'border-red-500': form.errors.parcel }" :disabled="isReadOnly" />
                                        <span v-if="form.errors.parcel"
                                            class="text-red-500 text-sm">{{ form.errors.parcel }}</span>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="sub">{{ trans('ui.sub') }}</Label>
                                        <Input v-model="form.sub" type="text" id="sub" :disabled="isReadOnly" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="category">{{ trans('ui.category') }}</Label>
                                        <Input v-model="form.category" type="text" id="category"
                                            :disabled="isReadOnly" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="class">{{ trans('ui.class') }}</Label>
                                        <Input v-model="form.class" type="text" id="class" :disabled="isReadOnly" />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="cadastral_area">{{ trans('ui.cadastral_area') }} (ha)</Label>
                                    <Input v-model="form.cadastral_area as number" type="number" step="0.01"
                                        id="cadastral_area" :disabled="isReadOnly" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="dominical_income">{{ trans('ui.dominical_income') }} (€)</Label>
                                        <Input v-model="form.dominical_income as number" type="number" step="0.01"
                                            id="dominical_income" :disabled="isReadOnly" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="agrarian_income">{{ trans('ui.agrarian_income') }} (€)</Label>
                                        <Input v-model="form.agrarian_income as number" type="number" step="0.01"
                                            id="agrarian_income" :disabled="isReadOnly" />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="notes">{{ trans('ui.notes') }}</Label>
                                    <Textarea v-model="form.notes" id="notes" rows="3" :disabled="isReadOnly" />
                                </div>

                                <!-- Info Alert -->
                                <Alert class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
                                    <AlertCircle class="h-4 w-4 text-blue-600" />
                                    <AlertDescription class="text-blue-800 dark:text-blue-200">
                                        {{ trans('ui.geometry_auto_update_info') }}
                                    </AlertDescription>
                                </Alert>
                            </CardContent>
                        </Card>

                        <!-- Submit Button -->
                        <div class="flex justify-between">
                            <Button v-if="can.delete_cadastral_unit && !isGrouped" type="button" variant="destructive"
                                @click="confirmDelete" :disabled="form.processing">
                                <Trash2 class="mr-2 h-4 w-4" />
                                {{ trans('ui.delete') }}
                            </Button>

                            <div class="flex space-x-3" :class="{ 'ml-auto': !can.delete_cadastral_unit || isGrouped }">
                                <Button type="button" variant="outline"
                                    @click="router.visit(route('cadastral-units.index'))">
                                    {{ trans('ui.cancel') }}
                                </Button>
                                <Button v-if="can.update_cadastral_unit" type="button" @click="submit"
                                    :disabled="form.processing || isReadOnly">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ trans('ui.save_changes') }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Geometry area -->
                    <div class="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ trans('ui.geometry') }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Alert class="mb-4">
                                    <MapPin class="h-4 w-4" />
                                    <AlertDescription>
                                        <span v-if="!hasGeometry">
                                            {{ trans('ui.geometry_not_found') || 'No geometry found for this parcel in the cadastral dataset.' }}
                                        </span>
                                        <span v-else>
                                            {{ trans('ui.geometry_loaded') || 'Geometry loaded from cadastral dataset.' }}
                                        </span>
                                    </AlertDescription>
                                </Alert>

                                <!-- Map Container -->
                                <div
                                    class="relative h-[500px] rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    <MapboxMap :key="mapKey" :access-token="mapboxAccessToken" ref="mapboxMap"
                                        class="h-full w-full" :center="mapCenter"
                                        map-style="mapbox://styles/mapbox/satellite-v9" :zoom="16">
                                        <MapboxNavigationControl position="bottom-right" />
                                    </MapboxMap>
                                </div>

                                <!-- Geometry JSON area -->
                                <div v-if="props.cadastralUnit.geometry_json" class="space-y-4">
                                    <Card class="mt-4 max-h-96 overflow-y-auto">
                                        <CardHeader class="flex items-center justify-between">
                                            <CardTitle>{{ trans('ui.geometry_json') }}</CardTitle>

                                            <Button type="button" variant="outline" size="sm"
                                                @click="copyToClipboard(props.cadastralUnit.geometry_json)">
                                                <Copy class="mr-2 h-4 w-4" />
                                                {{ trans('ui.copy') }}
                                            </Button>
                                        </CardHeader>
                                        <CardContent>
                                            <pre
                                                class="whitespace-pre-wrap break-words prose prose-sm dark:prose-invert">{{ props.cadastralUnit.geometry_json }}</pre>
                                        </CardContent>
                                    </Card>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog v-if="can.delete_cadastral_unit" v-model:open="deleteDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ trans('ui.confirm_delete') }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ trans('ui.confirm_delete_cadastral_unit_description') || 'Are you sure you want to delete this cadastral unit? This action cannot be undone.' }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="deleteDialogOpen = false">
                        {{ trans('ui.cancel') }}
                    </AlertDialogCancel>
                    <AlertDialogAction class="bg-destructive text-white hover:bg-destructive/90"
                        @click="deleteCadastralUnit">
                        {{ trans('ui.delete') }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
