<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertCircle, MapPin, Loader2, Copy, User as UserIcon } from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import { trans } from 'laravel-vue-i18n';
import axios from 'axios';

interface Region {
    id: number;
    name: string;
    code: string;
}

interface Province {
    id: number;
    name: string;
    code: string;
    region_id: number;
}

interface City {
    id: number;
    name: string;
    cadastral_code: string;
    province_id: number;
    region_id: number;
}

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
}

interface Props {
    regions: Region[];
    users?: User[] | null;
}

const props = defineProps<Props>();

// get query params from url
const urlParams = new URLSearchParams(window.location.search);
const prefilledRegionId = urlParams.get('region_id') ? parseInt(urlParams.get('region_id')!) : null;
const prefilledProvinceId = urlParams.get('province_id') ? parseInt(urlParams.get('province_id')!) : null;
const prefilledCityId = urlParams.get('city_id') ? parseInt(urlParams.get('city_id')!) : null;
const prefilledSheet = urlParams.get('sheet') || null;

const isDuplicate = !!(prefilledRegionId && prefilledProvinceId && prefilledCityId);

const breadcrumbs = [
    {
        title: trans('ui.land_registry'),
        href: route('cadastral-units.index'),
    },
    {
        title: isDuplicate ? trans('ui.duplicate') : trans('ui.create'),
        href: route('cadastral-units.create'),
    },
];

const selectedRegionId = ref<number | null>(prefilledRegionId);
const selectedProvinceId = ref<number | null>(prefilledProvinceId);
const provinces = ref<Province[]>([]);
const cities = ref<City[]>([]);
const loadingProvinces = ref(false);
const loadingCities = ref(false);

const form = useForm({
    user_id: null as number | null,
    city_id: prefilledCityId,
    section: null as string | null,
    sheet: prefilledSheet,
    parcel: null as string | null,
    sub: '',
    category: '',
    class: '',
    cadastral_area: null as number | null,
    dominical_income: null as number | null,
    agrarian_income: null as number | null,
    notes: '',
});

// load initial data if duplicating
onMounted(async () => {
    if (isDuplicate && prefilledRegionId && prefilledProvinceId && prefilledCityId) {
        // Load provinces for the pre-selected region
        loadingProvinces.value = true;
        try {
            const response = await axios.get(route('api.internal.regions.get.provinces', { region: prefilledRegionId }));
            provinces.value = response.data;
        } catch (error) {
            console.error('Error loading provinces:', error);
            provinces.value = [];
        } finally {
            loadingProvinces.value = false;
        }

        // Load cities for the pre-selected province
        loadingCities.value = true;
        try {
            const response = await axios.get(route('api.internal.provinces.get.cities', { province: prefilledProvinceId }));
            cities.value = response.data;
        } catch (error) {
            console.error('Error loading cities:', error);
            cities.value = [];
        } finally {
            loadingCities.value = false;
        }
    }
});

// Watch for region changes
watch(selectedRegionId, async (newRegionId, oldRegionId) => {
    if (isDuplicate && oldRegionId === null && newRegionId === prefilledRegionId) {
        return;
    }

    if (newRegionId) {
        // reset fields only if not initial load
        if (!(isDuplicate && oldRegionId === null)) {
            selectedProvinceId.value = null;
            form.city_id = null;
            cities.value = [];
        }

        // Load provinces for selected region
        loadingProvinces.value = true;
        try {
            const response = await axios.get(route('api.internal.regions.get.provinces', { region: newRegionId }));
            provinces.value = response.data;
        } catch (error) {
            console.error('Error loading provinces:', error);
            provinces.value = [];
        } finally {
            loadingProvinces.value = false;
        }
    } else {
        provinces.value = [];
        selectedProvinceId.value = null;
        form.city_id = null;
        cities.value = [];
    }
});

