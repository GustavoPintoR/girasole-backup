<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import Select from '@/components/ui/select/Select.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import axios from 'axios';
import {
    Combobox,
    ComboboxAnchor, ComboboxEmpty, ComboboxGroup,
    ComboboxInput, ComboboxItem,
    ComboboxItemIndicator, ComboboxList,
    ComboboxTrigger
} from '@/components/ui/combobox';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { computed, onMounted, ref, watch } from 'vue';
import { Company, User } from '@/types';
import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();

const props = defineProps<{
    company: Company;
    users: User[];
    countries: { key: string; value: string }[];
}>();

const breadcrumbs = [
    {
        title: trans('ui.companies'),
        href: route('companies.index'),
    },
    {
        title: props.company.name,
        href: route('companies.edit', props.company.id),
    },
];

const form = useForm({
    name: props.company.name,
    description: props.company.description || '',
    owner_id: props.company.owner_id || null,
    user_ids: props.company.users?.map(u => u.id) || [],
    assign_to_all: false,
    // Fiscal Info
    fiscal_type: props.company.billing_info?.fiscal_type || '',
    fiscal_code: props.company.billing_info?.fiscal_code || '',
    vat_number: props.company.billing_info?.vat_number || '',
    sdi_code: props.company.billing_info?.sdi || '',
    // Billing Address
    state: props.company.billing_address?.state || 'IT',
    region_id: props.company.billing_address?.region_id || null,
    province_id: props.company.billing_address?.province_id || null,
    city_id: props.company.billing_address?.city_id || null,
    postal_code: props.company.billing_address?.postal_code?.code || '',
    street: props.company.billing_address?.street || '',
    street_number: props.company.billing_address?.street_number || '',
    mobile_number: props.company.billing_address?.mobile_number || '',
    // Shipping Address
    same_as_billing: props.company.shipping_address?.same_as_billing ?? true,
    shipping_region_id: props.company.shipping_address?.region_id || null,
    shipping_province_id: props.company.shipping_address?.province_id || null,
    shipping_city_id: props.company.shipping_address?.city_id || null,
    shipping_postal_code: props.company.shipping_address?.postal_code?.code || '',
    shipping_address: props.company.shipping_address?.address || '',
    shipping_name: props.company.shipping_address?.name || '',
    shipping_cellular: props.company.shipping_address?.phone_number || '',
});

const selectedOwner = computed(() => {
    return props.users.find(u => u.id === form.owner_id);
});

function submit() {
    form.put(route('companies.update', props.company.id), {
        preserveScroll: true,
    });
}

interface Region {
    id: number;
    name: string;
}
interface Province {
    id: number;
    name: string;
}
interface City {
    id: number;
    name: string;
}
interface PostalCode {
    id: number;
    code: string;
}

const regions = ref<Region[]>([]);
const provinces = ref<Province[]>([]);
const cities = ref<City[]>([]);
const postalCodes = ref<PostalCode[]>([]);
const regionQuery = ref('');
const provinceQuery = ref('');
const cityQuery = ref('');
const postalCodeQuery = ref('');
const countryQuery = ref('');

const countryOptions = computed(() =>
    countryQuery.value === ''
        ? props.countries.map(country => ({
            value: country.key,
            label: country.value,
        }))
        : props.countries
            .filter(country => country.value.toLowerCase().includes(countryQuery.value.toLowerCase()))
            .map(country => ({
                value: country.key,
                label: country.value,
            }))
);

const regionOptions = computed(() =>
    regionQuery.value === ''
        ? regions.value.map(region => ({
            value: region.id,
            label: region.name,
        }))
        : regions.value
            .filter(region => region.name.toLowerCase().includes(regionQuery.value.toLowerCase()))
            .map(region => ({
                value: region.id,
                label: region.name,
            }))
);

const provinceOptions = computed(() =>
    provinceQuery.value === ''
        ? provinces.value.map(province => ({
            value: province.id,
            label: province.name,
        }))
        : provinces.value
            .filter(province => province.name.toLowerCase().includes(provinceQuery.value.toLowerCase()))
            .map(province => ({
                value: province.id,
                label: province.name,
            }))
);

