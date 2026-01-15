<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { trans } from 'laravel-vue-i18n';
import { Role } from '@/types';

const props = defineProps<{
    user: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        mobile_number: string;
        active: boolean;
        terms_and_conditions_id: number | null;
        main_company_id: number | null;
        companies: { id: number; name: string; owner_id: number }[];
    };
    roles: Role[];
    userRole: string;
    latestTerms: {
        id: number;
        version: string;
    } | null;
}>();

const breadcrumbs = [
    {
        title: trans('ui.users'),
        href: route('users.index'),
    },
    {
        title: `${props.user.first_name + ' ' + props.user.last_name}`,
        href: route('users.show', props.user.id),
    },
    {
        title: trans('ui.edit'),
        href: route('users.edit', props.user.id),
    },
];

const form = useForm({
    first_name: props.user.first_name || '',
    last_name: props.user.last_name || '',
    email: props.user.email || '',
    mobile_phone: props.user.mobile_number || '',
    password: '',
    password_confirmation: '',
    active: props.user.active || false,
    role: props.userRole,
    accepted_terms: props.latestTerms ? props.user.terms_and_conditions_id === props.latestTerms.id : false,
    main_company_id: props.user.main_company_id,
});

const submit = () => {
    form.put(route('users.update', { user: props.user.id }), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('users.index'));
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const cancel = () => {
    router.visit(route('users.index'));
};

// const isValidEmail = (email: string) => {
//     const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//     return emailRegex.test(email);
// };
</script>

<template>

    <Head :title="trans('ui.user_edit_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.user_edit_header') }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Personal Information Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.personal_information') }}</CardTitle>
                        <CardDescription>{{ trans('ui.personal_information_desc') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="first_name">{{ trans('ui.first_name') }}</Label>
                                <Input id="first_name" type="text" required v-model="form.first_name"
                                    :placeholder="trans('ui.first_name')" class="h-11" :disabled="form.processing" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name">{{ trans('ui.last_name') }}</Label>
                                <Input id="last_name" type="text" required v-model="form.last_name"
                                    :placeholder="trans('ui.last_name')" class="h-11" :disabled="form.processing" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="email">{{ trans('ui.email') }}</Label>
                                <Input id="email" type="email" required v-model="form.email"
                                    :placeholder="trans('ui.email')" class="h-11" :disabled="form.processing" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="phone">{{ trans('ui.mobile_phone') }}</Label>
                                <Input id="phone" type="text" v-model="form.mobile_phone"
                                    :placeholder="trans('ui.mobile_phone')" class="h-11" :disabled="form.processing" />
                                <InputError :message="form.errors.mobile_phone" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Companies Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.associated_companies') }}</CardTitle>
                        <CardDescription>{{ trans('ui.associated_companies_desc') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="props.user.companies.length === 0" class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ trans('ui.no_companies_associated') }}
                        </div>
                        <div v-else class="flex flex-col gap-4 mb-6">
                            <div v-for="company in props.user.companies" :key="company.id"
                                class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="font-medium">{{ company.name }}</span>
                                    <Badge v-if="company.owner_id === props.user.id" variant="default"
                                        class="bg-blue-700 text-white">
                                        {{ trans('ui.owner') }}
                                    </Badge>
                                    <Badge v-else variant="secondary">
                                        {{ trans('ui.member') }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="main_company_id">{{ trans('ui.main_company') }}</Label>
                            <Select v-model="form.main_company_id"
                                :disabled="form.processing || props.user.companies.length === 0">
                                <SelectTrigger class="h-11 w-full">
                                    <SelectValue :placeholder="trans('ui.select_main_company')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="company in props.user.companies" :key="company.id"
                                            :value="company.id">
                                            {{ company.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.main_company_id" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Security Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.security') }}</CardTitle>
                        <CardDescription>{{ trans('ui.security_access_update') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="type">{{ trans('ui.role') }}<span class="text-red-500">*</span></Label>
                                <Select v-model="form.role" :disabled="form.processing">
                                    <SelectTrigger class="h-11 w-[413px]" :data-size="11">
                                        <SelectValue :placeholder="trans('ui.select_type')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="role in props.roles" :key="role.name" :value="role.name">
                                                {{ trans(`ui.${role.name}`) }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.role" />
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <Switch id="active" v-model="form.active" :disabled="form.processing" />
                                    <Label for="active" class="cursor-pointer">
                                        {{ trans('ui.active') }}
                                    </Label>
                                </div>
                                <InputError :message="form.errors.active" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password">{{ trans('ui.password') }}</Label>
                                <Input id="password" type="password" v-model="form.password"
                                    :placeholder="trans('ui.password')" class="h-11" :disabled="form.processing" />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">{{ trans('ui.confirm_password') }}</Label>
                                <Input id="password_confirmation" type="password" v-model="form.password_confirmation"
                                    :placeholder="trans('ui.confirm_password')" class="h-11"
                                    :disabled="form.processing" />
                                <InputError :message="form.errors.password_confirmation" />
                                <div v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation"
                                    class="text-sm text-red-500">
                                    {{ trans('ui.password_mismatch') }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Terms and Conditions Card -->
                <Card class="max-w-4xl">
                    <CardHeader>
                        <CardTitle>{{ trans('ui.terms_and_conditions') }}</CardTitle>
                        <CardDescription>{{ trans('ui.manage_terms_acceptance') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center space-x-2">
                            <Switch id="accepted_terms" v-model="form.accepted_terms"
                                :disabled="!props.latestTerms || form.processing" />
                            <Label for="accepted_terms" class="cursor-pointer">
                                {{ trans('ui.accepted_latest_tc') }}
                            </Label>
                        </div>
                        <div v-if="!props.latestTerms" class="text-sm text-yellow-600 mt-2">
                            {{ trans('ui.no_active_terms') }}
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-2 pt-4">
                    <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                        {{ trans('ui.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? trans('ui.updating') : trans('ui.update_user') }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
