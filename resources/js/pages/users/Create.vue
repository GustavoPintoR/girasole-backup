<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { ref, computed, watch, onMounted } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { cn } from '@/lib/utils';
import axios from 'axios';
import { Country, Role } from '@/types';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs = [
    {
        title: trans('ui.users'),
        href: route('users.index'),
    },
    {
        title: trans('ui.create'),
        href: route('users.create'),
    },
];

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    mobile_phone: '',
    type: '',
    fiscal_code: '',
    vat_number: '',
    sdi_code: '',
    business_name: '',
    street: '',
    address: '',
    street_number: '',
    cellular: '',
    region_id: '',
    city_id: '',
    province_id: '',
    postal_code: '',
    shipping_region_id: '',
    shipping_province_id: '',
    shipping_postal_code: '',
    shipping_city_id: '',
    state: 'IT',
    password: '',
    password_confirmation: '',
    active: false,
    role: '',
    name: '',
    same_as_billing: false,
});

// billing
const regions = ref([]);
const provinces = ref([]);
const cities = ref([]);
const postalCodes = ref([]);
const regionQuery = ref('');
const provinceQuery = ref('');
const cityQuery = ref('');
const postalCodeQuery = ref('');
const countryQuery = ref('');

// shipping
const shippingRegions = ref([]);
const shippingProvinces = ref([]);
const shippingPostalCodes = ref([]);
const shippingCities = ref([]);
const shippingRegionQuery = ref('');
const shippingProvinceQuery = ref('');
const shippingCityQuery = ref('');
const shippingPostalCodeQuery = ref('');

const props = defineProps<{
    roles: Role[];
    countries: Country[];
}>();

const selectedCountry = ref<{ value: string; label: string } | null>(
    props.countries.find(c => c.key === form.state)
        ? { value: form.state, label: props.countries.find(c => c.key === form.state)!.value }
        : null
);

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

// billing
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

// shipping
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

// billing
const selectedRegion = ref<typeof regionOptions.value[0] | null>(null);
const selectedProvince = ref<typeof provinceOptions.value[0] | null>(null);
const selectedCity = ref<typeof cityOptions.value[0] | null>(null);
const selectedPostalCode = ref<typeof postalCodeOptions.value[0] | null>(null);

// shipping
const shippingSelectedRegion = ref<typeof shippingRegionOptions.value[0] | null>(null);
const shippingSelectedProvince = ref<typeof shippingProvinceOptions.value[0] | null>(null);
const shippingSelectedCity = ref<typeof shippingCityOptions.value[0] | null>(null);
const shippingSelectedPostalCode = ref<typeof shippingPostalCodeOptions.value[0] | null>(null);

onMounted(async () => {
    try {
        const response = await axios.get(route('api.internal.regions.index'));
        regions.value = response.data;
        shippingRegions.value = response.data;
    } catch (error) {
        console.error('Error fetching regions:', error);
    }
});

// billing
const onRegionChange = async (regionId: string) => {
    form.province_id = '';
    form.city_id = '';
    form.postal_code = '';
    provinces.value = [];
    cities.value = [];
    postalCodes.value = [];
    if (regionId) {
        try {
            const response = await axios.get(route('api.internal.regions.get.provinces', { region: regionId }));
            provinces.value = response.data;
        } catch (error) {
            console.error('Error fetching provinces:', error);
        }
    }
};

const onProvinceChange = async (provinceId: string) => {
    form.city_id = '';
    form.postal_code = '';
    cities.value = [];
    postalCodes.value = [];
    if (provinceId) {
        try {
            const response = await axios.get(route('api.internal.provinces.get.cities', { province: provinceId }));
            cities.value = response.data;
        } catch (error) {
            console.error('Error fetching cities:', error);
        }
    }
};

const onCityChange = async (cityId: string) => {
    form.postal_code = '';
    postalCodes.value = [];
    if (cityId) {
        try {
            const response = await axios.get(route('api.internal.cities.get.postal-codes', { city: cityId }));
            postalCodes.value = response.data;
        } catch (error) {
            console.error('Error fetching postal codes:', error);
        }
    }
};

