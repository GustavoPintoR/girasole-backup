<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle, Check, Circle, Dot, CircleAlert, AlertCircleIcon } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { Switch } from '@/components/ui/switch';
import {
  Alert,
  AlertTitle,
} from '@/components/ui/alert'
import { cn } from '@/lib/utils';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import { ChevronsUpDown, Search, Eye, EyeOff } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import {
    Stepper,
    StepperDescription,
    StepperItem,
    // StepperSeparator,
    StepperTitle,
    StepperTrigger
} from '@/components/ui/stepper';


const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    mobile_phone: '',
    type: 'business',
    fiscal_code: '',
    vat_number: '',
    sdi_code: '',
    business_name: '',
    street: '',
    street_number: '',
    city: '',
    province: '',
    postal_code: '',
    password: '',
    password_confirmation: '',
    state: 'IT',
    region_id: '',
    province_id: '',
    city_id: '',
    // shipping
    same_as_billing: false,
    address: '',
    name: '',
    cellular: '',
    shipping_region_id: '',
    shipping_province_id: '',
    shipping_city_id: '',
    shipping_postal_code: '',
    shipping_street_number: '',
});

const page = usePage();

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
const shippingCities = ref([]);
const shippingPostalCodes = ref([]);
const shippingRegionQuery = ref('');
const shippingProvinceQuery = ref('');
const shippingCityQuery = ref('');
const shippingPostalCodeQuery = ref('');
const showPwd = ref(false);
const showPwdConfirm = ref(false);

