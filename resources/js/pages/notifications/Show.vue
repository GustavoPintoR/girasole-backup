<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Captions, ArrowLeft, Clock, CheckCheck, X } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { Notification } from '@/types';
import { it } from 'date-fns/locale';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    notification: Notification;
}>();

const data = typeof props.notification.data === 'string' ? JSON.parse(props.notification.data) : props.notification.data;

const title = data.context.title;
const description = data.context.description;
const sentToEmail = data.context.send_to_email;

const breadcrumbs = [
    {
        title: trans('ui.notifications'),
        href: route('notifications.index'),
    },
    {
        title: title,
        href: route('notifications.show', props.notification.id),
    },
];

const handleBack = () => {
    router.visit(route('notifications.index'));
};

function markAsRead(id: string) {
    router.get(route('notification.markAsRead', id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="trans('ui.notification_show_header', { notification: title })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.notification_show_header', { notification: title }) }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button variant="destructive" v-if="!props.notification.read_at" @click="markAsRead(props.notification.id)">
                        <CheckCheck class="h-4 w-4 mr-2" />
                        {{ trans('ui.mark_as_read') }}
                    </Button>
                </div>
            </div>

            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Captions class="h-5 w-5 text-gray-500" />
                        {{ title }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="w-full md:grid-cols-2 gap-6">


                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.description') }}</dt>
                            <dd class="text-gray-900 dark:text-gray-100" v-html="description">
                            </dd>
                        </div>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.read_at') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <Badge v-if="props.notification.read_at">
                                    {{ format(parseISO(props.notification.read_at as string), 'PPP', { locale: it }) }}
                                </Badge>

                                <Badge v-else variant="secondary">
                                    {{ trans('ui.unread') }}
                                </Badge>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ trans('ui.sent_to_email') }}</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <Badge v-if="sentToEmail">
                                    <CheckCheck class="h-10 w-10"   />
                                </Badge>

                                <Badge v-else variant="destructive">
                                    <X class="h-10 w-10" />
                                </Badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.created_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.notification.created_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.notification.updated_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.record_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                #{{ props.notification.id }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
