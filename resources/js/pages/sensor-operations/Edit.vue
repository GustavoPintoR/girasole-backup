<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import InputError from '@/components/InputError.vue';
import { SensorOperation } from '@/types';

const props = defineProps<{
    sensorOperation: SensorOperation;
}>();

const breadcrumbs = [
    { title: trans('ui.sensor_operation'), href: route('sensor-operations.index') },
    { title: trans('ui.edit'), href: route('sensor-operations.edit', props.sensorOperation.id) }
];

const form = useForm({
    name: props.sensorOperation.name,
    label: props.sensorOperation.label,
});

function submit() {
    form.put(route('sensor-operations.update', props.sensorOperation.id), {
        preserveScroll: true,
        onSuccess: () => {
            //
        },
    });
}

const cancel = () => {
    router.visit(route('sensor-operations.index'));
};
</script>

<template>
    <Head :title="trans('ui.sensor_operation_edit_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.sensor_operation_edit_header') }}
                </h1>
            </div>

            <Card class="w-full">
                <CardHeader>
                    <CardTitle>{{ trans('ui.sensor_operation_edit_header_details') }}</CardTitle>
                    <CardDescription>{{ trans('ui.sensor_operation_edit_edit_desc') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-medium">
                                {{ trans('ui.name') }}
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                v-model="form.name"
                                :placeholder="trans('ui.name')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.name }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-medium">
                                {{ trans('ui.label') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="label"
                                type="text"
                                required
                                v-model="form.label"
                                :placeholder="trans('ui.label')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.label }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.label" />
                        </div>

                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                        {{ trans('ui.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing" @click="submit">
                        {{ form.processing ? trans('ui.updating') : trans('ui.edit_sensor_operation') }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
