<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import InputError from '@/components/InputError.vue';

const breadcrumbs = [
    { title: trans('ui.sensor_fields'), href: route('sensor-fields.index') },
    {
        title: trans('ui.create'),
        href: route('sensor-fields.create')
    }
];

const form = useForm({
    name: '',
    label: '',
    unit: ''
});

function submit() {
  form.post(route('sensor-fields.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

const cancel = () => {
  router.visit(route('sensor-fields.index'));
};
</script>

<template>
    <Head :title="trans('ui.sensor_field_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.sensor_field_create_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.sensor_field_create_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.sensor_field_create_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="space-y-2">
                            <Label for="name">
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
                            <Label for="label">
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

                        <div class="space-y-2">
                            <Label for="unit">
                                {{ trans('ui.unit_of_measurement') }}
                            </Label>
                            <Input
                                id="label"
                                type="text"
                                v-model="form.unit"
                                :placeholder="trans('ui.unit_of_measurement')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.unit }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.unit" />
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? trans('ui.creating') : trans('ui.create_sensor_field') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
