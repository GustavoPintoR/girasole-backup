<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, ShippingAddress } from '@/types';
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    user: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        mobile_number: string;
        active: boolean;
        shipping_address: ShippingAddress;
    };
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: trans('ui.shipping_address'),
        href: '/settings/shipping/address',
    },
];

const form = useForm({
    name: props.user?.shipping_address?.name || '',
    address: props.user?.shipping_address?.address || '',
    cellular: props.user?.shipping_address?.phone_number || '',
    shipping_region_id: props.user?.shipping_address?.region_id ?? null,
    shipping_province_id: props.user?.shipping_address?.province_id ?? null,
    shipping_city_id: props.user?.shipping_address?.city_id ?? null,
    shipping_postal_code: props.user?.shipping_address?.postal_code?.code || '',
    same_as_billing: props.user?.shipping_address?.same_as_billing,
});

const shippingRegions = ref([]);
const shippingProvinces = ref([]);
const shippingCities = ref([]);
const shippingPostalCodes = ref([]);
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

const shippingSelectedRegion = ref<typeof shippingRegionOptions.value[0] | null>(null);
const shippingSelectedProvince = ref<typeof shippingProvinceOptions.value[0] | null>(null);
const shippingSelectedCity = ref<typeof shippingCityOptions.value[0] | null>(null);
const shippingSelectedPostalCode = ref<typeof shippingPostalCodeOptions.value[0] | null>(null);

onMounted(async () => {
    try {
        const regionResponse = await axios.get(route('api.internal.regions.index'));
        shippingRegions.value = regionResponse.data;

        if (form.shipping_region_id != null) {
            shippingSelectedRegion.value = shippingRegionOptions.value.find(region => region.value === form.shipping_region_id) || null;
            const shippingProvinceResponse = await axios.get(route('api.internal.regions.get.provinces', { region: form.shipping_region_id }));
            shippingProvinces.value = shippingProvinceResponse.data;
        }

        if (form.shipping_province_id != null) {
            shippingSelectedProvince.value = shippingProvinceOptions.value.find(province => province.value === form.shipping_province_id) || null;
            const shippingCityResponse = await axios.get(route('api.internal.provinces.get.cities', { province: form.shipping_province_id }));
            shippingCities.value = shippingCityResponse.data;
        }

        if (form.shipping_city_id != null) {
            shippingSelectedCity.value = shippingCityOptions.value.find(city => city.value === form.shipping_city_id) || null;
            const shippingPostalCodeResponse = await axios.get(route('api.internal.cities.get.postal-codes', { city: form.shipping_city_id }));
            shippingPostalCodes.value = shippingPostalCodeResponse.data;
        }

        if (form.shipping_postal_code) {
            shippingSelectedPostalCode.value = shippingPostalCodeOptions.value.find(postalCode => postalCode.value === form.shipping_postal_code) || null;
        }
    } catch (error: any) {
        console.error('Error fetching data:', error.response?.data || error.message);
    }
});

const onShippingRegionChange = async (regionId: string | number | null) => {
    if (regionId !== form.shipping_region_id) {
        form.shipping_province_id = null;
        form.shipping_city_id = null;
        form.shipping_postal_code = '';
        shippingProvinces.value = [];
        shippingCities.value = [];
        shippingPostalCodes.value = [];
        shippingSelectedProvince.value = null;
        shippingSelectedCity.value = null;
        shippingSelectedPostalCode.value = null;
    }
    if (regionId != null) {
        try {
            const response = await axios.get(route('api.internal.regions.get.provinces', { region: regionId }));
            shippingProvinces.value = response.data;
        } catch (error: any) {
            console.error('Error fetching shipping provinces:', error.response?.data || error.message);
        }
    }
};

const onShippingProvinceChange = async (provinceId: string | number | null) => {
    if (provinceId !== form.shipping_province_id) {
        form.shipping_city_id = null;
        form.shipping_postal_code = '';
        shippingCities.value = [];
        shippingPostalCodes.value = [];
        shippingSelectedCity.value = null;
        shippingSelectedPostalCode.value = null;
    }
    if (provinceId != null) {
        try {
            const response = await axios.get(route('api.internal.provinces.get.cities', { province: provinceId }));
            shippingCities.value = response.data;
        } catch (error: any) {
            console.error('Error fetching shipping cities:', error.response?.data || error.message);
        }
    }
};

const onShippingCityChange = async (cityId: string | number | null) => {
    if (cityId !== form.shipping_city_id) {
        form.shipping_postal_code = '';
        shippingPostalCodes.value = [];
        shippingSelectedPostalCode.value = null;
    }
    if (cityId != null) {
        try {
            const response = await axios.get(route('api.internal.cities.get.postal-codes', { city: cityId }));
            shippingPostalCodes.value = response.data;
        } catch (error: any) {
            console.error('Error fetching shipping postal codes:', error.response?.data || error.message);
        }
    }
};

watch(shippingSelectedRegion, (newRegion) => {
    if (!form.same_as_billing) {
        const newRegionId = newRegion ? newRegion.value : null;
        if (newRegionId !== form.shipping_region_id) {
            form.shipping_region_id = newRegionId;
            onShippingRegionChange(newRegionId);
        }
    }
});

