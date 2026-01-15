<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import { Company, Sensor, SensorType, User } from '@/types';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    sensor: Sensor;
    users: User[];
    types: SensorType[];
    companies?: Company[];
    ownedCompanyIds?: number[];
    isAdmin?: boolean;
}>();

const breadcrumbs = [
    {
        title: trans('ui.sensors'),
        href: route('sensors.index'),
    },
    {
        title: trans('ui.edit_sensor'),
        href: route('sensors.edit', props.sensor.id),
    },
];

const form = useForm({
    name: props.sensor.name,
    sensor_type_id: props.sensor.sensor_type?.id,
    description: props.sensor.description,
    urn: props.sensor.urn,
    transmission_module_identification: props.sensor.transmission_module_identification,
    iccid: props.sensor.iccid,
    serial_number: props.sensor.serial_number,
    firmware: props.sensor.firmware,
    latitude: props.sensor.latitude,
    longitude: props.sensor.longitude,
    owner_id: props.sensor.owner?.id,
    company_id: (props as any).sensor.company?.id || '',
    cadastral_group_id: props.sensor.cadastral_group?.id || '',
});

const cadastralGroups = ref<any[]>([]);
const isInitialLoad = ref(true);
const companiesList = ref<any[]>(props.companies || []);

const fetchCadastralGroups = async (userId: string | number) => {
    try {
        const response = await axios.get('/api/cadastral-groups', {
            params: { user_id: userId }
        });
        cadastralGroups.value = response.data.groups || [];
    } catch (error) {
        console.error('Failed to fetch cadastral groups:', error);
        cadastralGroups.value = [];
    }
};

watch(() => form.owner_id, async (newVal) => {
    if (!isInitialLoad.value) {
        form.cadastral_group_id = '';
    }
    // reset company when owner changes (unless initial load)
    if (!isInitialLoad.value) {
        form.company_id = '';
    }

    if (newVal) {
        await fetchCadastralGroups(newVal);
        // fetch companies for selected owner
        try {
            const compResp = await axios.get('/api/companies', { params: { user_id: newVal } });
            companiesList.value = compResp.data.companies || [];
        } catch (err) {
            console.error('Failed to fetch companies for owner:', err);
        }
    } else {
        cadastralGroups.value = [];
        if (!isInitialLoad.value) {
            form.latitude = '';
            form.longitude = '';
        }
    }
});

watch(() => form.cadastral_group_id, async (newVal) => {
    if (isInitialLoad.value || !newVal) {
        return;
    }

    try {
        const response = await axios.get(route('api.internal.cadastral-groups.get-coordinates'), {
            params: { group_id: newVal }
        });

        if (response.data.coordinates) {
            form.latitude = response.data.coordinates.lat;
            form.longitude = response.data.coordinates.lng;
        }
    } catch (error) {
        console.error('Failed to fetch cadastral group coordinates:', error);
    }
});

onMounted(async () => {
    if (form.owner_id) {
        await fetchCadastralGroups(form.owner_id);
    }
    setTimeout(() => {
        isInitialLoad.value = false;
    }, 100);
});

function submit() {
    form.put(route('sensors.update', props.sensor.id), {
        preserveScroll: true,
    });
}

const cancel = () => {
    router.visit(route('sensors.index'));
};
</script>

