<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Download, Calendar, Clock, CheckCircle2, XCircle, FileJson } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';
import { format, parseISO } from 'date-fns';
import { it, enUS } from 'date-fns/locale';
import { usePage } from '@inertiajs/vue3';

import type { ForecastLog, CadastralGroup } from '@/types';

const props = defineProps<{
    forecastLog: ForecastLog & {
        field: CadastralGroup;
    };
}>();

const page = usePage();
const locale = computed(() => (page.props.appLocale === 'it' ? it : enUS));

const breadcrumbs = [
    {
        title: trans('ui.forecast_logs'),
        href: route('forecast-logs.index'),
    },
    {
        title: `#${props.forecastLog.id} – ${props.forecastLog.field.name}`,
        href: route('forecast-logs.show', props.forecastLog.id),
    },
];

type StatusKey = 'pending' | 'success' | 'failed';

const statusConfig: Record<StatusKey, { label: string; variant: any; icon: any }> = {
    pending: { label: trans('ui.pending'), variant: 'secondary', icon: Clock },
    success: { label: trans('ui.success'), variant: 'default', icon: CheckCircle2 },
    failed: { label: trans('ui.failed'), variant: 'destructive', icon: XCircle },
};

const currentStatus = computed(() => {
    const key = props.forecastLog.status.toLowerCase() as StatusKey;
    return statusConfig[key] || statusConfig.pending;
});

const downloadJson = () => {
    if (!props.forecastLog.data) {
        alert(trans('ui.no_data_to_download'));
        return;
    }

    const jsonString = JSON.stringify(props.forecastLog.data, null, 2);
    const blob = new Blob([jsonString], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `forecast-log-${props.forecastLog.id}-${props.forecastLog.field.name}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};
</script>

<template>
    <Head :title="`${trans('ui.forecast_log')} #${forecastLog.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold flex items-center gap-3">
                        {{ trans('ui.forecast_log') }} #{{ forecastLog.id }}
                        <Badge :variant="currentStatus.variant" class="px-3 py-1">
                            <component :is="currentStatus.icon" class="w-4 h-4 mr-1.5" />
                            {{ currentStatus.label }}
                        </Badge>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                        {{ forecastLog.field.name }}
                        <span
                            v-if="forecastLog.field.color"
                            class="inline-block w-5 h-5 rounded ml-2 align-middle border border-gray-300"
                            :style="{ backgroundColor: forecastLog.field.color }"
                        ></span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Execution Details -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.execution_details') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-muted-foreground">{{ trans('ui.field') }}</p>
                                <p class="font-medium text-lg">{{ forecastLog.field.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">{{ trans('ui.field_id') }}</p>
                                <p class="font-medium text-lg">{{ forecastLog.field.id }}</p>
                            </div>

                            <div>
                                <div>
                                    <p class="text-sm text-muted-foreground">{{ trans('ui.status') }}</p>
                                    <Badge :variant="currentStatus.variant">
                                        {{ currentStatus.label }}
                                    </Badge>
                                </div>
                            </div>

                            <div>
                                <p class="text-sm text-muted-foreground">{{ trans('ui.executed_at') }}</p>
                                <p class="font-medium flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    {{ forecastLog.ran_at
                                    ? format(parseISO(forecastLog.ran_at), 'PPPp', { locale })
                                    : '—'
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">{{ trans('ui.created_at') }}</p>
                                <p class="text-sm">
                                    {{ format(parseISO(forecastLog.created_at), 'PPPp', { locale }) }}
                                </p>
                            </div>
                        </div>

                        <!-- Parameters -->
                        <div v-if="forecastLog.parameters && Object.keys(forecastLog.parameters).length">
                            <p class="text-sm font-medium text-muted-foreground mb-2">
                                {{ trans('ui.parameters') }}
                            </p>
                            <pre class="text-xs bg-muted p-4 rounded-lg overflow-x-auto border"><code>{{ JSON.stringify(forecastLog.parameters, null, 2) }}</code></pre>
                        </div>
                        <div v-else class="text-sm text-muted-foreground italic">
                            {{ trans('ui.no_parameters') }}
                        </div>
                    </CardContent>
                </Card>

                <!-- Data Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileJson class="h-5 w-5" />
                            {{ trans('ui.forecast_data') }}
                        </CardTitle>

                    </CardHeader>
                    <CardContent class="text-center py-8">
                        <FileJson class="h-16 w-16 text-muted-foreground mx-auto mb-4 opacity-50" />
                        <p class="text-sm text-muted-foreground mb-4">
                            {{ forecastLog.data
                            ? trans('ui.data_ready_for_download')
                            : trans('ui.waiting_for_execution')
                            }}
                        </p>
                        <Button
                            variant="outline"
                            @click="downloadJson"
                            :disabled="!forecastLog.data"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            {{ trans('ui.download_json') }}
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
