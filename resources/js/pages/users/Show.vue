```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Edit, ArrowLeft, Clock, User, Mail, Phone, FileText, Lock, Key, Building } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { it } from 'date-fns/locale';
import { computed } from 'vue';

interface Company {
    id: number;
    name: string;
    owner_id: number;
}

interface TermsAndConditions {
  version?: string | null;
}

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    mobile_number: string;
    active: boolean;
    created_at: string;
    updated_at: string;
    accepted_at: string;
    companies: Company[];
    main_company_id: number | null;
    terms_and_conditions?: TermsAndConditions;
}


const props = defineProps<{
    user: User;
    userRole: string;
}>();

const breadcrumbs = [
    {
        title: trans('ui.users'),
        href: route('users.index'),
    },
    {
        title: `${props.user.first_name} ${props.user.last_name}`,
        href: route('users.show', props.user.id),
    },
];

const handleBack = () => {
    router.visit(route('users.index'));
};

const handleEdit = () => {
    router.visit(route('users.edit', props.user.id));
};

const { can } = usePermissions();

const formattedTCAcceptedAt = computed(() => {
  const ts = props.user?.accepted_at;
  console.log(props.user?.accepted_at);
  return ts ? format(parseISO(ts), 'PPP', { locale: it }) : null;
});
</script>

<template>
    <Head :title="trans('ui.user_show_header', { name: `${props.user.first_name} ${props.user.last_name}` })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.user_details') }}
                    </h1>
                    <Badge v-if="props.user.active" variant="default" class="bg-green-700">
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
                    <Button v-if="can.update_user" @click="handleEdit">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.edit') }}
                    </Button>
                </div>
            </div>

            <!-- Personal Information -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.personal_information') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.personal_information_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.first_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ props.user.first_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ props.user.last_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.email_address') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <Mail class="h-4 w-4 text-gray-500" />
                                {{ props.user.email }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.mobile_phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <Phone class="h-4 w-4 text-gray-500" />
                                {{ props.user.mobile_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.role') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <Key class="h-4 w-4 text-gray-500" />
                                <Badge>{{ trans(`ui.${props.userRole}`) }}</Badge>
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Companies -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Building class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.associated_companies') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.associated_companies_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="props.user.companies.length === 0" class="text-gray-600 dark:text-gray-400">
                        {{ trans('ui.no_companies_associated') }}
                    </div>
                    <div v-else class="flex flex-col gap-4">
                        <div v-for="company in props.user.companies" :key="company.id" class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="font-medium">{{ company.name }}</span>
                                <Badge v-if="company.owner_id === props.user.id" variant="default" class="bg-blue-700 text-white">
                                    {{ trans('ui.owner') }}
                                </Badge>
                                <Badge v-else variant="secondary">
                                    {{ trans('ui.member') }}
                                </Badge>
                                <Badge v-if="company.id === props.user.main_company_id" variant="outline" class="border-green-600 text-green-600">
                                    {{ trans('ui.main_company') }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Security -->
            <Card class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Lock class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.status') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.status_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.status') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <Badge v-if="props.user.active" variant="default" class="bg-green-700">
                                    {{ trans('ui.active') }}
                                </Badge>
                                <Badge v-else variant="secondary">
                                    {{ trans('ui.inactive') }}
                                </Badge>
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Terms & Conditions -->
            <Card class="max-w-4xl mb-6">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5 text-gray-500" />
                {{ trans('ui.terms_and_conditions') }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ trans('ui.accepted_at') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ formattedTCAcceptedAt ?? '—' }}
                    </dd>
                </div>

                <!-- Optional: show version if present -->
                <div v-if="props.user?.terms_and_conditions?.version">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ trans('ui.version') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ props.user?.terms_and_conditions?.version }}
                    </dd>
                </div>
                </dl>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.created_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.user.created_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(props.user.updated_at), 'PPP p', {locale: it }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.record_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                #{{ props.user.id }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