// shipping
const onShippingRegionChange = async (regionId: string) => {
    form.shipping_province_id = '';
    form.shipping_city_id = '';
    form.shipping_postal_code = '';
    shippingProvinces.value = [];
    shippingCities.value = [];
    shippingPostalCodes.value = [];
    if (regionId) {
        try {
            const response = await axios.get(route('api.internal.regions.get.provinces', { region: regionId }));
            shippingProvinces.value = response.data;
        } catch (error) {
            console.error('Error fetching provinces:', error);
        }
    }
};

const onShippingProvinceChange = async (provinceId: string) => {
    form.shipping_city_id = '';
    form.shipping_postal_code = '';
    shippingCities.value = [];
    shippingPostalCodes.value = [];
    if (provinceId) {
        try {
            const response = await axios.get(route('api.internal.provinces.get.cities', { province: provinceId }));
            shippingCities.value = response.data;
        } catch (error) {
            console.error('Error fetching cities:', error);
        }
    }
};

const onShippingCityChange = async (cityId: string) => {
    form.shipping_postal_code = '';
    shippingPostalCodes.value = [];
    if (cityId) {
        try {
            const response = await axios.get(route('api.internal.cities.get.postal-codes', { city: cityId }));
            shippingPostalCodes.value = response.data;
        } catch (error) {
            console.error('Error fetching postal codes:', error);
        }
    }
};

// Watch billing address fields and update shipping fields if useBillingAddress is true
watch([() => form.street, () => form.street_number, () => form.region_id, () => form.province_id, () => form.city_id, () => form.postal_code, () => form.cellular, () => form.name], () => {
    if (form.same_as_billing) {
        form.address = form.street;
        form.shipping_region_id = form.region_id;
        form.shipping_province_id = form.province_id;
        form.shipping_city_id = form.city_id;
        form.shipping_postal_code = form.postal_code;
        form.cellular = form.mobile_phone;
        form.name = `${form.first_name} ${form.last_name}`;
        shippingSelectedRegion.value = selectedRegion.value;
        shippingSelectedProvince.value = selectedProvince.value;
        shippingSelectedCity.value = selectedCity.value;
        shippingSelectedPostalCode.value = selectedPostalCode.value;
        // Update shipping dropdown options
        shippingProvinces.value = provinces.value;
        shippingCities.value = cities.value;
        shippingPostalCodes.value = postalCodes.value;
    }
});

// Watch useBillingAddress toggle
watch(() => form.same_as_billing, (useBilling) => {
    if (useBilling) {
        // Copy billing address to shipping address
        form.address = form.street;
        form.shipping_region_id = form.region_id;
        form.shipping_province_id = form.province_id;
        form.shipping_city_id = form.city_id;
        form.shipping_postal_code = form.postal_code;
        form.cellular = form.mobile_phone;
        form.name = `${form.first_name} ${form.last_name}`;
        shippingSelectedRegion.value = selectedRegion.value;
        shippingSelectedProvince.value = selectedProvince.value;
        shippingSelectedCity.value = selectedCity.value;
        shippingSelectedPostalCode.value = selectedPostalCode.value;
        shippingProvinces.value = provinces.value;
        shippingCities.value = cities.value;
        shippingPostalCodes.value = postalCodes.value;
    } else {
        // Clear shipping address fields
        form.address = '';
        form.shipping_region_id = '';
        form.shipping_province_id = '';
        form.shipping_city_id = '';
        form.shipping_postal_code = '';
        form.cellular = '';
        form.name = '';
        shippingSelectedRegion.value = null;
        shippingSelectedProvince.value = null;
        shippingSelectedCity.value = null;
        shippingSelectedPostalCode.value = null;
        shippingProvinces.value = [];
        shippingCities.value = [];
        shippingPostalCodes.value = [];
    }
});

watch(selectedCountry, (newCountry) => {
    form.state = newCountry ? newCountry.value : '';
});

