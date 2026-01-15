<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Combobox, ComboboxAnchor, ComboboxTrigger, ComboboxInput, ComboboxList, ComboboxGroup, ComboboxItem, ComboboxItemIndicator, ComboboxEmpty } from '@/components/ui/combobox';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import InputError from '@/components/InputError.vue';
import { ref, computed, watch, onMounted } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { format, parseISO } from 'date-fns';
import axios from 'axios';

interface City {
    id: number;
    name: string;
    province_id?: number;
}

interface CityFormData {
    city_id: number;
    zone: string;
    notes: string;
}

interface PostalCode {
    id: number;
    code: string;
    zone: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
    cities: City[];
}

const props = defineProps<{
    postalCode: PostalCode;
}>();

const breadcrumbs = [
    {
        title: trans('ui.postal_codes'),
        href: route('postal-codes.index'),
    },
    {
        title: props.postalCode.code,
        href: route('postal-codes.show', props.postalCode.id),
    },
    {
        title: trans('ui.edit'),
        href: route('postal-codes.edit', props.postalCode.id),
    },
];

const form = useForm({
    postal_code: props.postalCode.code,
    cities: props.postalCode.cities.map(city => ({
        city_id: city.id,
        zone: props.postalCode.zone || '',
        notes: props.postalCode.notes || '',
    })) as CityFormData[],
});

const cities = ref<City[]>([]);
const cityQuery = ref('');
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

const selectedCities = ref<typeof cityOptions.value[]>([]);

watch(selectedCities, (newSelectedCities) => {
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

watch(cities, () => {
    selectedCities.value = cityOptions.value.filter(city => form.cities.some(c => c.city_id === city.value));
});

const fetchCities = async (provinceId: string) => {
    if (!provinceId) {
        cities.value = [];
        form.cities = props.postalCode.cities.map(city => ({
            city_id: city.id,
            zone: props.postalCode.zone || '',
            notes: props.postalCode.notes || '',
        }));
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

onMounted(async () => {
    if (props.postalCode.cities.length > 0) {
        const firstCity = props.postalCode.cities[0];
        if (firstCity.province_id) {
            await fetchCities(firstCity.province_id.toString());
            selectedCities.value = cityOptions.value.filter(city => form.cities.some(c => c.city_id === city.value));
        }
    }
});

const submit = () => {
    form.put(route('postal-codes.update', props.postalCode.id), {
        preserveScroll: true,
        onSuccess: () => {
            //
        },
    });
};

const cancel = () => {
    router.visit(route('postal-codes.index'));
};

const showPostalCodeAndCities = computed(() => {
    return form.cities.length > 0;
});
</script>

<template>
    <Head :title="trans('ui.postal_code_edit_header', { postal_code: props.postalCode.code })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.postal_code_edit_header', { postal_code: props.postalCode.code }) }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.postal_code_edit_header_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.postal_code_desc', { postal_code: props.postalCode.code }) }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Cities (Combobox Multi-Select) -->
                        <div class="space-y-2">
                            <Label for="cities">{{ trans('ui.cities') }}<span class="text-red-500">*</span></Label>
                            <Combobox v-model="selectedCities" multiple by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button variant="outline" class="h-11 w-[462px] justify-between">
                                            <span class="truncate">
                                                {{ selectedCities.length > 0 ? selectedCities.map(c => c.label).join(', ') : trans('ui.select_cities') }}
                                            </span>
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[462px] absolute z-10">
                                    <div class="relative w-[462px]">
                                        <ComboboxInput
                                            class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                            :placeholder="trans('ui.search_cities')"
                                            @update:modelValue="cityQuery = $event"
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
                                {{ form.processing ? trans('ui.updating') : trans('ui.update_postal_code') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Metadata -->
            <Card class="max-w-4xl mt-4">
                <CardHeader>
                    <CardTitle class="text-base">{{ trans('ui.information') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.created_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.postalCode.created_at), 'PPP p') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.postalCode.updated_at), 'PPP p') }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