const cityOptions = computed(() =>
    cityQuery.value === ''
        ? cities.value.map(city => ({
            value: city.id,
            label: city.name,
        }))
        : cities.value
            .filter(city => city.name.toLowerCase().includes(cityQuery.value.toLowerCase()))
            .map(city => ({
                value: city.id,
                label: city.name,
            }))
);

const postalCodeOptions = computed(() =>
    postalCodeQuery.value === ''
        ? postalCodes.value.map(postalCode => ({
            value: postalCode.code,
            label: postalCode.code,
        }))
        : postalCodes.value
            .filter(postalCode => postalCode.code.toLowerCase().includes(postalCodeQuery.value.toLowerCase()))
            .map(postalCode => ({
                value: postalCode.code,
                label: postalCode.code,
            }))
);

const selectedRegion = ref<{ value: number, label: string } | null>(null);
const selectedProvince = ref<{ value: number, label: string } | null>(null);
const selectedCity = ref<{ value: number, label: string } | null>(null);
const selectedPostalCode = ref<{ value: string, label: string } | null>(null);
const selectedCountry = ref(
    props.countries.find(c => c.key === form.state)
        ? { value: form.state, label: props.countries.find(c => c.key === form.state)!.value }
        : null
);

// Shipping Address Refs
const shippingRegions = ref<Region[]>([]);
const shippingProvinces = ref<Province[]>([]);
const shippingCities = ref<City[]>([]);
const shippingPostalCodes = ref<PostalCode[]>([]);
const shippingRegionQuery = ref('');
const shippingProvinceQuery = ref('');
const shippingCityQuery = ref('');
const shippingPostalCodeQuery = ref('');

const shippingRegionOptions = computed(() =>
    shippingRegionQuery.value === ''
        ? shippingRegions.value.map(region => ({
            value: region.id,
            label: region.name,
        }))
        : shippingRegions.value
            .filter(region => region.name.toLowerCase().includes(shippingRegionQuery.value.toLowerCase()))
            .map(region => ({
                value: region.id,
                label: region.name,
            }))
);

const shippingProvinceOptions = computed(() =>
    shippingProvinceQuery.value === ''
        ? shippingProvinces.value.map(province => ({
            value: province.id,
            label: province.name,
        }))
        : shippingProvinces.value
            .filter(province => province.name.toLowerCase().includes(shippingProvinceQuery.value.toLowerCase()))
            .map(province => ({
                value: province.id,
                label: province.name,
            }))
);

const shippingCityOptions = computed(() =>
    shippingCityQuery.value === ''
        ? shippingCities.value.map(city => ({
            value: city.id,
            label: city.name,
        }))
        : shippingCities.value
            .filter(city => city.name.toLowerCase().includes(shippingCityQuery.value.toLowerCase()))
            .map(city => ({
                value: city.id,
                label: city.name,
            }))
);

const shippingPostalCodeOptions = computed(() =>
    shippingPostalCodeQuery.value === ''
        ? shippingPostalCodes.value.map(postalCode => ({
            value: postalCode.code,
            label: postalCode.code,
        }))
        : shippingPostalCodes.value
            .filter(postalCode => postalCode.code.toLowerCase().includes(shippingPostalCodeQuery.value.toLowerCase()))
            .map(postalCode => ({
                value: postalCode.code,
                label: postalCode.code,
            }))
);

const selectedShippingRegion = ref<{ value: number, label: string } | null>(null);
const selectedShippingProvince = ref<{ value: number, label: string } | null>(null);
const selectedShippingCity = ref<{ value: number, label: string } | null>(null);
const selectedShippingPostalCode = ref<{ value: string, label: string } | null>(null);