// billing
watch(selectedRegion, (newRegion) => {
    form.region_id = newRegion ? newRegion.value : '';
    onRegionChange(form.region_id);
});

watch(selectedProvince, (newProvince) => {
    form.province_id = newProvince ? newProvince.value : '';
    onProvinceChange(form.province_id);
});

watch(selectedCity, (newCity) => {
    form.city_id = newCity ? newCity.value : '';
    onCityChange(form.city_id);
});

watch(selectedPostalCode, (newPostalCode) => {
    form.postal_code = newPostalCode ? newPostalCode.value : '';
});

watch(regions, () => {
    selectedRegion.value = regionOptions.value.find(region => region.value === form.region_id) || null;
});

watch(provinces, () => {
    selectedProvince.value = provinceOptions.value.find(province => province.value === form.province_id) || null;
});

watch(cities, () => {
    selectedCity.value = cityOptions.value.find(city => city.value === form.city_id) || null;
});

watch(postalCodes, () => {
    selectedPostalCode.value = postalCodeOptions.value.find(postalCode => postalCode.value === form.postal_code) || null;
});

// shipping
watch(shippingSelectedRegion, (newRegion) => {
    if (!form.same_as_billing) {
        form.shipping_region_id = newRegion ? newRegion.value : '';
        onShippingRegionChange(form.shipping_region_id);
    }
});

watch(shippingSelectedProvince, (newProvince) => {
    if (!form.same_as_billing) {
        form.shipping_province_id = newProvince ? newProvince.value : '';
        onShippingProvinceChange(form.shipping_province_id);
    }
});

watch(shippingSelectedCity, (newCity) => {
    if (!form.same_as_billing) {
        form.shipping_city_id = newCity ? newCity.value : '';
        onShippingCityChange(form.shipping_city_id);
    }
});

watch(shippingSelectedPostalCode, (newPostalCode) => {
    if (!form.same_as_billing) {
        form.shipping_postal_code = newPostalCode ? newPostalCode.value : '';
    }
});

watch(shippingRegions, () => {
    shippingSelectedRegion.value = shippingRegionOptions.value.find(region => region.value === form.shipping_region_id) || null;
});

watch(shippingProvinces, () => {
    shippingSelectedProvince.value = shippingProvinceOptions.value.find(province => province.value === form.shipping_province_id) || null;
});

watch(shippingCities, () => {
    shippingSelectedCity.value = shippingCityOptions.value.find(city => city.value === form.shipping_city_id) || null;
});

watch(shippingPostalCodes, () => {
    shippingSelectedPostalCode.value = shippingPostalCodeOptions.value.find(postalCode => postalCode.value === form.shipping_postal_code) || null;
});

const submit = () => {
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('users.index'));
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const cancel = () => {
    router.visit(route('users.index'));
};

const isValidEmail = (email: string) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};
</script>

