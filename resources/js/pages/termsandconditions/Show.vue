<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { InfoIcon, Edit, ArrowLeft, Calendar, Clock, FileText, Hash } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { it } from 'date-fns/locale';

interface TermsAndConditions {
    id: number;
    version: string;
    description: string;
    description_html: string;
    summary: string;
    summary_html: string;
    is_active: boolean;
    active_at: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    terms: TermsAndConditions;
}>();

const breadcrumbs = [
    {
        title: trans('ui.terms_and_conditions'),
        href: route('terms-and-conditions.index'),
    },
    {
        title: trans('ui.version_title', { version: props.terms.version }),
        href: route('terms-and-conditions.show', props.terms.id),
    },
];

const handleBack = () => {
    router.visit(route('terms-and-conditions.index'));
};

const handleEdit = () => {
    router.visit(route('terms-and-conditions.edit', props.terms.id));
};

const { can } = usePermissions();
</script>

<template>

    <Head :title="trans('ui.terms_show_header', { version: props.terms.version })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.terms_and_conditions') }}
                    </h1>
                    <Badge v-if="props.terms.is_active" variant="default" class="bg-green-700">
                        {{ trans('ui.active') }}
                    </Badge>
                    <Badge v-else variant="secondary">
                        {{ trans('ui.inactive') }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="can.update_terms_and_conditions" @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <!-- Status info -->
            <Alert v-if="props.terms.is_active"
                class="mb-6 border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 max-w-4xl">
                <InfoIcon class="h-4 w-4 text-blue-600" />
                <AlertDescription class="text-blue-800 dark:text-blue-200">
                    {{ trans('ui.terms_status_info') }}
                </AlertDescription>
            </Alert>

            <!-- Version Information -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Hash class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.version_info') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                {{ trans('ui.version') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.terms.version }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                {{ trans('ui.status') }}</dt>
                            <dd>
                                <Badge v-if="props.terms.is_active" variant="default" class="bg-green-700">
                                    {{ trans('ui.active') }}
                                </Badge>
                                <Badge v-else variant="secondary">
                                    {{ trans('ui.inactive') }}
                                </Badge>
                            </dd>
                        </div>
                        <div v-if="props.terms.active_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                {{ trans('ui.active_date') }}</dt>
                            <dd class="text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <Calendar class="h-4 w-4 text-gray-500" />
                                {{ format(parseISO(props.terms.active_at), 'PPP') }}
                            </dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle>{{ trans('ui.terms_summary') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.terms_summary_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-sm dark:prose-invert max-w-none"
                            v-html="props.terms.summary_html">
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Description -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.full_terms') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.full_terms_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-sm dark:prose-invert max-w-none"
                            v-html="props.terms.description_html">
                        </p>
                    </div>
                </CardContent>
            </Card>

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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.created_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.terms.created_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.terms.updated_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div v-if="props.terms.active_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.active_since') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.terms.active_at), 'PPP', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.record_id') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                #{{ props.terms.id }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
