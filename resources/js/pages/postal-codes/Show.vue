<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { MapPin, Edit, ArrowLeft, Clock } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { it } from 'date-fns/locale';

interface City {
    id: number;
    name: string;
    province?: { id: number; name: string; region?: { id: number; name: string } };
    region?: { id: number; name: string };
    pivot: {
        zone: string | null;
        notes: string | null;
    };
}

interface PostalCode {
    id: number;
    code: string;
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
];

const handleBack = () => {
    router.visit(route('postal-codes.index'));
};

const handleEdit = () => {
    router.visit(route('postal-codes.edit', props.postalCode.id));
};

const { can } = usePermissions();

// const hasNotes = computed(() => {
//     return props.postalCode.cities.some(city => city.pivot.notes);
// });

// const filteredCities = computed(() => {
//     return props.postalCode.cities.filter(city => city.pivot.notes);
// });

</script>

<template>
    <Head :title="trans('ui.postal_code_show_header', { postal_code: props.postalCode.code })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.postal_codes') }}
                    </h1>
                    <Badge variant="default" class="bg-blue-700 dark:text-white">
                        {{ trans('ui.postal_code') }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="can.update_postal_code" @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <!-- Postal Code Information -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <MapPin class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.postal_code_info') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.postal_code') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.postalCode.code }}
                            </dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Location Details -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle>{{ trans('ui.location_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.location_details_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="props.postalCode.cities.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ trans('ui.city') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ trans('ui.province') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ trans('ui.region') }}</th>
<!--                                    <th scope="col" class="px-6 py-3">{{ trans('ui.zone') }}</th>-->
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="city in props.postalCode.cities" :key="city.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4">{{ city.name }}</td>
                                    <td class="px-6 py-4">{{ city.province?.name ?? trans('ui.none') }}</td>
                                    <td class="px-6 py-4">{{ city.province?.region?.name ?? trans('ui.none') }}</td>
<!--                                    <td class="px-6 py-4">{{ city.pivot.zone ?? trans('ui.none') }}</td>-->
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400">
                        {{ trans('ui.no_cities_associated') }}
                    </div>
                </CardContent>
            </Card>

            <!-- Notes -->
<!--            <Card v-if="hasNotes" class="max-w-4xl mb-6">-->
<!--                <CardHeader>-->
<!--                    <CardTitle class="flex items-center gap-2">-->
<!--                        <FileText class="h-5 w-5 text-gray-500" />-->
<!--                        {{ trans('ui.notes') }}-->
<!--                    </CardTitle>-->
<!--                    <CardDescription>-->
<!--                        {{ trans('ui.notes_desc') }}-->
<!--                    </CardDescription>-->
<!--                </CardHeader>-->
<!--                <CardContent>-->
<!--                    <div class="prose prose-sm dark:prose-invert max-w-none">-->
<!--                        <div v-for="city in filteredCities" :key="city.id">-->
<!--                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-700 mb-4">-->
<!--                                <p class="text-gray-700 dark:text-gray-300 font-semibold mb-2">-->
<!--                                    {{ trans('ui.notes_for_city', { city: city.name }) }}-->
<!--                                </p>-->
<!--                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">-->
<!--                                    {{ city.pivot.notes }}-->
<!--                                </p>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </CardContent>-->
<!--            </Card>-->

            <!-- Metadata -->
            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.metadata') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.created_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.postalCode.created_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.postalCode.updated_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.record_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                #{{ props.postalCode.id }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