onMounted(async () => {
    try {
        const regionResponse = await axios.get(route('api.internal.regions.index'));
        regions.value = regionResponse.data;
        shippingRegions.value = regionResponse.data;

        // Initialize Billing Address
        if (form.region_id) {
            const region = regions.value.find(r => r.id === form.region_id);
            if (region) selectedRegion.value = { value: region.id, label: region.name };

            const provinceResponse = await axios.get(route('api.internal.regions.get.provinces', { region: form.region_id }));
            provinces.value = provinceResponse.data;
            if (form.province_id) {
                const province = provinces.value.find(p => p.id === form.province_id);
                if (province) selectedProvince.value = { value: province.id, label: province.name };

                const cityResponse = await axios.get(route('api.internal.provinces.get.cities', { province: form.province_id }));
                cities.value = cityResponse.data;
                if (form.city_id) {
                    const city = cities.value.find(c => c.id === form.city_id);
                    if (city) selectedCity.value = { value: city.id, label: city.name };

                    const postalCodeResponse = await axios.get(route('api.internal.cities.get.postal-codes', { city: form.city_id }));
                    postalCodes.value = postalCodeResponse.data;
                    if (form.postal_code) {
                        const postalCode = postalCodes.value.find(p => p.code === form.postal_code);
                        if (postalCode) selectedPostalCode.value = { value: postalCode.code, label: postalCode.code };
                    }
                }
            }
        }

        // Initialize Shipping Address
        if (!form.same_as_billing && form.shipping_region_id) {
            const region = shippingRegions.value.find(r => r.id === form.shipping_region_id);
            if (region) selectedShippingRegion.value = { value: region.id, label: region.name };

            const provinceResponse = await axios.get(route('api.internal.regions.get.provinces', { region: form.shipping_region_id }));
            shippingProvinces.value = provinceResponse.data;
            if (form.shipping_province_id) {
                const province = shippingProvinces.value.find(p => p.id === form.shipping_province_id);
                if (province) selectedShippingProvince.value = { value: province.id, label: province.name };

                const cityResponse = await axios.get(route('api.internal.provinces.get.cities', { province: form.shipping_province_id }));
                shippingCities.value = cityResponse.data;
                if (form.shipping_city_id) {
                    const city = shippingCities.value.find(c => c.id === form.shipping_city_id);
                    if (city) selectedShippingCity.value = { value: city.id, label: city.name };

                    const postalCodeResponse = await axios.get(route('api.internal.cities.get.postal-codes', { city: form.shipping_city_id }));
                    shippingPostalCodes.value = postalCodeResponse.data;
                    if (form.shipping_postal_code) {
                        const postalCode = shippingPostalCodes.value.find(p => p.code === form.shipping_postal_code);
                        if (postalCode) selectedShippingPostalCode.value = { value: postalCode.code, label: postalCode.code };
                    }
                }
            }
        }

    } catch (error: any) {
        console.error('Error fetching data:', error.response?.data || error.message);
    }
});

// Billing Address Watchers
const onRegionChange = async (regionId: string | number | null) => {
    if (regionId !== form.region_id) {
        form.province_id = null;
        form.city_id = null;
        form.postal_code = '';
        provinces.value = [];
        cities.value = [];
        postalCodes.value = [];
        selectedProvince.value = null;
        selectedCity.value = null;
        selectedPostalCode.value = null;
    }
    if (regionId != null) {
        try {
            const response = await axios.get(route('api.internal.regions.get.provinces', { region: regionId }));
            provinces.value = response.data;
        } catch (error: any) {
            console.error('Error fetching provinces:', error.response?.data || error.message);
        }
    }
};

const onProvinceChange = async (provinceId: string | number | null) => {
    if (provinceId !== form.province_id) {
        form.city_id = null;
        form.postal_code = '';
        cities.value = [];
        postalCodes.value = [];
        selectedCity.value = null;
        selectedPostalCode.value = null;
    }
    if (provinceId != null) {
        try {
            const response = await axios.get(route('api.internal.provinces.get.cities', { province: provinceId }));
            cities.value = response.data;
        } catch (error: any) {
            console.error('Error fetching cities:', error.response?.data || error.message);
        }
    }
};

const onCityChange = async (cityId: string | number | null) => {
    if (cityId !== form.city_id) {
        form.postal_code = '';
        postalCodes.value = [];
        selectedPostalCode.value = null;
    }
    if (cityId != null) {
        try {
            const response = await axios.get(route('api.internal.cities.get.postal-codes', { city: cityId }));
            postalCodes.value = response.data;
        } catch (error: any) {
            console.error('Error fetching postal codes:', error.response?.data || error.message);
        }
    }
};

watch(selectedCountry, (newCountry) => {
    form.state = newCountry ? newCountry.value : '';
});

