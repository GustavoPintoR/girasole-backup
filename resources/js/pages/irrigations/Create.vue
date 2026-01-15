<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { trans } from 'laravel-vue-i18n';


const breadcrumbs = [
    { title: trans('ui.irrigations'), href: route('irrigations.index') },
    { title: trans('ui.create'), href: route('irrigations.create') },
];

const form = useForm({
    type: '',
    description: '',
});

const submit = () => {
    form.post(route('irrigations.store'), {
        preserveScroll: true,
    });
};

const cancel = () => {
    router.visit(route('irrigations.index'));
};
</script>

<template>
    <Head :title="trans('ui.irrigation_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.irrigation_create_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.irrigation_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.irrigation_create_desc') }}
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="type">
                                {{ trans('ui.type') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="type"
                                type="text"
                                required
                                v-model="form.type"
                                :placeholder="trans('ui.type')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.type }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.type" />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">{{ trans('ui.description') }}</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :placeholder="trans('ui.description')"
                                class="min-h-24 w-[462px]"
                                :class="{ 'border-red-500': form.errors.description }"
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
                                {{ form.processing ? trans('ui.creating') : trans('ui.create_irrigation') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
