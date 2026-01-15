<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import InputError from '@/components/InputError.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs = [
    { title: trans('ui.planting_schemes'), href: route('planting-schemes.index') },
    {
        title: trans('ui.create'),
        href: route('planting-schemes.create')
    }
];

const form = useForm({
    name: '',
    pattern: '',
    row_spacing: '',
    plant_spacing: '',
    description: ''
});

const props = defineProps<{
    patterns: {id: string, name: string}[];
}>();

function submit() {
  form.post(route('planting-schemes.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

const cancel = () => {
  router.visit(route('planting-schemes.index'));
};
</script>

<template>
    <Head :title="trans('ui.planting_scheme_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.planting_scheme_create_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.planting_scheme_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.planting_scheme_create_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="space-y-2">
                            <Label for="name">
                                {{ trans('ui.name') }} <span class="text-red-500">*</span>
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
                            <Label for="pattern">{{ trans('ui.pattern') }}</Label>
                            <Select v-model="form.pattern" :disabled="form.processing">
                                <SelectTrigger class="h-11 w-[462px]" :data-size="11">
                                    <SelectValue :placeholder="trans('ui.select_pattern')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="pattern in props.patterns" :key="pattern.name" :value="pattern.name">
                                            {{ trans(`ui.${pattern.name}`) }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.pattern" />
                        </div>

                        <div class="space-y-2">
                            <Label for="row_spacing">
                                {{ trans('ui.row_spacing') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="row_spacing"
                                type="number"
                                required
                                step="0.01"
                                v-model="form.row_spacing"
                                :placeholder="trans('ui.row_spacing')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.row_spacing }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.row_spacing" />
                        </div>

                        <div class="space-y-2">
                            <Label for="plant_spacing">
                                {{ trans('ui.plant_spacing') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="plant_spacing"
                                type="number"
                                required
                                step="0.01"
                                v-model="form.plant_spacing"
                                :placeholder="trans('ui.plant_spacing')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.plant_spacing }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.plant_spacing" />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">
                                {{ trans('ui.description') }}
                            </Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :placeholder="trans('ui.description')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.plant_spacing }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? trans('ui.creating') : trans('ui.create_planting_scheme') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