watch(selectedRegion, (newRegion) => {
    const newRegionId = newRegion ? newRegion.value : null;
    if (newRegionId !== form.region_id) {
        form.region_id = newRegionId;
        onRegionChange(newRegionId);
    }
});

watch(selectedProvince, (newProvince) => {
    const newProvinceId = newProvince ? newProvince.value : null;
    if (newProvinceId !== form.province_id) {
        form.province_id = newProvinceId;
        onProvinceChange(newProvinceId);
    }
});

watch(selectedCity, (newCity) => {
    const newCityId = newCity ? newCity.value : null;
    if (newCityId !== form.city_id) {
        form.city_id = newCityId;
        onCityChange(newCityId);
    }
});

watch(selectedPostalCode, (newPostalCode) => {
    form.postal_code = newPostalCode ? newPostalCode.value : '';
});

// Shipping Address Watchers
const onShippingRegionChange = async (regionId: string | number | null) => {
    if (regionId !== form.shipping_region_id) {
        form.shipping_province_id = null;
        form.shipping_city_id = null;
        form.shipping_postal_code = '';
        shippingProvinces.value = [];
        shippingCities.value = [];
        shippingPostalCodes.value = [];
        selectedShippingProvince.value = null;
        selectedShippingCity.value = null;
        selectedShippingPostalCode.value = null;
    }
    if (regionId != null) {
        try {
            const response = await axios.get(route('api.internal.regions.get.provinces', { region: regionId }));
            shippingProvinces.value = response.data;
        } catch (error: any) {
            console.error('Error fetching provinces:', error.response?.data || error.message);
        }
    }
};

const onShippingProvinceChange = async (provinceId: string | number | null) => {
    if (provinceId !== form.shipping_province_id) {
        form.shipping_city_id = null;
        form.shipping_postal_code = '';
        shippingCities.value = [];
        shippingPostalCodes.value = [];
        selectedShippingCity.value = null;
        selectedShippingPostalCode.value = null;
    }
    if (provinceId != null) {
        try {
            const response = await axios.get(route('api.internal.provinces.get.cities', { province: provinceId }));
            shippingCities.value = response.data;
        } catch (error: any) {
            console.error('Error fetching cities:', error.response?.data || error.message);
        }
    }
};

const onShippingCityChange = async (cityId: string | number | null) => {
    if (cityId !== form.shipping_city_id) {
        form.shipping_postal_code = '';
        shippingPostalCodes.value = [];
        selectedShippingPostalCode.value = null;
    }
    if (cityId != null) {
        try {
            const response = await axios.get(route('api.internal.cities.get.postal-codes', { city: cityId }));
            shippingPostalCodes.value = response.data;
        } catch (error: any) {
            console.error('Error fetching postal codes:', error.response?.data || error.message);
        }
    }
};

watch(selectedShippingRegion, (newRegion) => {
    const newRegionId = newRegion ? newRegion.value : null;
    if (newRegionId !== form.shipping_region_id) {
        form.shipping_region_id = newRegionId;
        onShippingRegionChange(newRegionId);
    }
});

watch(selectedShippingProvince, (newProvince) => {
    const newProvinceId = newProvince ? newProvince.value : null;
    if (newProvinceId !== form.shipping_province_id) {
        form.shipping_province_id = newProvinceId;
        onShippingProvinceChange(newProvinceId);
    }
});

watch(selectedShippingCity, (newCity) => {
    const newCityId = newCity ? newCity.value : null;
    if (newCityId !== form.shipping_city_id) {
        form.shipping_city_id = newCityId;
        onShippingCityChange(newCityId);
    }
});

watch(selectedShippingPostalCode, (newPostalCode) => {
    form.shipping_postal_code = newPostalCode ? newPostalCode.value : '';
});

// User selection logic
const selectedUsersCount = computed(() => form.user_ids.length);
const allUsersSelected = computed(() => form.user_ids.length === props.users.length);

function setUserSelection(userId: number, selected: boolean) {
    if (!selected && userId === Number(form.owner_id)) {
        return;
    }
    const index = form.user_ids.indexOf(userId);
    if (selected && index === -1) {
        form.user_ids.push(userId);
    } else if (!selected && index > -1) {
        form.user_ids.splice(index, 1);
    }
}

