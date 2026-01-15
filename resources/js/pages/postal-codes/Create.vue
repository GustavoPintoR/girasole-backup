<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { ref, computed, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { cn } from '@/lib/utils';
import axios from 'axios';

interface CityFormData {
    city_id: number;
    zone: string;
    notes: string;
}

const breadcrumbs = [
    {
        title: trans('ui.postal_codes'),
        href: route('postal-codes.index'),
    },
    {
        title: trans('ui.create'),
        href: route('postal-codes.create'),
    },
];

const form = useForm({
    region_id: '',
    province_id: '',
    cities: [] as CityFormData[],
    postal_code: '',
});

const regions = ref([]);
const provinces = ref([]);
const cities = ref([]);
const regionQuery = ref('');
const provinceQuery = ref('');
const cityQuery = ref('');

const fetchRegions = async () => {
    try {
        const response = await axios.get(route('api.internal.regions.index'));
        regions.value = response.data;
    } catch (error) {
        console.error('Error fetching regions:', error);
    }
};

const fetchProvinces = async (regionId: string) => {
    if (!regionId) {
        provinces.value = [];
        cities.value = [];
        form.province_id = '';
        form.cities = [];
        return;
    }
    try {
        const response = await axios.get(route('api.internal.regions.get.provinces', { region: regionId }));
        provinces.value = response.data;
        cities.value = [];
        form.cities = [];
    } catch (error) {
        console.error('Error fetching provinces:', error);
    }
};

const fetchCities = async (provinceId: string) => {
    if (!provinceId) {
        cities.value = [];
        form.cities = [];
        return;
    }
    try {
        const response = await axios.get(route('api.internal.provinces.get.cities', { province: provinceId }));
        cities.value = response.data;
        form.cities = form.cities.filter(city => cities.value.some(c => c.id === city.city_id));
    } catch (error) {
        console.error('Error fetching cities:', error);
    }
};

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

const selectedRegion = ref<typeof regionOptions.value[0] | null>(null);
const selectedProvince = ref<typeof provinceOptions.value[0] | null>(null);
const selectedCities = ref<typeof cityOptions.value[]>([]);

watch(selectedRegion, (newRegion) => {
    form.region_id = newRegion ? newRegion.value : '';
});

watch(selectedProvince, (newProvince) => {
    form.province_id = newProvince ? newProvince.value : '';
});

watch(selectedCities, (newSelectedCities) => {
    // Update form.cities to include zone and notes for new selections, preserve existing for unchanged cities
    const existingCities = form.cities;
    form.cities = newSelectedCities.map(city => {
        const existing = existingCities.find(c => c.city_id === city.value);
        return {
            city_id: city.value,
            zone: existing?.zone || '',
            notes: existing?.notes || '',
        };
    });
});

// Sync Combobox selections with form fields on data fetch
watch(regions, () => {
    selectedRegion.value = regionOptions.value.find(region => region.value === form.region_id) || null;
});

watch(provinces, () => {
    selectedProvince.value = provinceOptions.value.find(province => province.value === form.province_id) || null;
});

watch(cities, () => {
    selectedCities.value = cityOptions.value.filter(city => form.cities.some(c => c.city_id === city.value));
});

// Fetch provinces and cities on region/province change
watch(() => form.region_id, (newRegionId) => {
    fetchProvinces(newRegionId);
});

watch(() => form.province_id, (newProvinceId) => {
    fetchCities(newProvinceId);
});

const submit = () => {
    form.post(route('postal-codes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('postal-codes.index'));
        },
    });
};

const cancel = () => {
    router.visit(route('postal-codes.index'));
};

const showPostalCodeAndCities = computed(() => {
    return form.cities.length > 0;
});

fetchRegions();
</script>