// Watch for province changes
watch(selectedProvinceId, async (newProvinceId, oldProvinceId) => {
    if (isDuplicate && oldProvinceId === null && newProvinceId === prefilledProvinceId) {
        return;
    }

    if (newProvinceId) {
        if (!(isDuplicate && oldProvinceId === null)) {
            form.city_id = null;
        }

        // Load cities for selected province
        loadingCities.value = true;
        try {
            const response = await axios.get(route('api.internal.provinces.get.cities', { province: newProvinceId }));
            cities.value = response.data;
        } catch (error) {
            console.error('Error loading cities:', error);
            cities.value = [];
        } finally {
            loadingCities.value = false;
        }
    } else {
        form.city_id = null;
        cities.value = [];
    }
});

const handleRegionSelect = (value: string | null) => {
    selectedRegionId.value = value ? parseInt(value) : null;
};

const handleProvinceSelect = (value: string | null) => {
    selectedProvinceId.value = value ? parseInt(value) : null;
};

const handleCitySelect = (value: string | null) => {
    form.city_id = value ? parseInt(value) : null;
};

const submit = () => {
    form.post(route('cadastral-units.store'), {
        preserveScroll: true,
        onSuccess: () => {
        },
    });
};

const cancel = () => {
    router.visit(route('cadastral-units.index'));
};

const hasRequiredFields = computed(() => {
    return form.city_id && form.sheet !== null && form.parcel !== null;
});
</script>