function selectAllUsers() {
    form.user_ids = props.users.map(u => u.id);
}

function deselectAllUsers() {
    form.user_ids = form.owner_id ? [Number(form.owner_id)] : [];
}

function toggleSelectAll() {
    if (allUsersSelected.value) {
        deselectAllUsers();
    } else {
        selectAllUsers();
    }
}

const cancel = () => {
    router.visit(route('companies.index'));
};

watch(() => form.assign_to_all, (newValue) => {
    if (newValue) {
        selectAllUsers();
    } else {
        deselectAllUsers();
    }
});

watch(() => form.owner_id, (newOwnerId, oldOwnerId) => {
    if (form.assign_to_all) return;
    if (newOwnerId) {
        const ownerIdNum = Number(newOwnerId);
        if (!form.user_ids.includes(ownerIdNum)) {
            form.user_ids.push(ownerIdNum);
        }
    }

    if (oldOwnerId) {
        const oldOwnerIdNum = Number(oldOwnerId);
        if (form.user_ids.includes(oldOwnerIdNum) && oldOwnerIdNum !== Number(newOwnerId)) {
            form.user_ids.splice(form.user_ids.indexOf(oldOwnerIdNum), 1);
        }
    }
});
</script>

<template>

    <Head :title="trans('ui.update_company')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ trans('ui.update_company') }}
            </h1>

            <Card class="max-w-4xl">
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">{{ trans('ui.business_name') }} <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" type="text"
                                :placeholder="trans('ui.business_name_placeholder')"
                                :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">{{ trans('ui.company_description') }}</Label>
                            <textarea id="description" v-model="form.description"
                                :placeholder="trans('ui.company_description_placeholder')"
                                class="w-full min-h-[100px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-destructive': form.errors.description }" rows="4"></textarea>
                            <p v-if="form.errors.description" class="text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Billing Info -->
                        <div class="space-y-4 border-t pt-4">
                            <h3 class="text-lg font-medium">{{ trans('ui.billing_info') }}</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Fiscal Type -->
                                <div class="space-y-2">
                                    <Label for="fiscal_type">{{ trans('ui.type') }} <span class="text-red-500">*</span></Label>
                                    <Select v-model="form.fiscal_type" required>
                                        <SelectTrigger id="fiscal_type" class="w-full">
                                            <SelectValue :placeholder="trans('ui.select_type')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="business">{{ trans('ui.business') }}</SelectItem>
                                            <SelectItem value="sole_business">{{ trans('ui.sole_business') }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.fiscal_type" class="text-sm text-destructive">{{ form.errors.fiscal_type }}</p>
                                </div>

                                <!-- Fiscal Code -->
                                <div class="space-y-2">
                                    <Label for="fiscal_code">{{ trans('ui.fiscal_code') }} <span class="text-red-500">*</span></Label>
                                    <Input id="fiscal_code" v-model="form.fiscal_code" required />
                                    <p v-if="form.errors.fiscal_code" class="text-sm text-destructive">{{ form.errors.fiscal_code }}</p>
                                </div>

                                <!-- VAT Number -->
                                <div v-if="form.fiscal_type === 'business'" class="space-y-2">
                                    <Label for="vat_number">{{ trans('ui.vat_number') }} <span class="text-red-500">*</span></Label>
                                    <Input id="vat_number" v-model="form.vat_number" required />
                                    <p v-if="form.errors.vat_number" class="text-sm text-destructive">{{ form.errors.vat_number }}</p>
                                </div>

                                <!-- SDI Code -->
                                <div v-if="form.fiscal_type === 'business'" class="space-y-2">
                                    <Label for="sdi_code">{{ trans('ui.sdi_code') }} <span class="text-red-500">*</span></Label>
                                    <Input id="sdi_code" v-model="form.sdi_code" required />
                                    <p v-if="form.errors.sdi_code" class="text-sm text-destructive">{{ form.errors.sdi_code }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="space-y-4 border-t pt-4">
                            <h3 class="text-lg font-medium">{{ trans('ui.billing_address') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- State -->
                                <div class="space-y-2">
                                    <Label for="state">{{ trans('ui.state') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedCountry" by="value" disabled>
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between" disabled>
                                                    <span class="truncate">
                                                        {{ selectedCountry?.label ?? trans('ui.select_state') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_states')"
                                                    @update:modelValue="countryQuery = $event"
                                                    :disabled="form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_states_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="country in countryOptions"
                                                        :key="country.value"
                                                        :value="country"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ country.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedCountry?.value !== country.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.state" class="text-sm text-destructive">{{ form.errors.state }}</p>
                                </div>

                                <!-- Region -->
                                <div class="space-y-2">
                                    <Label for="region">{{ trans('ui.region') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedRegion" by="value" :disabled="form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between">
                                                    <span class="truncate">
                                                        {{ selectedRegion?.label ?? trans('ui.select_region') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_regions')"
                                                    @update:modelValue="regionQuery = $event"
                                                    :disabled="form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_regions_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="region in regionOptions"
                                                        :key="region.value"
                                                        :value="region"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ region.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedRegion?.value !== region.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.region_id" class="text-sm text-destructive">{{ form.errors.region_id }}</p>
                                </div>

                                <!-- Province -->
                                <div class="space-y-2">
                                    <Label for="province">{{ trans('ui.province') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedProvince" by="value" :disabled="!form.region_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.region_id">
                                                    <span class="truncate">
                                                        {{ selectedProvince?.label ?? trans('ui.select_province') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_provinces')"
                                                    @update:modelValue="provinceQuery = $event"
                                                    :disabled="!form.region_id || form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_provinces_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="province in provinceOptions"
                                                        :key="province.value"
                                                        :value="province"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ province.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedProvince?.value !== province.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.province_id" class="text-sm text-destructive">{{ form.errors.province_id }}</p>
                                </div>

                                <!-- City -->
                                <div class="space-y-2">
                                    <Label for="city">{{ trans('ui.city') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedCity" by="value" :disabled="!form.province_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.province_id">
                                                    <span class="truncate">
                                                        {{ selectedCity?.label ?? trans('ui.select_city') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_cities')"
                                                    @update:modelValue="cityQuery = $event"
                                                    :disabled="!form.province_id || form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_cities_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="city in cityOptions"
                                                        :key="city.value"
                                                        :value="city"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ city.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedCity?.value !== city.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.city_id" class="text-sm text-destructive">{{ form.errors.city_id }}</p>
                                </div>

                                <!-- Postal Code -->
                                <div class="space-y-2">
                                    <Label for="postal_code">{{ trans('ui.postal_code') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedPostalCode" by="value" :disabled="!form.city_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.city_id">
                                                    <span class="truncate">
                                                        {{ selectedPostalCode?.label ?? trans('ui.select_postal_code') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_postal_codes')"
                                                    @update:modelValue="postalCodeQuery = $event"
                                                    :disabled="!form.city_id || form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_postal_codes_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="postalCode in postalCodeOptions"
                                                        :key="postalCode.value"
                                                        :value="postalCode"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ postalCode.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedPostalCode?.value !== postalCode.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.postal_code" class="text-sm text-destructive">{{ form.errors.postal_code }}</p>
                                </div>

                                <!-- Street -->
                                <div class="space-y-2">
                                    <Label for="street">{{ trans('ui.street_or_square') }} <span class="text-red-500">*</span></Label>
                                    <Input id="street" v-model="form.street" required />
                                    <p v-if="form.errors.street" class="text-sm text-destructive">{{ form.errors.street }}</p>
                                </div>

                                <!-- Street Number -->
                                <div class="space-y-2">
                                    <Label for="street_number">{{ trans('ui.street_number') }} <span class="text-red-500">*</span></Label>
                                    <Input id="street_number" v-model="form.street_number" required />
                                    <p v-if="form.errors.street_number" class="text-sm text-destructive">{{ form.errors.street_number }}</p>
                                </div>

                                <!-- Mobile Number -->
                                <div class="space-y-2">
                                    <Label for="mobile_number">{{ trans('ui.mobile_phone') }} <span class="text-red-500">*</span></Label>
                                    <Input id="mobile_number" v-model="form.mobile_number" required />
                                    <p v-if="form.errors.mobile_number" class="text-sm text-destructive">{{ form.errors.mobile_number }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="space-y-4 border-t pt-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium">{{ trans('ui.shipping_address') }}</h3>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="same_as_billing" v-model="form.same_as_billing"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <Label for="same_as_billing" class="cursor-pointer font-normal">
                                        {{ trans('ui.same_as_billing_address') }}
                                    </Label>
                                </div>
                            </div>

                            <div v-if="!form.same_as_billing" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Shipping Region -->
                                <div class="space-y-2">
                                    <Label for="shipping_region">{{ trans('ui.region') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedShippingRegion" by="value" :disabled="form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between">
                                                    <span class="truncate">
                                                        {{ selectedShippingRegion?.label ?? trans('ui.select_region') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_regions')"
                                                    @update:modelValue="shippingRegionQuery = $event"
                                                    :disabled="form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_regions_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="region in shippingRegionOptions"
                                                        :key="region.value"
                                                        :value="region"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ region.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedShippingRegion?.value !== region.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.shipping_region_id" class="text-sm text-destructive">{{ form.errors.shipping_region_id }}</p>
                                </div>

                                <!-- Shipping Province -->
                                <div class="space-y-2">
                                    <Label for="shipping_province">{{ trans('ui.province') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedShippingProvince" by="value" :disabled="!form.shipping_region_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.shipping_region_id">
                                                    <span class="truncate">
                                                        {{ selectedShippingProvince?.label ?? trans('ui.select_province') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_provinces')"
                                                    @update:modelValue="shippingProvinceQuery = $event"
                                                    :disabled="!form.shipping_region_id || form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_provinces_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="province in shippingProvinceOptions"
                                                        :key="province.value"
                                                        :value="province"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ province.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedShippingProvince?.value !== province.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.shipping_province_id" class="text-sm text-destructive">{{ form.errors.shipping_province_id }}</p>
                                </div>

                                <!-- Shipping City -->
                                <div class="space-y-2">
                                    <Label for="shipping_city">{{ trans('ui.city') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedShippingCity" by="value" :disabled="!form.shipping_province_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.shipping_province_id">
                                                    <span class="truncate">
                                                        {{ selectedShippingCity?.label ?? trans('ui.select_city') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_cities')"
                                                    @update:modelValue="shippingCityQuery = $event"
                                                    :disabled="!form.shipping_province_id || form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_cities_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="city in shippingCityOptions"
                                                        :key="city.value"
                                                        :value="city"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ city.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedShippingCity?.value !== city.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.shipping_city_id" class="text-sm text-destructive">{{ form.errors.shipping_city_id }}</p>
                                </div>

                                <!-- Shipping Postal Code -->
                                <div class="space-y-2">
                                    <Label for="shipping_postal_code">{{ trans('ui.postal_code') }} <span class="text-red-500">*</span></Label>
                                    <Combobox v-model="selectedShippingPostalCode" by="value" :disabled="!form.shipping_city_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.shipping_city_id">
                                                    <span class="truncate">
                                                        {{ selectedShippingPostalCode?.label ?? trans('ui.select_postal_code') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-full relative z-10">
                                            <div class="relative w-full">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_postal_codes')"
                                                    @update:modelValue="shippingPostalCodeQuery = $event"
                                                    :disabled="!form.shipping_city_id || form.processing"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                    <Search class="size-4 text-muted-foreground" />
                                                </span>
                                            </div>
                                            <div class="w-[480px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                    {{ trans('ui.no_postal_codes_found') }}
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="postalCode in shippingPostalCodeOptions"
                                                        :key="postalCode.value"
                                                        :value="postalCode"
                                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50"
                                                    >
                                                        {{ postalCode.label }}
                                                        <ComboboxItemIndicator>
                                                            <Check :class="cn('ml-auto h-4 w-4', selectedShippingPostalCode?.value !== postalCode.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <p v-if="form.errors.shipping_postal_code" class="text-sm text-destructive">{{ form.errors.shipping_postal_code }}</p>
                                </div>

                                <!-- Shipping Address -->
                                <div class="space-y-2">
                                    <Label for="shipping_address">{{ trans('ui.address') }} <span class="text-red-500">*</span></Label>
                                    <Input id="shipping_address" v-model="form.shipping_address" required />
                                    <p v-if="form.errors.shipping_address" class="text-sm text-destructive">{{ form.errors.shipping_address }}</p>
                                </div>

                                <!-- Shipping Name -->
                                <div class="space-y-2">
                                    <Label for="shipping_name">{{ trans('ui.name') }} <span class="text-red-500">*</span></Label>
                                    <Input id="shipping_name" v-model="form.shipping_name" required />
                                    <p v-if="form.errors.shipping_name" class="text-sm text-destructive">{{ form.errors.shipping_name }}</p>
                                </div>

                                <!-- Shipping Cellular -->
                                <div class="space-y-2">
                                    <Label for="shipping_cellular">{{ trans('ui.mobile_phone') }} <span class="text-red-500">*</span></Label>
                                    <Input id="shipping_cellular" v-model="form.shipping_cellular" required />
                                    <p v-if="form.errors.shipping_cellular" class="text-sm text-destructive">{{ form.errors.shipping_cellular }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Owner select field -->
                        <div v-if="can.assign_company_owner" class="space-y-2">
                            <Label for="owner_id">{{ trans('ui.owner') }} <span class="text-red-500">*</span></Label>
                            <Select v-model="form.owner_id" :required="true">
                                <SelectTrigger id="owner_id" :class="[
                                    'w-full',
                                    form.errors.owner_id ? 'border-destructive ring-1 ring-destructive' : ''
                                ]" :aria-invalid="!!form.errors.owner_id">
                                    <SelectValue :placeholder="trans('ui.select_owner')">
                                        {{ selectedOwner ? selectedOwner.full_name : '' }} ({{ selectedOwner ? selectedOwner.email : '' }})
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="owner in props.users" :key="owner.id" :value="owner.id">
                                        {{ owner.full_name }} ({{ owner.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.owner_id" class="text-sm text-destructive">
                                {{ form.errors.owner_id }}
                            </p>
                        </div>

                        <!-- User Assignment Section -->
                        <div v-if="can.assign_company_users" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <Label>{{ trans('ui.assign_to_users') }}</Label>
                                <!-- Assign to All Checkbox -->
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="assign_to_all" v-model="form.assign_to_all"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <Label for="assign_to_all" class="cursor-pointer font-normal">
                                        {{ trans('ui.assign_to_all_users') }}
                                    </Label>
                                </div>
                            </div>

                            <!-- User Selection List (super admins only) -->
                            <div class="border rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                                <div class="flex items-center justify-between mb-2 pb-2 border-b">
                                    <span class="text-sm text-gray-600">
                                        {{ selectedUsersCount }} {{ trans('ui.users_selected') }}
                                    </span>
                                    <Button type="button" variant="ghost" size="sm" @click="toggleSelectAll"
                                        :disabled="form.assign_to_all">
                                        {{ allUsersSelected ? trans('ui.deselect_all') : trans('ui.select_all') }}
                                    </Button>
                                </div>

                                <!-- User checkboxes -->
                                <div v-for="user in props.users" :key="user.id" class="flex items-center space-x-2">
                                    <input type="checkbox" :id="`user-${user.id}`"
                                        :checked="form.user_ids.includes(user.id)"
                                        @change="(e) => setUserSelection(user.id, (e.target as HTMLInputElement).checked)"
                                        :disabled="form.assign_to_all || user.id === Number(form.owner_id)"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                                    <Label :for="`user-${user.id}`" class="cursor-pointer font-normal flex-1"
                                        :class="{ 'opacity-50': form.assign_to_all || user.id === Number(form.owner_id) }">
                                        {{ user.full_name }} ({{ user.email }})
                                        <span v-if="user.id === Number(form.owner_id)" class="text-sm text-gray-500">
                                            (Owner - automatically assigned)
                                        </span>
                                    </Label>
                                </div>
                            </div>

                            <p v-if="form.errors.user_ids" class="text-sm text-destructive">
                                {{ form.errors.user_ids }}
                            </p>
                            <!-- <p class="text-sm text-gray-500">
                                {{ trans('ui.company_assignment_help') }}
                            </p> -->
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? trans('ui.updating') || 'Updating...' : trans('ui.update') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
