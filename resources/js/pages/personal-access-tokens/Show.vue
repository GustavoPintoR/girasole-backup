<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { MapPin, ArrowLeft, Copy, RefreshCw, AlertCircle } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { PersonalAccessToken } from '@/types';
import { usePermissions } from '@/composables/usePermissions';
import { it } from 'date-fns/locale';
import { Badge } from '@/components/ui/badge';
import { ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { toast } from 'vue-sonner';

const props = defineProps<{
    token: PersonalAccessToken;
    plainTextToken?: string;
}>();

const isCopied = ref(false);

const breadcrumbs = [
    {
        title: trans('ui.api_keys'),
        href: route('api-keys.index'),
    },
    {
        title: props.token.name,
        href: route('api-keys.show', props.token.id),
    },
];

const handleBack = () => {
    router.visit(route('api-keys.index'));
};

// const handleEdit = () => {
//     router.visit(route('api-keys.edit', props.token.id));
// };

const handleCopy = () => {
    if (props.plainTextToken) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(props.plainTextToken).then(() => {
                isCopied.value = true;
                toast(trans('ui.copied'), {
                    description: trans('ui.token_copied'),
                    action: {
                        label: trans('ui.undo'),
                        onClick: () => console.log('Undo copy action'),
                    },
                });
                setTimeout(() => (isCopied.value = false), 2000);
            }).catch((err) => {
                console.error('Failed to copy token:', err);
                toast(trans('ui.error'), {
                    description: trans('ui.copy_failed'),
                });
            });
        } else {
            toast(trans('ui.warning'), {
                description: trans('ui.manual_copy'),
            });
        }
    }
};

const handleRegenerate = () => {
    router.post(route('api-keys.regenerate', props.token.id), {}, {
        onSuccess: () => {
            toast(trans('ui.success'), {
                description: trans('ui.token_regenerated'),
                action: {
                    label: trans('ui.undo'),
                    onClick: () => console.log('Undo regenerate action'),
                },
            });
        },
    });
};

const { can } = usePermissions();
</script>

<template>
    <Head :title="trans('ui.api_key_show_header', { key: props.token.name })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.api_key_show_header', { key: props.token.name }) }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button variant="destructive" v-if="can.update_personal_access_token" @click="handleRegenerate">
                        <RefreshCw class="h-4 w-4 mr-2" />
                        {{ trans('ui.regenerate') }}
                    </Button>
                </div>
            </div>

            <!-- Token Display and Warning -->
            <Card v-if="props.plainTextToken" class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        {{ trans('ui.new_api_key') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <Alert class="mb-4">
                        <AlertCircle class="h-4 w-4" />
                        <AlertTitle>{{ trans('ui.warning') }}</AlertTitle>
                        <AlertDescription>
                            {{ trans('ui.token_warning') }}
                        </AlertDescription>
                    </Alert>
                    <div class="flex items-center gap-2">
                        <code class="text-sm bg-gray-100 dark:bg-gray-800 p-2 rounded break-all">
                            {{ props.plainTextToken }}
                        </code>
                        <Button variant="outline" @click="handleCopy">
                            <Copy class="h-4 w-4 mr-2" />
                            {{ isCopied ? trans('ui.copied') : trans('ui.copy') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Token Info -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <MapPin class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.api_key_info') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.token') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.token.name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.api_key_abilities') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.token.abilities?.join(', ') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.user') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ props.token.tokenable?.full_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.expires_at') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <Badge v-if="props.token.expires_at">
                                    {{ format(parseISO(props.token.expires_at as string), 'PPP', { locale: it }) }}
                                </Badge>
                                <Badge v-else variant="secondary">
                                    {{ trans('ui.none') }}
                                </Badge>
                            </dd>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