<template>

    <Head :title="isDuplicate ? trans('ui.duplicate_cadastral_unit') : trans('ui.create_cadastral_unit')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ isDuplicate ? trans('ui.duplicate_cadastral_unit') : trans('ui.create_cadastral_unit') }}
                </h1>
            </div>

            <!-- Duplication Notice -->
            <Alert v-if="isDuplicate" class="mb-6 border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
                <Copy class="h-4 w-4 text-blue-600" />
                <AlertDescription class="text-blue-800 dark:text-blue-200">
                    {{ trans('ui.duplicating_from_existing') }}
                </AlertDescription>
            </Alert>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.cadastral_unit_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.cadastral_unit_information') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- User Selection (Super Admin only) -->
                        <div v-if="props.users" class="space-y-4 pb-4 border-b">
                            <h3 class="text-lg font-medium flex items-center gap-2">
                                <UserIcon class="h-4 w-4" />
                                {{ trans('ui.owner') }}
                            </h3>
                            <div class="space-y-2">
                                <Label for="user_id">
                                    {{ trans('ui.select_owner') }}
                                </Label>
                                <Select :model-value="form.user_id?.toString()"
                                    @update:model-value="(v) => form.user_id = v ? parseInt(v as string) : null"
                                    :disabled="form.processing">
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
                        </div>

                        <!-- Location Section -->
                        <div class="space-y-4 pb-4 border-b">
                            <h3 class="text-lg font-medium flex items-center gap-2">
                                <MapPin class="h-4 w-4" />
                                {{ trans('ui.location_information') }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Region -->
                                <div class="space-y-2">
                                    <Label for="region">
                                        {{ trans('ui.region') }} <span class="text-red-500">*</span>
                                    </Label>
                                    <Select @update:model-value="handleRegionSelect as (value: string | null) => void"
                                        :model-value="selectedRegionId?.toString()" :disabled="form.processing">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_region')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="region in props.regions" :key="region.id"
                                                :value="region.id.toString()">
                                                {{ region.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Province -->
                                <div class="space-y-2">
                                    <Label for="province">
                                        {{ trans('ui.province') }} <span class="text-red-500">*</span>
                                    </Label>
                                    <Select @update:model-value="handleProvinceSelect as (value: string | null) => void"
                                        :model-value="selectedProvinceId?.toString()"
                                        :disabled="!selectedRegionId || loadingProvinces || form.processing">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="trans('ui.select_province')">
                                                <span v-if="loadingProvinces" class="flex items-center gap-2">
                                                    <Loader2 class="h-4 w-4 animate-spin" />
                                                    {{ trans('ui.loading') }}
                                                </span>
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="province in provinces" :key="province.id"
                                                :value="province.id.toString()">
                                                {{ province.name }} ({{ province.code }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="!selectedRegionId && selectedProvinceId === null"
                                        class="text-sm text-gray-500">
                                        {{ trans('ui.select_region_first') }}
                                    </p>
                                </div>

                                <!-- City -->
                                <div class="space-y-2">
                                    <Label for="city_id">
                                        {{ trans('ui.city') }} <span class="text-red-500">*</span>
                                    </Label>
                                    <Select @update:model-value="handleCitySelect as (value: string | null) => void"
                                        :model-value="form.city_id?.toString()"
                                        :disabled="!selectedProvinceId || loadingCities || form.processing">
                                        <SelectTrigger :class="{ 'border-red-500': form.errors.city_id }">
                                            <SelectValue :placeholder="trans('ui.select_city')">
                                                <span v-if="loadingCities" class="flex items-center gap-2">
                                                    <Loader2 class="h-4 w-4 animate-spin" />
                                                    {{ trans('ui.loading') }}
                                                </span>
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="city in cities" :key="city.id"
                                                :value="city.id.toString()">
                                                {{ city.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="!selectedProvinceId && form.city_id === null"
                                        class="text-sm text-gray-500">
                                        {{ trans('ui.select_province_first') }}
                                    </p>
                                    <p v-if="form.errors.city_id" class="text-sm text-red-500">
                                        {{ form.errors.city_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Cadastral Identification Section -->
                        <div class="space-y-4 pb-4 border-b">
                            <h3 class="text-lg font-medium">{{ trans('ui.cadastral_identification') }}</h3>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Section -->
                                <div class="space-y-2">
                                    <Label for="section">
                                        {{ trans('ui.section') }}
                                    </Label>
                                    <Input id="section" v-model="form.section as string" type="text"
                                        :placeholder="trans('ui.optional')"
                                        :class="{ 'border-red-500': form.errors.section }"
                                        :disabled="form.processing" />
                                    <p v-if="form.errors.section" class="text-sm text-red-500">
                                        {{ form.errors.section }}
                                    </p>
                                </div>

                                <!-- Sheet -->
                                <div class="space-y-2">
                                    <Label for="sheet">
                                        {{ trans('ui.sheet') }} <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="sheet" v-model="form.sheet as string" type="text"
                                        placeholder="e.g., 012345" maxlength="6"
                                        :class="{ 'border-red-500': form.errors.sheet }" :disabled="form.processing" />
                                    <p v-if="form.errors.sheet" class="text-sm text-red-500">
                                        {{ form.errors.sheet }}
                                    </p>
                                </div>

                                <!-- Parcel -->
                                <div class="space-y-2">
                                    <Label for="parcel">
                                        {{ trans('ui.parcel') }} <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="parcel" v-model="form.parcel as string" type="text"
                                        placeholder="e.g., 456" maxlength="10"
                                        :class="{ 'border-red-500': form.errors.parcel }" :disabled="form.processing" />
                                    <p v-if="form.errors.parcel" class="text-sm text-red-500">
                                        {{ form.errors.parcel }}
                                    </p>
                                </div>

                                <!-- Sub -->
                                <div class="space-y-2">
                                    <Label for="sub">
                                        {{ trans('ui.sub') }}
                                    </Label>
                                    <Input id="sub" v-model="form.sub" type="text" :placeholder="trans('ui.optional')"
                                        :class="{ 'border-red-500': form.errors.sub }" :disabled="form.processing" />
                                    <p v-if="form.errors.sub" class="text-sm text-red-500">
                                        {{ form.errors.sub }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Classification Section -->
                        <div class="space-y-4 pb-4 border-b">
                            <h3 class="text-lg font-medium">{{ trans('ui.classification') }}</h3>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Category -->
                                <div class="space-y-2">
                                    <Label for="category">
                                        {{ trans('ui.category') }}
                                    </Label>
                                    <Input id="category" v-model="form.category" type="text"
                                        :placeholder="trans('ui.optional')"
                                        :class="{ 'border-red-500': form.errors.category }"
                                        :disabled="form.processing" />
                                    <p v-if="form.errors.category" class="text-sm text-red-500">
                                        {{ form.errors.category }}
                                    </p>
                                </div>

                                <!-- Class -->
                                <div class="space-y-2">
                                    <Label for="class">
                                        {{ trans('ui.class') }}
                                    </Label>
                                    <Input id="class" v-model="form.class" type="text"
                                        :placeholder="trans('ui.optional')"
                                        :class="{ 'border-red-500': form.errors.class }" :disabled="form.processing" />
                                    <p v-if="form.errors.class" class="text-sm text-red-500">
                                        {{ form.errors.class }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information Section -->
                        <div class="space-y-4 pb-4 border-b">
                            <h3 class="text-lg font-medium">{{ trans('ui.financial_information') }}</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Cadastral Area -->
                                <div class="space-y-2">
                                    <Label for="cadastral_area">
                                        {{ trans('ui.cadastral_area') }} (ha)
                                    </Label>
                                    <Input id="cadastral_area" v-model.number="form.cadastral_area as number"
                                        type="number" step="0.01" :placeholder="trans('ui.optional')"
                                        :class="{ 'border-red-500': form.errors.cadastral_area }"
                                        :disabled="form.processing" />
                                    <p v-if="form.errors.cadastral_area" class="text-sm text-red-500">
                                        {{ form.errors.cadastral_area }}
                                    </p>
                                </div>

                                <!-- Dominical Income -->
                                <div class="space-y-2">
                                    <Label for="dominical_income">
                                        {{ trans('ui.dominical_income') }} (€)
                                    </Label>
                                    <Input id="dominical_income" v-model.number="form.dominical_income as number"
                                        type="number" step="0.01" :placeholder="trans('ui.optional')"
                                        :class="{ 'border-red-500': form.errors.dominical_income }"
                                        :disabled="form.processing" />
                                    <p v-if="form.errors.dominical_income" class="text-sm text-red-500">
                                        {{ form.errors.dominical_income }}
                                    </p>
                                </div>

                                <!-- Agrarian Income -->
                                <div class="space-y-2">
                                    <Label for="agrarian_income">
                                        {{ trans('ui.agrarian_income') }} (€)
                                    </Label>
                                    <Input id="agrarian_income" v-model.number="form.agrarian_income as number"
                                        type="number" step="0.01" :placeholder="trans('ui.optional')"
                                        :class="{ 'border-red-500': form.errors.agrarian_income }"
                                        :disabled="form.processing" />
                                    <p v-if="form.errors.agrarian_income" class="text-sm text-red-500">
                                        {{ form.errors.agrarian_income }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium">{{ trans('ui.additional_information') }}</h3>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <Label for="notes">
                                    {{ trans('ui.notes') }}
                                </Label>
                                <Textarea id="notes" v-model="form.notes"
                                    :placeholder="trans('ui.add_any_additional_notes')" :rows="4"
                                    :class="{ 'border-red-500': form.errors.notes }" :disabled="form.processing" />
                                <p v-if="form.errors.notes" class="text-sm text-red-500">
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <Alert v-if="form.errors.parcel"
                            class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                            <AlertCircle class="h-4 w-4 text-red-600" />
                            <AlertDescription class="text-red-800 dark:text-red-200">
                                {{ form.errors.parcel }}
                            </AlertDescription>
                        </Alert>

                        <Alert class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
                            <AlertCircle class="h-4 w-4 text-blue-600" />
                            <AlertDescription class="text-blue-800 dark:text-blue-200">
                                {{ trans('ui.geometry_auto_update_info') }}
                            </AlertDescription>
                        </Alert>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing || !hasRequiredFields">
                                {{ form.processing ? trans('ui.creating') : trans('ui.create') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