<template>
    <Head :title="trans('ui.postal_code_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.postal_code_create_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.postal_code_create_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.postal_code_create_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Region (Combobox) -->
                        <div class="space-y-2">
                            <Label for="region">{{ trans('ui.region') }}<span class="text-red-500">*</span></Label>
                            <Combobox v-model="selectedRegion" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button variant="outline" class="h-11 w-[462px] justify-between">
                                            <span class="truncate">
                                                {{ selectedRegion?.label ?? trans('ui.select_region') }}
                                            </span>
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[462px] relative z-10">
                                    <div class="relative w-[462px]">
                                        <ComboboxInput
                                            class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                            :placeholder="trans('ui.search_regions')"
                                            @update:modelValue="regionQuery = $event"
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

                        <!-- Province (Combobox) -->
                        <div class="space-y-2">
                            <Label for="province">{{ trans('ui.province') }}<span class="text-red-500">*</span></Label>
                            <Combobox v-model="selectedProvince" by="value" :disabled="!form.region_id">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button
                                            variant="outline"
                                            class="h-11 w-[462px] justify-between"
                                            :disabled="!form.region_id"
                                        >
                                            <span class="truncate">
                                                {{ selectedProvince?.label ?? trans('ui.select_province') }}
                                            </span>
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[462px] relative z-10">
                                    <div class="relative w-[462px]">
                                        <ComboboxInput
                                            class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                            :placeholder="trans('ui.search_provinces')"
                                            @update:modelValue="provinceQuery = $event"
                                            :disabled="!form.region_id"
                                        />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                            <Search class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
                                    <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
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

                        <!-- Cities (Combobox Multi-Select) -->
                        <div class="space-y-2">
                            <Label for="cities">{{ trans('ui.cities') }}<span class="text-red-500">*</span></Label>
                            <Combobox v-model="selectedCities" multiple by="value" :disabled="!form.province_id">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button
                                            variant="outline"
                                            class="h-11 w-[462px] justify-between"
                                            :disabled="!form.province_id"
                                        >
                                            <span class="truncate">
                                                {{ selectedCities.length > 0 ? selectedCities.map(c => c.label).join(', ') : trans('ui.select_cities') }}
                                            </span>
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[462px] relative z-10">
                                    <div class="relative w-[462px]">
                                        <ComboboxInput
                                            class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                            :placeholder="trans('ui.search_cities')"
                                            @update:modelValue="cityQuery = $event"
                                            :disabled="!form.province_id"
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
                                                    <Check :class="cn('ml-auto h-4 w-4', !selectedCities.some(c => c.value === city.value) && 'opacity-0')" />
                                                </ComboboxItemIndicator>
                                            </ComboboxItem>
                                        </ComboboxGroup>
                                    </div>
                                </ComboboxList>
                            </Combobox>
                            <InputError :message="form.errors.cities" />
                        </div>

                        <!-- Postal Code -->
                        <div v-if="showPostalCodeAndCities" class="space-y-2">
                            <Label for="postal_code">
                                {{ trans('ui.postal_code') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="postal_code"
                                type="text"
                                required
                                v-model="form.postal_code"
                                :placeholder="trans('ui.postal_code')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.postal_code }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.postal_code" />
                        </div>

                        <!-- City-Specific Zone and Notes -->
<!--                        <div v-if="showPostalCodeAndCities" class="space-y-4">-->
<!--                            <div v-for="(city, index) in form.cities" :key="city.city_id" class="space-y-4 w-[500px]">-->
<!--                                <Card class="border border-gray-200 dark:border-gray-700">-->
<!--                                    <CardHeader>-->
<!--                                        <CardTitle class="flex items-center gap-2 text-lg">-->
<!--                                            <MapPin class="h-5 w-5 text-gray-500" />-->
<!--                                            {{ trans('ui.details_for_city', { city: cityOptions.find(c => c.value === city.city_id)?.label || 'Unknown City' }) }}-->
<!--                                        </CardTitle>-->
<!--                                    </CardHeader>-->
<!--                                    <CardContent class="space-y-4">-->
<!--                                        &lt;!&ndash; Zone &ndash;&gt;-->
<!--                                        <div class="space-y-2">-->
<!--                                            <Label :for="'zone-' + city.city_id">-->
<!--                                                {{ trans('ui.zone_for_city', { city: cityOptions.find(c => c.value === city.city_id)?.label || 'Unknown City' }) }}-->
<!--                                            </Label>-->
<!--                                            <Input-->
<!--                                                :id="'zone-' + city.city_id"-->
<!--                                                type="text"-->
<!--                                                v-model="form.cities[index].zone"-->
<!--                                                :placeholder="trans('ui.zone')"-->
<!--                                                class="h-11 w-[462px]"-->
<!--                                                :class="{ 'border-red-500': form.errors[`cities.${index}.zone`] }"-->
<!--                                                :disabled="form.processing"-->
<!--                                            />-->
<!--                                            <InputError :message="form.errors[`cities.${index}.zone`]" />-->
<!--                                        </div>-->
<!--                                        &lt;!&ndash; Notes &ndash;&gt;-->
<!--                                        <div class="space-y-2">-->
<!--                                            <Label :for="'notes-' + city.city_id">-->
<!--                                                {{ trans('ui.notes_for_city', { city: cityOptions.find(c => c.value === city.city_id)?.label || 'Unknown City' }) }}-->
<!--                                            </Label>-->
<!--                                            <Textarea-->
<!--                                                :id="'notes-' + city.city_id"-->
<!--                                                v-model="form.cities[index].notes"-->
<!--                                                :placeholder="trans('ui.notes_placeholder')"-->
<!--                                                :rows="3"-->
<!--                                                class="w-[462px]"-->
<!--                                                :class="{ 'border-red-500': form.errors[`cities.${index}.notes`] }"-->
<!--                                                :disabled="form.processing"-->
<!--                                            />-->
<!--                                            <p class="text-sm text-gray-500">-->
<!--                                                {{ trans('ui.max_thousand_chars') }} ({{ form.cities[index].notes.length }}/1000)-->
<!--                                            </p>-->
<!--                                            <InputError :message="form.errors[`cities.${index}.notes`]" />-->
<!--                                        </div>-->
<!--                                    </CardContent>-->
<!--                                </Card>-->
<!--                            </div>-->
<!--                        </div>-->

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? trans('ui.creating') : trans('ui.create_postal_code') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