<template>
    <Head :title="trans('ui.user_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.user_create_header') }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Personal Information Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.company_contact_person') }}</CardTitle>
                        <CardDescription>{{ trans('ui.provide_your_details') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="first_name">{{ trans('ui.first_name') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="first_name"
                                    type="text"
                                    required
                                    autofocus
                                    v-model="form.first_name"
                                    :placeholder="trans('ui.first_name')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name">{{ trans('ui.last_name') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="last_name"
                                    type="text"
                                    required
                                    v-model="form.last_name"
                                    :placeholder="trans('ui.last_name')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="email">{{ trans('ui.email_address') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    v-model="form.email"
                                    placeholder="email@example.com"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.email" />
                                <div v-if="form.email && !isValidEmail(form.email)" class="text-sm text-red-500">
                                    {{ trans('ui.invalid_email') }}
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label for="mobile_phone">{{ trans('ui.mobile_phone') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="mobile_phone"
                                    type="tel"
                                    required
                                    v-model="form.mobile_phone"
                                    :placeholder="trans('ui.mobile_phone')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.mobile_phone" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Billing Information Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.billing_info') }}</CardTitle>
                        <CardDescription>{{ trans('ui.billing_information') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="type">{{ trans('ui.type') }}<span class="text-red-500">*</span></Label>
                                <Select v-model="form.type" :disabled="form.processing">
                                    <SelectTrigger class="h-11 w-[413px]" :data-size="11">
                                        <SelectValue :placeholder="trans('ui.select_type')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="business">{{ trans('ui.business') }}</SelectItem>
                                            <SelectItem value="sole_business">{{ trans('ui.sole_business') }}</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.type" />
                            </div>
                            <div class="space-y-2">
                                <Label for="fiscal_code">{{ trans('ui.fiscal_code') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="fiscal_code"
                                    type="text"
                                    required
                                    v-model="form.fiscal_code"
                                    :placeholder="trans('ui.fiscal_code')"
                                    class="h-11"
                                    :disabled="form.processing"
                                    maxlength="16"
                                />
                                <InputError :message="form.errors.fiscal_code" />
                            </div>
                            <div v-if="form.type === 'business'" class="space-y-2">
                                <Label for="vat_number">{{ trans('ui.vat_number') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="vat_number"
                                    type="text"
                                    required
                                    v-model="form.vat_number"
                                    :placeholder="trans('ui.vat_number')"
                                    class="h-11"
                                    :disabled="form.processing"
                                    maxlength="11"
                                />
                                <InputError :message="form.errors.vat_number" />
                            </div>
                            <div v-if="form.type === 'business'" class="space-y-2">
                                <Label for="sdi_code">{{ trans('ui.sdi_code') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="sdi_code"
                                    type="text"
                                    required
                                    v-model="form.sdi_code"
                                    :placeholder="trans('ui.sdi_code')"
                                    class="h-11"
                                    :disabled="form.processing"
                                    maxlength="7"
                                />
                                <InputError :message="form.errors.sdi_code" />
                            </div>
                            <div v-if="form.type === 'business'" class="space-y-2">
                                <Label for="business_name">{{ trans('ui.business_name') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="business_name"
                                    type="text"
                                    required
                                    v-model="form.business_name"
                                    :placeholder="trans('ui.business_name')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.business_name" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Billing Address Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.billing_address') }}</CardTitle>
                        <CardDescription>{{ trans('ui.business_address_details') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="state">{{ trans('ui.state') }}<span class="text-red-500">*</span></Label>
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
                                    <ComboboxList class="w-[462px] relative z-10">
                                        <div class="relative w-full">
                                            <ComboboxInput
                                                class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                :placeholder="trans('ui.search_states')"
                                                @update:modelValue="countryQuery = $event"
                                            />
                                            <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                <Search class="size-4 text-muted-foreground" />
                                            </span>
                                        </div>
                                        <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                <InputError :message="form.errors.region_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="region">{{ trans('ui.region') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="selectedRegion" by="value" :disabled="form.processing">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button variant="outline" class="h-11 w-[413px] justify-between">
                                                <span class="truncate">
                                                    {{ selectedRegion?.label ?? trans('ui.select_region') }}
                                                </span>
                                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                            </Button>
                                        </ComboboxTrigger>
                                    </ComboboxAnchor>
                                    <ComboboxList class="w-[413px] relative z-10">
                                        <div class="relative w-[413px]">
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
                                        <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                <InputError :message="form.errors.region_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="province">{{ trans('ui.province') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="selectedProvince" by="value" :disabled="!form.region_id || form.processing">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="h-11 w-[413px] justify-between"
                                                :disabled="!form.region_id"
                                            >
                                                <span class="truncate">
                                                    {{ selectedProvince?.label ?? trans('ui.select_province') }}
                                                </span>
                                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                            </Button>
                                        </ComboboxTrigger>
                                    </ComboboxAnchor>
                                    <ComboboxList class="w-[413px] relative z-10">
                                        <div class="relative w-[413px]">
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
                                        <div class="w-[413px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                <InputError :message="form.errors.province_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="city">{{ trans('ui.city') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="selectedCity" by="value" :disabled="!form.province_id || form.processing">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="h-11 w-[413px] justify-between"
                                                :disabled="!form.province_id"
                                            >
                                                <span class="truncate">
                                                    {{ selectedCity?.label ?? trans('ui.select_city') }}
                                                </span>
                                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                            </Button>
                                        </ComboboxTrigger>
                                    </ComboboxAnchor>
                                    <ComboboxList class="w-[413px] relative z-10">
                                        <div class="relative w-[413px]">
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
                                        <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                <InputError :message="form.errors.city_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="postal_code">{{ trans('ui.postal_code') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="selectedPostalCode" by="value" :disabled="!form.city_id || form.processing">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="h-11 w-[413px] justify-between"
                                                :disabled="!form.city_id"
                                            >
                                                <span class="truncate">
                                                    {{ selectedPostalCode?.label ?? trans('ui.select_postal_code') }}
                                                </span>
                                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                            </Button>
                                        </ComboboxTrigger>
                                    </ComboboxAnchor>
                                    <ComboboxList class="w-[413px] relative z-10">
                                        <div class="relative w-[413px]">
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
                                        <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                <InputError :message="form.errors.postal_code" />
                            </div>
                            <div class="space-y-2">
                                <Label for="street">{{ trans('ui.street_or_square') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="street"
                                    type="text"
                                    required
                                    v-model="form.street"
                                    :placeholder="trans('ui.street_or_square')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.street" />
                            </div>
                            <div class="space-y-2">
                                <Label for="street_number">{{ trans('ui.street_number') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="street_number"
                                    type="text"
                                    required
                                    v-model="form.street_number"
                                    :placeholder="trans('ui.street_number')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.street_number" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Shipping Address Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.shipping_address') }}</CardTitle>
                        <CardDescription>{{ trans('ui.shipping_address_desc') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Switch
                                    id="same_as_billing"
                                    v-model="form.same_as_billing"
                                    :disabled="form.processing"
                                    aria-label="Use billing address for shipping"
                                />
                                <Label for="same_as_billing" class="cursor-pointer">
                                    {{ trans('ui.use_billing_address') }}
                                </Label>
                            </div>
                            <div v-if="form.same_as_billing" class="text-gray-600 dark:text-gray-400">
                                {{ trans('ui.shipping_same_as_billing') }}
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="name_surname_field">{{ trans('ui.name_surname_field') }}<span class="text-red-500">*</span></Label>
                                    <Input
                                        id="name_surname_field"
                                        type="text"
                                        required
                                        v-model="form.name"
                                        :placeholder="trans('ui.name_surname_field')"
                                        class="h-11"
                                        :disabled="form.processing"
                                        aria-required="true"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="shipping_region">{{ trans('ui.region') }}<span class="text-red-500">*</span></Label>
                                    <Combobox v-model="shippingSelectedRegion" by="value" :disabled="form.processing" aria-label="Select shipping region">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button variant="outline" class="h-11 w-[413px] justify-between" :disabled="form.processing">
                                    <span class="truncate">
                                        {{ shippingSelectedRegion?.label ?? trans('ui.select_region') }}
                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-[413px] relative z-10">
                                            <div class="relative w-[413px]">
                                                <ComboboxInput
                                                    class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                    :placeholder="trans('ui.search_regions')"
                                                    @update:modelValue="shippingRegionQuery = $event"
                                                    :disabled="form.processing"
                                                    aria-label="Search shipping regions"
                                                />
                                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                    <Search class="size-4 text-muted-foreground" />
                                </span>
                                            </div>
                                            <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                                            <Check :class="cn('ml-auto h-4 w-4', shippingSelectedRegion?.value !== region.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <InputError :message="form.errors.shipping_region_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="province">{{ trans('ui.province') }}<span class="text-red-500">*</span></Label>
                                    <Combobox v-model="shippingSelectedProvince" by="value" :disabled="!form.shipping_region_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button
                                                    variant="outline"
                                                    class="h-11 w-[413px] justify-between"
                                                    :disabled="!form.shipping_region_id"
                                                >
                                                    <span class="truncate">
                                                        {{ shippingSelectedProvince?.label ?? trans('ui.select_province') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-[413px] relative z-10">
                                            <div class="relative w-[413px]">
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
                                            <div class="w-[413px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                                            <Check :class="cn('ml-auto h-4 w-4', shippingSelectedProvince?.value !== province.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <InputError :message="form.errors.shipping_province_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="city">{{ trans('ui.city') }}<span class="text-red-500">*</span></Label>
                                    <Combobox v-model="shippingSelectedCity" by="value" :disabled="!form.shipping_province_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button
                                                    variant="outline"
                                                    class="h-11 w-[413px] justify-between"
                                                    :disabled="!form.shipping_province_id"
                                                >
                                                    <span class="truncate">
                                                        {{ shippingSelectedCity?.label ?? trans('ui.select_city') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-[413px] relative z-10">
                                            <div class="relative w-[413px]">
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
                                            <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                                            <Check :class="cn('ml-auto h-4 w-4', shippingSelectedCity?.value !== city.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <InputError :message="form.errors.shipping_city_id" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="postal_code">{{ trans('ui.postal_code') }}<span class="text-red-500">*</span></Label>
                                    <Combobox v-model="shippingSelectedPostalCode" by="value" :disabled="!form.shipping_city_id || form.processing">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button
                                                    variant="outline"
                                                    class="h-11 w-[413px] justify-between"
                                                    :disabled="!form.shipping_city_id"
                                                >
                                                    <span class="truncate">
                                                        {{ shippingSelectedPostalCode?.label ?? trans('ui.select_postal_code') }}
                                                    </span>
                                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-[413px] relative z-10">
                                            <div class="relative w-[413px]">
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
                                            <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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
                                                            <Check :class="cn('ml-auto h-4 w-4', shippingSelectedPostalCode?.value !== postalCode.value && 'opacity-0')" />
                                                        </ComboboxItemIndicator>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </div>
                                        </ComboboxList>
                                    </Combobox>
                                    <InputError :message="form.errors.shipping_postal_code" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="address">{{ trans('ui.address_field') }}<span class="text-red-500">*</span></Label>
                                    <Textarea
                                        id="address"
                                        required
                                        v-model="form.address"
                                        :placeholder="trans('ui.address')"
                                        class="h-11"
                                        :disabled="form.processing"
                                    />
                                    <InputError :message="form.errors.address" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="cellular">{{ trans('ui.cellular') }}<span class="text-red-500">*</span></Label>
                                    <Input
                                        id="cellular"
                                        type="text"
                                        required
                                        v-model="form.cellular"
                                        :placeholder="trans('ui.cellular')"
                                        class="h-11"
                                        :disabled="form.processing"
                                    />
                                    <InputError :message="form.errors.cellular" />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Security Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.security') }}</CardTitle>
                        <CardDescription>{{ trans('ui.security_access_update') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="type">{{ trans('ui.role') }}<span class="text-red-500">*</span></Label>
                                <Select v-model="form.role" :disabled="form.processing">
                                    <SelectTrigger class="h-11 w-[413px]" :data-size="11">
                                        <SelectValue :placeholder="trans('ui.select_type')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="role in props.roles" :key="role.name" :value="role.name">
                                                {{ trans(`ui.${role.name}`) }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.role" />
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <Switch
                                        id="active"
                                        v-model="form.active"
                                        :disabled="form.processing"
                                    />
                                    <Label for="active" class="cursor-pointer">
                                        {{ trans('ui.active') }}
                                    </Label>
                                </div>
                                <InputError :message="form.errors.active" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password">{{ trans('ui.password') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    v-model="form.password"
                                    :placeholder="trans('ui.password')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">{{ trans('ui.confirm_password') }}<span class="text-red-500">*</span></Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
                                    v-model="form.password_confirmation"
                                    :placeholder="trans('ui.confirm_password')"
                                    class="h-11"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.password_confirmation" />
                                <div v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" class="text-sm text-red-500">
                                    {{ trans('ui.password_mismatch') }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-2 pt-4">
                    <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                        {{ trans('ui.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? trans('ui.creating') : trans('ui.create_user') }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