watch(shippingSelectedProvince, (newProvince) => {
    if (!form.same_as_billing) {
        const newProvinceId = newProvince ? newProvince.value : null;
        if (newProvinceId !== form.shipping_province_id) {
            form.shipping_province_id = newProvinceId;
            onShippingProvinceChange(newProvinceId);
        }
    }
});

watch(shippingSelectedCity, (newCity) => {
    if (!form.same_as_billing) {
        const newCityId = newCity ? newCity.value : null;
        if (newCityId !== form.shipping_city_id) {
            form.shipping_city_id = newCityId;
            onShippingCityChange(newCityId);
        }
    }
});

watch(shippingSelectedPostalCode, (newPostalCode) => {
    if (!form.same_as_billing) {
        form.shipping_postal_code = newPostalCode ? newPostalCode.value : '';
    }
});

watch(shippingRegions, () => {
    if (form.shipping_region_id != null && !form.same_as_billing) {
        shippingSelectedRegion.value = shippingRegionOptions.value.find(region => region.value === form.shipping_region_id) || null;
    }
});

watch(shippingProvinces, () => {
    if (form.shipping_province_id != null && !form.same_as_billing) {
        shippingSelectedProvince.value = shippingProvinceOptions.value.find(province => province.value === form.shipping_province_id) || null;
    }
});

watch(shippingCities, () => {
    if (form.shipping_city_id != null && !form.same_as_billing) {
        shippingSelectedCity.value = shippingCityOptions.value.find(city => city.value === form.shipping_city_id) || null;
    }
});

watch(shippingPostalCodes, () => {
    if (form.shipping_postal_code && !form.same_as_billing) {
        shippingSelectedPostalCode.value = shippingPostalCodeOptions.value.find(postalCode => postalCode.value === form.shipping_postal_code) || null;
    }
});

const submit = () => {
    form.patch(route('profile.shipping.address.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="trans('ui.shipping_address')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title="trans('ui.shipping_address')" :description="trans('ui.shipping_address_desc')" />

                <form @submit.prevent="submit" class="space-y-6">
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
                            {{ trans('ui.same_as_billing_address') }}
                        </div>
                        <div v-else class="grid gap-6">
                            <div class="grid gap-2">
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
                            <div class="grid gap-2">
                                <Label for="shipping_region">{{ trans('ui.region') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="shippingSelectedRegion" by="value" :disabled="form.processing" aria-label="Select shipping region">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button variant="outline" class="h-11 w-full justify-between" :disabled="form.processing">
                                                    <span class="truncate">
                                                        {{ shippingSelectedRegion?.label ?? trans('ui.select_region') }}
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
                                                aria-label="Search shipping regions"
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
                                                        <Check :class="cn('ml-auto h-4 w-4', shippingSelectedRegion?.value !== region.value && 'opacity-0')" />
                                                    </ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </div>
                                    </ComboboxList>
                                </Combobox>
                                <InputError :message="form.errors.shipping_region_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="province">{{ trans('ui.province') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="shippingSelectedProvince" by="value" :disabled="!form.shipping_region_id || form.processing">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="h-11 w-full justify-between"
                                                :disabled="!form.shipping_region_id"
                                            >
                                                    <span class="truncate">
                                                        {{ shippingSelectedProvince?.label ?? trans('ui.select_province') }}
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
                                                        <Check :class="cn('ml-auto h-4 w-4', shippingSelectedProvince?.value !== province.value && 'opacity-0')" />
                                                    </ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </div>
                                    </ComboboxList>
                                </Combobox>
                                <InputError :message="form.errors.shipping_province_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="city">{{ trans('ui.city') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="shippingSelectedCity" by="value" :disabled="!form.shipping_province_id || form.processing">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="h-11 w-full justify-between"
                                                :disabled="!form.shipping_province_id"
                                            >
                                                    <span class="truncate">
                                                        {{ shippingSelectedCity?.label ?? trans('ui.select_city') }}
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
                                                        <Check :class="cn('ml-auto h-4 w-4', shippingSelectedCity?.value !== city.value && 'opacity-0')" />
                                                    </ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </div>
                                    </ComboboxList>
                                </Combobox>
                                <InputError :message="form.errors.shipping_city_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="postal_code">{{ trans('ui.postal_code') }}<span class="text-red-500">*</span></Label>
                                <Combobox v-model="shippingSelectedPostalCode" by="value" :disabled="!form.shipping_city_id || form.processing">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="h-11 w-full justify-between"
                                                :disabled="!form.shipping_city_id"
                                            >
                                                    <span class="truncate">
                                                        {{ shippingSelectedPostalCode?.label ?? trans('ui.select_postal_code') }}
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
                                                        <Check :class="cn('ml-auto h-4 w-4', shippingSelectedPostalCode?.value !== postalCode.value && 'opacity-0')" />
                                                    </ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </div>
                                    </ComboboxList>
                                </Combobox>
                                <InputError :message="form.errors.shipping_postal_code" />
                            </div>
                            <div class="grid gap-2">
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
                            <div class="grid gap-2">
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