const props = defineProps<{
    countries: { key: string; value: string }[];
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

const selectedRegion = ref<typeof regionOptions.value[0] | null>(null);
const selectedProvince = ref<typeof provinceOptions.value[0] | null>(null);
const selectedCity = ref<typeof cityOptions.value[0] | null>(null);
const selectedPostalCode = ref<typeof postalCodeOptions.value[0] | null>(null);

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

// shipping handlers
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


watch(selectedCountry, (newCountry) => {
    form.state = newCountry ? newCountry.value : '';
});

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

// shipping watchers
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

// Sync billing -> shipping when same_as_billing
watch([() => form.street, () => form.street_number, () => form.region_id, () => form.province_id, () => form.city_id, () => form.postal_code, () => form.mobile_phone, () => form.first_name, () => form.last_name], () => {
    if (form.same_as_billing) {
        form.address = form.street;
        form.shipping_street_number = form.street_number;
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

// Watch same_as_billing toggle
watch(() => form.same_as_billing, (useBilling) => {
    if (useBilling) {
        form.address = form.street;
        form.shipping_street_number = form.street_number;
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
        form.address = '';
        form.shipping_street_number = '';
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

// Sync combobox selections with form fields on data fetch
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


// Clear errors for each field as soon as it changes
const fieldsToWatch = {
    first_name: 1,
    last_name: 1,
    email: 1,
    mobile_phone: 1,
    type: 2,
    fiscal_code: 2,
    vat_number: 2,
    sdi_code: 2,
    business_name: 2,
    state: 3,
    region_id: 3,
    province_id: 3,
    city_id: 3,
    postal_code: 3,
    street: 3,
    street_number: 3,
    name: 3,
    address: 3,
    shipping_region_id: 3,
    shipping_province_id: 3,
    shipping_city_id: 3,
    shipping_postal_code: 3,
    shipping_street_number: 3,
    cellular: 3,
    password: 4,
    password_confirmation: 4,
};

Object.entries(fieldsToWatch).forEach(([field, step]) => {
    watch(() => form[field as keyof typeof form], () => {
        form.clearErrors(field as any);
        stepDirty.value[step] = true;
    });
});

const stepIndex = ref(1);
const steps = [
    {
        step: 1,
        title: trans('ui.company_contact_person'),
        description: trans('ui.personal_information'),
    },
    {
        step: 2,
        title: trans('ui.billing_info'),
        description: trans('ui.business_details'),
    },
    {
        step: 3,
        title: trans('ui.billing_address'),
        description: trans('ui.address_information'),
    },
    {
        step: 4,
        title: trans('ui.password_setup'),
        description: trans('ui.security'),
    },
];

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const isValidEmail = (email: string) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

const isStep1Valid = () => {
    return form.first_name && form.last_name && form.email && isValidEmail(form.email) && form.mobile_phone;
};

const isStep2Valid = () => {
    if (form.type == 'business') {
        return form.type && form.vat_number && form.sdi_code && form.business_name && form.fiscal_code;
    }
    return form.type && form.fiscal_code;
};

const isStep3Valid = () => {
    return (
        form.state &&
        form.region_id &&
        form.province_id &&
        form.city_id &&
        form.postal_code &&
        form.street &&
        form.street_number
    );
};

const isStep4Valid = () => {
    return form.password && form.password_confirmation && form.password === form.password_confirmation;
};

const isCurrentStepValid = () => {
    switch (stepIndex.value) {
        case 1: return isStep1Valid();
        case 2: return isStep2Valid();
        case 3: return isStep3Valid();
        case 4: return isStep4Valid();
        default: return false;
    }
};

const canProceedToStep = (step: number) => {
    switch (step) {
        case 1: return true;
        case 2: return isStep1Valid();
        case 3: return isStep1Valid() && isStep2Valid();
        case 4: return isStep1Valid() && isStep2Valid() && isStep3Valid();
        default: return false;
    }
};

const stepValidators: Record<number, () => boolean> = {
    1: () => !!(form.first_name && form.last_name && form.email && isValidEmail(form.email) && form.mobile_phone),
    2: () => form.type === 'business'
        ? !!(form.type && form.vat_number && form.sdi_code && form.business_name && form.fiscal_code)
        : !!(form.type && form.fiscal_code),
    3: () => !!(form.state && form.region_id && form.province_id && form.city_id && form.postal_code && form.street && form.street_number),
    4: () => !!(form.password && form.password_confirmation && form.password === form.password_confirmation),
}

const stepServerHasErrors = (step: number) => {
    const e = form?.errors ?? {}
    if (step === 1) return !!(e.first_name || e.last_name || e.email || e.mobile_phone)
    if (step === 2) return !!(e.type || e.fiscal_code || e.vat_number || e.sdi_code || e.business_name)
    if (step === 3) return !!(e.region_id || e.province_id || e.city_id || e.postal_code || e.street || e.street_number)
    if (step === 4) return !!(e.password || e.password_confirmation)
    return false
}

const stepHasErrors = (s: number) => {
    const invalid = !(stepValidators[s]?.() ?? true)
    const hasServer = stepServerHasErrors(s)
    const interacted = stepDirty.value[s]
    return (invalid && interacted) || hasServer
}

const stepState = (s: number) => {
    if (stepIndex.value === s) return 'active'
    if (!stepHasErrors(s) && stepIndex.value > s) return 'completed'
    if (stepHasErrors(s)) return 'error'
    return 'inactive'
}

const stepDirty = ref<Record<number, boolean>>({
    1: false,
    2: false,
    3: false,
    4: false,
})

</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background relative p-6 md:p-10 bg-cover bg-center bg-fixed"
        style="background-image: url('/images/GirasoleFarm_Benvenuto_optimized.jpeg');">

        <Head title="Register" />

        <Stepper v-slot="{ isPrevDisabled, nextStep, prevStep }" v-model="stepIndex"
            class="flex items-center justify-center px-4 sm:px-6 lg:px-8">
            <form @submit.prevent="stepIndex === 4 && isCurrentStepValid() ? submit() : null" class="mb-5">
                <div class="flex w-full items-center mb-5 gap-20">
                    <Link :href="route('login')"
                        class="ml-auto rounded-sm bg-primary/90 hover:bg-primary text-white dark:bg-black/50 dark:hover:bg-black/60 dark:text-[#EDEDEC] border border-transparent px-5 py-1.5 text-sm leading-normal">
                    {{ trans('ui.return_to_login') }}
                    </Link>
                </div>

                <!-- General Error Message -->
                <Alert v-if="page.props.errors.error" variant="destructive" class="mb-6">
                    <AlertCircleIcon />
                    <AlertTitle>{{ page.props.errors.error }}</AlertTitle>
                </Alert>

                <!-- Stepper Header -->
                <div
                    class="flex w-full justify-between items-start gap-20 mb-5 bg-card rounded-lg border p-8">
                    <StepperItem v-for="step in steps" :key="step.step"
                        class="relative flex w-full flex-col items-center justify-center" :step="step.step">
                        <StepperTrigger as-child>
                            <Button
                                :variant="['completed', 'active'].includes(stepState(step.step)) ? 'default' : 'outline'"
                                size="icon" class="z-10 rounded-full shrink-0 w-12 h-12 transition-all duration-200
             data-[error=true]:border-red-500 data-[error=true]:text-red-600"
                                :data-error="stepState(step.step) === 'error'"
                                :class="[stepState(step.step) === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
                                :disabled="!canProceedToStep(step.step)">
                                <template v-if="stepState(step.step) === 'error'">
                                    <CircleAlert class="w-5 h-5 text-red-600" />
                                </template>
                                <template v-else-if="stepState(step.step) === 'completed'">
                                    <Check class="w-5 h-5" />
                                </template>
                                <template v-else-if="stepState(step.step) === 'active'">
                                    <Circle class="w-5 h-5" />
                                </template>
                                <template v-else>
                                    <Dot class="w-5 h-5" />
                                </template>
                            </Button>
                        </StepperTrigger>
                        <div class="mt-4 flex flex-col items-center text-center max-w-xs">
                            <StepperTitle class="text-sm font-semibold transition-colors duration-200 lg:text-base"
                                :class="[
                                    stepState(step.step) === 'active' && 'text-primary',
                                    stepState(step.step) === 'error' && 'text-red-600'
                                ]">
                                {{ step.title }}
                            </StepperTitle>
                            <StepperDescription
                                class="text-xs text-muted-foreground transition-colors duration-200 mt-1 lg:text-sm"
                                :class="[
                                    stepState(step.step) === 'active' && 'text-primary',
                                    stepState(step.step) === 'error' && 'text-red-500'
                                ]">
                                {{ step.description }}
                            </StepperDescription>
                        </div>
                    </StepperItem>
                </div>

                <!-- Step Content -->
                <div class="bg-card rounded-lg border p-8">
                    <!-- Step 1: Personal Information -->
                    <template v-if="stepIndex === 1">
                        <div class="space-y-6">
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold">{{ trans('ui.company_contact_person') }}</h2>
                                <p class="text-muted-foreground mt-2">{{ trans('ui.provide_your_details') }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="first_name">{{ trans('ui.first_name') }}<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="first_name" type="text" required autofocus v-model="form.first_name"
                                        :placeholder="trans('ui.first_name')" class="h-11" />
                                    <InputError :message="form.errors.first_name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="last_name">{{ trans('ui.last_name') }}<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="last_name" type="text" required v-model="form.last_name"
                                        :placeholder="trans('ui.last_name')" class="h-11" />
                                    <InputError :message="form.errors.last_name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">{{ trans('ui.email_address') }}<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="email" type="email" required v-model="form.email"
                                        placeholder="email@example.com" class="h-11" />
                                    <InputError :message="form.errors.email" />
                                    <div v-if="form.email && !isValidEmail(form.email)" class="text-sm text-red-500">
                                        {{ trans('ui.invalid_email') }}
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="mobile_phone">{{ trans('ui.mobile_phone') }}<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="mobile_phone" type="tel" required v-model="form.mobile_phone"
                                        :placeholder="trans('ui.mobile_phone')" class="h-11" />
                                    <InputError :message="form.errors.mobile_phone" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 2: Business Information -->
                    <template v-if="stepIndex === 2">
                        <div class="space-y-6">
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold">{{ trans('ui.billing_info') }}</h2>
                                <p class="text-muted-foreground mt-2">{{ trans('ui.billing_information') }}</p>
                            </div>

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="type">{{ trans('ui.type') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Select v-model="form.type" class="h-11">
                                            <SelectTrigger class="h-11 w-[462px]" :data-size="11">
                                                <SelectValue :placeholder="trans('ui.select_type')" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="business">
                                                        {{ trans('ui.business') }}
                                                    </SelectItem>
                                                    <SelectItem value="sole_business">
                                                        {{ trans('ui.sole_business') }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>

                                        <InputError :message="form.errors.type" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="fiscal_code">{{ trans('ui.fiscal_code') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Input id="fiscal_code" type="text" required v-model="form.fiscal_code"
                                            :placeholder="trans('ui.fiscal_code')" class="h-11" />
                                        <InputError :message="form.errors.fiscal_code" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="form.type == 'business'">

                                    <div class="space-y-2">
                                        <Label for="vat_number">{{ trans('ui.vat_number') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Input id="vat_number" type="text" required v-model="form.vat_number"
                                            :placeholder="trans('ui.vat_number')" class="h-11" />
                                        <InputError :message="form.errors.vat_number" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="sdi_code">{{ trans('ui.sdi_code') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Input id="sdi_code" type="text" required v-model="form.sdi_code"
                                            :placeholder="trans('ui.sdi_code')" class="h-11" />
                                        <InputError :message="form.errors.sdi_code" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="business_name">{{ trans('ui.business_name') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Input id="business_name" type="text" required v-model="form.business_name"
                                            :placeholder="trans('ui.business_name')" class="h-11" />
                                        <InputError :message="form.errors.business_name" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 3: Address Information -->
                    <template v-if="stepIndex === 3">
                        <div class="space-y-6">
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold">{{ trans('ui.billing_address') }}</h2>
                                <p class="text-muted-foreground mt-2">{{ trans('ui.business_address_details') }}</p>
                            </div>

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- State (Combobox) -->
                                    <div class="space-y-2">
                                        <Label for="state">{{ trans('ui.state') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Combobox v-model="selectedCountry" by="value" disabled>
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between"
                                                        disabled>
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
                                                        @update:modelValue="countryQuery = $event" />
                                                    <span
                                                        class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div
                                                    class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                        {{ trans('ui.no_states_found') }}
                                                    </ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="country in countryOptions"
                                                            :key="country.value" :value="country"
                                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
                                                            {{ country.label }}
                                                            <ComboboxItemIndicator>
                                                                <Check
                                                                    :class="cn('ml-auto h-4 w-4', selectedCountry?.value !== country.value && 'opacity-0')" />
                                                            </ComboboxItemIndicator>
                                                        </ComboboxItem>
                                                    </ComboboxGroup>
                                                </div>
                                            </ComboboxList>
                                        </Combobox>
                                        <InputError :message="form.errors.region_id" />
                                    </div>

                                    <!-- Region (Combobox) -->
                                    <div class="space-y-2">
                                        <Label for="region">{{ trans('ui.region') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Combobox v-model="selectedRegion" by="value">
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
                                            <ComboboxList class="w-[462px] relative z-10">
                                                <div class="relative w-full">
                                                    <ComboboxInput
                                                        class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full"
                                                        :placeholder="trans('ui.search_regions')"
                                                        @update:modelValue="regionQuery = $event" />
                                                    <span
                                                        class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div
                                                    class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                        {{ trans('ui.no_regions_found') }}
                                                    </ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="region in regionOptions"
                                                            :key="region.value" :value="region"
                                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
                                                            {{ region.label }}
                                                            <ComboboxItemIndicator>
                                                                <Check
                                                                    :class="cn('ml-auto h-4 w-4', selectedRegion?.value !== region.value && 'opacity-0')" />
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
                                        <Label for="province">{{ trans('ui.province') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Combobox v-model="selectedProvince" by="value" :disabled="!form.region_id">
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between"
                                                        :disabled="!form.region_id">
                                                        <span class="truncate">
                                                            {{ selectedProvince?.label ?? trans('ui.select_province') }}
                                                        </span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="w-[462px] relative z-10">
                                                <div class="relative w-full">
                                                    <ComboboxInput
                                                        class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-[462px]"
                                                        :placeholder="trans('ui.search_provinces')"
                                                        @update:modelValue="provinceQuery = $event"
                                                        :disabled="!form.region_id" />
                                                    <span
                                                        class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div
                                                    class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                        {{ trans('ui.no_provinces_found') }}
                                                    </ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="province in provinceOptions"
                                                            :key="province.value" :value="province"
                                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
                                                            {{ province.label }}
                                                            <ComboboxItemIndicator>
                                                                <Check
                                                                    :class="cn('ml-auto h-4 w-4', selectedProvince?.value !== province.value && 'opacity-0')" />
                                                            </ComboboxItemIndicator>
                                                        </ComboboxItem>
                                                    </ComboboxGroup>
                                                </div>
                                            </ComboboxList>
                                        </Combobox>
                                        <InputError :message="form.errors.province_id" />
                                    </div>

                                    <!-- City (Combobox) -->
                                    <div class="space-y-2">
                                        <Label for="city">{{ trans('ui.city') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Combobox v-model="selectedCity" by="value" :disabled="!form.province_id">
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between"
                                                        :disabled="!form.province_id">
                                                        <span class="truncate">
                                                            {{ selectedCity?.label ?? trans('ui.select_city') }}
                                                        </span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="w-[462px] relative z-10">
                                                <div class="relative w-full">
                                                    <ComboboxInput
                                                        class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-[462px]"
                                                        :placeholder="trans('ui.search_cities')"
                                                        @update:modelValue="cityQuery = $event"
                                                        :disabled="!form.province_id" />
                                                    <span
                                                        class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div
                                                    class="w-full bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                        {{ trans('ui.no_cities_found') }}
                                                    </ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="city in cityOptions" :key="city.value"
                                                            :value="city"
                                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
                                                            {{ city.label }}
                                                            <ComboboxItemIndicator>
                                                                <Check
                                                                    :class="cn('ml-auto h-4 w-4', selectedCity?.value !== city.value && 'opacity-0')" />
                                                            </ComboboxItemIndicator>
                                                        </ComboboxItem>
                                                    </ComboboxGroup>
                                                </div>
                                            </ComboboxList>
                                        </Combobox>
                                        <InputError :message="form.errors.city_id" />
                                    </div>

                                    <!-- Postal Code (Combobox) -->
                                    <div class="space-y-2">
                                        <Label for="postal_code">{{ trans('ui.postal_code') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Combobox v-model="selectedPostalCode" by="value" :disabled="!form.city_id">
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between"
                                                        :disabled="!form.city_id">
                                                        <span class="truncate">
                                                            {{ selectedPostalCode?.label ??
                                                                trans('ui.select_postal_code') }}
                                                        </span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="w-[462px] relative z-10">
                                                <div class="relative w-full">
                                                    <ComboboxInput
                                                        class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-[462px]"
                                                        :placeholder="trans('ui.search_postal_codes')"
                                                        @update:modelValue="postalCodeQuery = $event"
                                                        :disabled="!form.city_id" />
                                                    <span
                                                        class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div
                                                    class="w-full bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">
                                                        {{ trans('ui.no_postal_codes_found') }}
                                                    </ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="postalCode in postalCodeOptions"
                                                            :key="postalCode.value" :value="postalCode"
                                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
                                                            {{ postalCode.label }}
                                                            <ComboboxItemIndicator>
                                                                <Check
                                                                    :class="cn('ml-auto h-4 w-4', selectedPostalCode?.value !== postalCode.value && 'opacity-0')" />
                                                            </ComboboxItemIndicator>
                                                        </ComboboxItem>
                                                    </ComboboxGroup>
                                                </div>
                                            </ComboboxList>
                                        </Combobox>
                                        <InputError :message="form.errors.postal_code" />
                                    </div>

                                    <!-- Street -->
                                    <div class="space-y-2">
                                        <Label for="street">{{ trans('ui.street_or_square') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Input id="street" type="text" required v-model="form.street"
                                            :placeholder="trans('ui.street_or_square')" class="h-11" />
                                        <InputError :message="form.errors.street" />
                                    </div>

                                    <!-- Street Number -->
                                    <div class="space-y-2">
                                        <Label for="street_number">{{ trans('ui.street_number') }}<span
                                                class="text-red-500">*</span></Label>
                                        <Input id="street_number" type="text" required v-model="form.street_number"
                                            :placeholder="trans('ui.street_number')" class="h-11" />
                                        <InputError :message="form.errors.street_number" />
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping toggle and inputs -->
                            <div class="mt-6">
                                <div class="flex items-center gap-4 mb-4">
                                    <Label class="mb-0">{{ trans('ui.use_billing_as_shipping') }}</Label>
                                    <Switch v-model="form.same_as_billing" />
                                </div>

                                <div v-if="!form.same_as_billing" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="name">{{ trans('ui.recipient_name') }}<span class="text-red-500">*</span></Label>
                                        <Input id="name" type="text" v-model="form.name" :placeholder="trans('ui.recipient_name')" class="h-11" />
                                        <InputError :message="form.errors.name" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="shipping_region">{{ trans('ui.region') }}<span class="text-red-500">*</span></Label>
                                        <Combobox v-model="shippingSelectedRegion" by="value">
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between">
                                                        <span class="truncate">{{ shippingSelectedRegion?.label ?? trans('ui.select_region') }}</span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="w-[462px] relative z-10">
                                                <div class="relative w-full">
                                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full" :placeholder="trans('ui.search_regions')" @update:modelValue="shippingRegionQuery = $event" />
                                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">{{ trans('ui.no_regions_found') }}</ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="region in shippingRegionOptions" :key="region.value" :value="region" class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
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
                                        <Label for="shipping_province">{{ trans('ui.province') }}<span class="text-red-500">*</span></Label>
                                        <Combobox v-model="shippingSelectedProvince" by="value" :disabled="!form.shipping_region_id">
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.shipping_region_id">
                                                        <span class="truncate">{{ shippingSelectedProvince?.label ?? trans('ui.select_province') }}</span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="w-[462px] relative z-10">
                                                <div class="relative w-full">
                                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-[462px]" :placeholder="trans('ui.search_provinces')" @update:modelValue="shippingProvinceQuery = $event" :disabled="!form.shipping_region_id" />
                                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div class="w-[462px] bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">{{ trans('ui.no_provinces_found') }}</ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="province in shippingProvinceOptions" :key="province.value" :value="province" class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
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
                                        <Label for="shipping_city">{{ trans('ui.city') }}<span class="text-red-500">*</span></Label>
                                        <Combobox v-model="shippingSelectedCity" by="value" :disabled="!form.shipping_province_id">
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.shipping_province_id">
                                                        <span class="truncate">{{ shippingSelectedCity?.label ?? trans('ui.select_city') }}</span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="w-full bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <div class="relative w-full">
                                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-[462px]" :placeholder="trans('ui.search_cities')" @update:modelValue="shippingCityQuery = $event" :disabled="!form.shipping_province_id" />
                                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div class="w-full bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">{{ trans('ui.no_cities_found') }}</ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="city in shippingCityOptions" :key="city.value" :value="city" class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
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
                                        <Label for="shipping_postal_code">{{ trans('ui.postal_code') }}<span class="text-red-500">*</span></Label>
                                        <Combobox v-model="shippingSelectedPostalCode" by="value" :disabled="!form.shipping_city_id">
                                            <ComboboxAnchor as-child>
                                                <ComboboxTrigger as-child>
                                                    <Button variant="outline" class="h-11 w-full justify-between" :disabled="!form.shipping_city_id">
                                                        <span class="truncate">{{ shippingSelectedPostalCode?.label ?? trans('ui.select_postal_code') }}</span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="w-full bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                <div class="relative w-full">
                                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 w-[462px]" :placeholder="trans('ui.search_postal_codes')" @update:modelValue="shippingPostalCodeQuery = $event" :disabled="!form.shipping_city_id" />
                                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </span>
                                                </div>
                                                <div class="w-full bg-white border rounded-md max-h-60 overflow-y-auto mt-1 dark:bg-input/30">
                                                    <ComboboxEmpty class="px-4 py-2 text-gray-500">{{ trans('ui.no_postal_codes_found') }}</ComboboxEmpty>
                                                    <ComboboxGroup>
                                                        <ComboboxItem v-for="postalCode in shippingPostalCodeOptions" :key="postalCode.value" :value="postalCode" class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-input/50">
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
                                        <Label for="address">{{ trans('ui.street_or_square') }}<span class="text-red-500">*</span></Label>
                                        <Input id="address" type="text" v-model="form.address" :placeholder="trans('ui.street_or_square')" class="h-11" />
                                        <InputError :message="form.errors.address" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="shipping_street_number">{{ trans('ui.street_number') }}<span class="text-red-500">*</span></Label>
                                        <Input id="shipping_street_number" type="text" v-model="form.shipping_street_number" :placeholder="trans('ui.street_number')" class="h-11" />
                                        <InputError :message="form.errors.shipping_street_number" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="cellular">{{ trans('ui.cellular') }}</Label>
                                        <Input id="cellular" type="text" v-model="form.cellular" :placeholder="trans('ui.cellular')" class="h-11" />
                                        <InputError :message="form.errors.cellular" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 4: Password Setup -->
                    <template v-if="stepIndex === 4">
                        <div class="space-y-6">
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold">{{ trans('ui.security_setup') }}</h2>
                                <p class="text-muted-foreground mt-2">{{ trans('ui.create_account_password') }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                                <!-- Password -->
                                <div class="space-y-2">
                                    <Label for="password">
                                        {{ trans('ui.password') }}<span class="text-red-500">*</span>
                                    </Label>

                                    <div class="relative">
                                        <Input id="password" :type="showPwd ? 'text' : 'password'" required
                                            v-model="form.password" :placeholder="trans('ui.password')"
                                            class="h-11 pr-10" autocomplete="new-password" />
                                        <button type="button"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground"
                                            :aria-label="showPwd ? 'Hide password' : 'Show password'"
                                            :aria-pressed="showPwd" @click="showPwd = !showPwd">
                                            <Eye v-if="!showPwd" class="h-5 w-5" />
                                            <EyeOff v-else class="h-5 w-5" />
                                        </button>
                                    </div>

                                    <InputError :message="form.errors.password" />
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <Label for="password_confirmation">
                                        {{ trans('ui.confirm_password') }}<span class="text-red-500">*</span>
                                    </Label>

                                    <div class="relative">
                                        <Input id="password_confirmation" :type="showPwdConfirm ? 'text' : 'password'"
                                            required v-model="form.password_confirmation"
                                            :placeholder="trans('ui.confirm_password')" class="h-11 pr-10"
                                            autocomplete="new-password" />
                                        <button type="button"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground"
                                            :aria-label="showPwdConfirm ? 'Hide password' : 'Show password'"
                                            :aria-pressed="showPwdConfirm" @click="showPwdConfirm = !showPwdConfirm">
                                            <Eye v-if="!showPwdConfirm" class="h-5 w-5" />
                                            <EyeOff v-else class="h-5 w-5" />
                                        </button>
                                    </div>

                                    <InputError :message="form.errors.password_confirmation" />
                                    <div v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation"
                                        class="text-sm text-red-500">
                                        {{ trans('ui.password_mismatch') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center justify-between pt-6">
                        <Button :disabled="isPrevDisabled" variant="outline" size="lg" @click="prevStep()" class="px-8">
                            {{ trans('ui.back') }}
                        </Button>

                        <div class="flex items-center gap-4">
                            <Button v-if="stepIndex !== 4" :disabled="!isCurrentStepValid()" size="lg"
                                @click="(stepDirty[stepIndex] = true, nextStep())" class="px-8">
                                {{ trans('ui.next_step') }}
                            </Button>

                            <Button v-if="stepIndex === 4" type="submit" size="lg"
                                :disabled="form.processing || !isCurrentStepValid()"
                                @click="stepDirty[stepIndex] = true" class="px-8">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                {{ trans('ui.create_account') }}
                            </Button>
                        </div>
                    </div>
                </div>
            </form>
        </Stepper>

    </div>
</template>
