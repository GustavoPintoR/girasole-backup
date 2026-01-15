<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { trans } from 'laravel-vue-i18n';
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { cn } from '@/lib/utils';
import {
    Combobox,
    ComboboxAnchor, ComboboxEmpty, ComboboxGroup,
    ComboboxInput, ComboboxItem,
    ComboboxItemIndicator, ComboboxList,
    ComboboxTrigger
} from '@/components/ui/combobox';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { BillingAddress } from '@/types';

const props = defineProps<{
    user: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        mobile_number: string;
        active: boolean;
        billing_address: BillingAddress;
    };
    countries: { key: string; value: string }[];
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: trans('ui.billing_address'),
        href: '/settings/billing/address',
    },
];

const form = useForm({
    street: props.user?.billing_address?.street || '',
    street_number: props.user?.billing_address?.street_number || '',
    city_id: props.user?.billing_address?.city_id ?? null,
    province_id: props.user?.billing_address?.province_id ?? null,
    region_id: props.user?.billing_address?.region_id ?? null,
    postal_code: props.user?.billing_address?.postal_code?.code || '',
    state: props.user?.billing_address?.state || 'IT',
});

const regions = ref([]);
const provinces = ref([]);
const cities = ref([]);
const postalCodes = ref([]);
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

const selectedRegion = ref<typeof regionOptions.value[0] | null>(null);
const selectedProvince = ref<typeof provinceOptions.value[0] | null>(null);
const selectedCity = ref<typeof cityOptions.value[0] | null>(null);
const selectedPostalCode = ref<typeof postalCodeOptions.value[0] | null>(null);
const selectedCountry = ref<{ value: string; label: string } | null>(
    props.countries.find(c => c.key === form.state)
        ? { value: form.state, label: props.countries.find(c => c.key === form.state)!.value }
        : null
);

onMounted(async () => {
    try {
        const regionResponse = await axios.get(route('api.internal.regions.index'));
        regions.value = regionResponse.data;
        if (form.region_id != null) {
            selectedRegion.value = regionOptions.value.find(region => region.value === form.region_id) || null;
        }

        if (form.region_id != null) {
            const provinceResponse = await axios.get(route('api.internal.regions.get.provinces', { region: form.region_id }));
            provinces.value = provinceResponse.data;
            if (form.province_id != null) {
                selectedProvince.value = provinceOptions.value.find(province => province.value === form.province_id) || null;
            }
        }

        if (form.province_id != null) {
            const cityResponse = await axios.get(route('api.internal.provinces.get.cities', { province: form.province_id }));
            cities.value = cityResponse.data;
            if (form.city_id != null) {
                selectedCity.value = cityOptions.value.find(city => city.value === form.city_id) || null;
            }
        }

        if (form.city_id != null) {
            const postalCodeResponse = await axios.get(route('api.internal.cities.get.postal-codes', { city: form.city_id }));
            postalCodes.value = postalCodeResponse.data;
            if (form.postal_code) {
                selectedPostalCode.value = postalCodeOptions.value.find(postalCode => postalCode.value === form.postal_code) || null;
            }
        }
    } catch (error: any) {
        console.error('Error fetching data:', error.response?.data || error.message);
    }
});

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
    form.region_id = newRegionId;
    onRegionChange(newRegionId);

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

watch(regions, () => {
    if (form.region_id != null) {
        selectedRegion.value = regionOptions.value.find(region => region.value === form.region_id) || null;
    }
});

watch(provinces, () => {
    if (form.province_id != null) {
        selectedProvince.value = provinceOptions.value.find(province => province.value === form.province_id) || null;
    }
});

watch(cities, () => {
    if (form.city_id != null) {
        selectedCity.value = cityOptions.value.find(city => city.value === form.city_id) || null;
    }
});

watch(postalCodes, () => {
    if (form.postal_code) {
        selectedPostalCode.value = postalCodeOptions.value.find(postalCode => postalCode.value === form.postal_code) || null;
    }
});

const submit = () => {
    form.patch(route('profile.billing.address.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="trans('ui.billing_address')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title="trans('ui.billing_address')" :description="trans('ui.business_address_details')" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
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
                        <InputError :message="form.errors.state" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="region">{{ trans('ui.region') }}<span class="text-red-500">*</span></Label>
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
                        <InputError :message="form.errors.region_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="province">{{ trans('ui.province') }}<span class="text-red-500">*</span></Label>
                        <Combobox v-model="selectedProvince" by="value" :disabled="!form.region_id || form.processing">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="h-11 w-full justify-between"
                                        :disabled="!form.region_id"
                                    >
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
                        <InputError :message="form.errors.province_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="city">{{ trans('ui.city') }}<span class="text-red-500">*</span></Label>
                        <Combobox v-model="selectedCity" by="value" :disabled="!form.province_id || form.processing">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="h-11 w-full justify-between"
                                        :disabled="!form.province_id"
                                    >
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
                        <InputError :message="form.errors.city_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="postal_code">{{ trans('ui.postal_code') }}<span class="text-red-500">*</span></Label>
                        <Combobox v-model="selectedPostalCode" by="value" :disabled="!form.city_id || form.processing">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="h-11 w-full justify-between"
                                        :disabled="!form.city_id"
                                    >
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
                        <InputError :message="form.errors.postal_code" />
                    </div>

                    <div class="grid gap-2">
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

                    <div class="grid gap-2">
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

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ trans('ui.save') }}</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">{{ trans('ui.saved') }}.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
