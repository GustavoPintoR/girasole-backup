<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { trans } from 'laravel-vue-i18n';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { BillingInfo } from '@/types';

const props = defineProps<{
    user: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        mobile_number: string;
        active: boolean;
        billing_info: BillingInfo
    };
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: trans('ui.billing_info'),
        href: '/settings/billing/info',
    },
];

const form = useForm({
    type: props.user?.billing_info?.fiscal_type || '',
    fiscal_code: props.user?.billing_info?.fiscal_code || '',
    vat_number: props.user?.billing_info?.vat_number || '',
    sdi_code: props.user?.billing_info?.sdi || '',
    business_name: props.user?.billing_info?.business_name || '',
});

const submit = () => {
    form.patch(route('profile.billing.info.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="trans('ui.billing_info')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title="trans('ui.billing_info')" :description="trans('ui.billing_information')" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="type">{{ trans('ui.type') }}<span class="text-red-500">*</span></Label>
                        <Select v-model="form.type" :disabled="form.processing">
                            <SelectTrigger class="h-11 w-full">
                                <SelectValue :placeholder="trans('ui.select_type')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="business">{{ trans('ui.business') }}</SelectItem>
                                    <SelectItem value="sole_business">{{ trans('ui.sole_business') }}</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="fiscal_code">{{ trans('ui.fiscal_code') }}<span class="text-red-500">*</span></Label>
                        <Input
                            id="fiscal_code"
                            type="text"
                            required
                            v-model="form.fiscal_code"
                            :placeholder="trans('ui.fiscal_code')"
                            class="h-11"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.fiscal_code" />
                    </div>

                    <div v-if="form.type === 'business'" class="grid gap-2">
                        <Label for="vat_number">{{ trans('ui.vat_number') }}<span class="text-red-500">*</span></Label>
                        <Input
                            id="vat_number"
                            type="text"
                            required
                            v-model="form.vat_number"
                            :placeholder="trans('ui.vat_number')"
                            class="h-11"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.vat_number" />
                    </div>

                    <div v-if="form.type === 'business'" class="grid gap-2">
                        <Label for="sdi_code">{{ trans('ui.sdi_code') }}<span class="text-red-500">*</span></Label>
                        <Input
                            id="sdi_code"
                            type="text"
                            required
                            v-model="form.sdi_code"
                            :placeholder="trans('ui.sdi_code')"
                            class="h-11"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.sdi_code" />
                    </div>

                    <div v-if="form.type === 'business'" class="grid gap-2">
                        <Label for="business_name">{{ trans('ui.business_name') }}<span class="text-red-500">*</span></Label>
                        <Input
                            id="business_name"
                            type="text"
                            required
                            v-model="form.business_name"
                            :placeholder="trans('ui.business_name')"
                            class="h-11"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.business_name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ trans('ui.save') }}</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">{{ trans('ui.saved') }}.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