<template>
    <Head :title="trans('ui.edit_sensor')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ trans('ui.edit_sensor') }}
            </h1>

            <Card class="max-w-4xl">
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- URN -->
                        <div class="space-y-2">
                            <Label for="urn">{{ trans('ui.urn') }} <span class="text-red-500">*</span></Label>
                            <Input id="urn" v-model="form.urn" type="text" :placeholder="trans('ui.urn')"
                                   :class="{ 'border-destructive': form.errors.urn }" required />
                            <p v-if="form.errors.urn" class="text-sm text-destructive">
                                {{ form.errors.urn }}
                            </p>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">{{ trans('ui.name') }} <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" type="text" :placeholder="trans('ui.name')"
                                :class="{ 'border-destructive': form.errors.name }" required />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Type -->
                        <div class="space-y-2">
                            <Label for="sensor_type_id">{{ trans('ui.sensor_type') }} <span
                                    class="text-red-500">*</span></Label>
                            <Select v-model="form.sensor_type_id" :disabled="form.processing">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="trans('ui.select_sensor_type')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="type in props.types" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.sensor_type_id" />
                        </div>

                        <!-- Firmware -->
                        <div class="space-y-2">
                            <Label for="firmware">{{ trans('ui.firmware') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input id="firmware" v-model="form.firmware" type="text" :placeholder="trans('ui.firmware')"
                                   :class="{ 'border-destructive': form.errors.firmware }" />
                            <InputError :message="form.errors.firmware" />
                        </div>

                        <!-- Serial -->
                        <div class="space-y-2">
                            <Label for="serial_number">{{ trans('ui.serial_number') }}</Label>
                            <Input id="serial" v-model="form.serial_number" type="text"
                                   :placeholder="trans('ui.serial_number')"
                                   :class="{ 'border-destructive': form.errors.serial_number }" />
                            <p v-if="form.errors.serial_number" class="text-sm text-destructive">
                                {{ form.errors.serial_number }}
                            </p>
                        </div>

                        <!-- ICCID -->
                        <div class="space-y-2">
                            <Label for="iccid">{{ trans('ui.iccid') }} <span class="text-red-500">*</span></Label>
                            <Input id="iccid" v-model="form.iccid" type="text"
                                   :placeholder="trans('ui.iccid_placeholder')" required
                                   :class="{ 'border-destructive': form.errors.iccid }" />
                            <p v-if="form.errors.iccid" class="text-sm text-destructive">
                                {{ form.errors.iccid }}
                            </p>
                        </div>

                        <!-- Transmission Module Identification -->
                        <div class="space-y-2">
                            <Label for="transmission_module_identification">{{ trans('ui.transmission_module_identification') }} <span class="text-red-500">*</span></Label>
                            <Input id="transmission_module_identification" v-model="form.transmission_module_identification" type="text" :placeholder="trans('ui.transmission_module_identification_placeholder')"
                                   :class="{ 'border-destructive': form.errors.transmission_module_identification }" required />
                            <p v-if="form.errors.transmission_module_identification" class="text-sm text-destructive">
                                {{ form.errors.transmission_module_identification }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">{{ trans('ui.sensor_description') }}</Label>
                            <textarea id="description" v-model="form.description"
                                :placeholder="trans('ui.sensor_description_placeholder')"
                                class="w-full min-h-[100px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-destructive': form.errors.description }" rows="4"></textarea>
                            <p v-if="form.errors.description" class="text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Owner select field -->
                        <div class="space-y-2">
                            <Label for="owner_id">{{ trans('ui.owner') }}</Label>
                            <Select v-model="form.owner_id" :disabled="form.processing">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="trans('ui.select_owner')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="owner in props.users" :key="owner.id" :value="owner.id">
                                            {{ owner.full_name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.owner_id" />
                        </div>

                        <!-- Company select field -->
                        <div class="space-y-2">
                            <Label for="company_id">{{ trans('ui.company') }}</Label>
                            <Select v-model="form.company_id" :disabled="form.processing">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="trans('ui.select_company')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="company in companiesList" :key="company.id" :value="company.id">
                                            {{ company.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.company_id" />
                        </div>

                        <!-- Cadastral Group select field -->
                        <div v-if="form.owner_id" class="space-y-2">
                            <Label for="cadastral_group_id">{{ trans('ui.cadastral_group') }}</Label>
                            <Select v-model="form.cadastral_group_id" :disabled="form.processing">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="trans('ui.select_cadastral_group')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="group in cadastralGroups" :key="group.id" :value="group.id">
                                            {{ group.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.cadastral_group_id" />
                        </div>

                        <!-- Latitude -->
                        <div class="space-y-2">
                            <Label for="latitude">
                                {{ trans('ui.latitude') }}
                            </Label>
                            <Input id="latitude" v-model="form.latitude" type="number" step="0.000000000000001"
                                :placeholder="trans('ui.sensor_latitude_placeholder')"
                                :class="{ 'border-destructive': form.errors.latitude }" />
                            <p v-if="form.errors.latitude" class="text-sm text-destructive">
                                {{ form.errors.latitude }}
                            </p>
                        </div>

                        <!-- Longitude -->
                        <div class="space-y-2">
                            <Label for="longitude">
                                {{ trans('ui.longitude') }}
                            </Label>
                            <Input id="longitude" v-model="form.longitude" type="number" step="0.000000000000001"
                                :placeholder="trans('ui.sensor_longitude_placeholder')"
                                :class="{ 'border-destructive': form.errors.longitude }" />
                            <p v-if="form.errors.longitude" class="text-sm text-destructive">
                                {{ form.errors.longitude }}
                            </p>
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
