<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { BreadcrumbItem, Plan, User } from '@/types';
import { trans } from 'laravel-vue-i18n';
import { AlertCircle } from 'lucide-vue-next';
import Card from '@/components/ui/card/Card.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Label from '@/components/ui/label/Label.vue';
import Select from '@/components/ui/select/Select.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import InputError from '@/components/InputError.vue';
import Alert from '@/components/ui/alert/Alert.vue';
import AlertTitle from '@/components/ui/alert/AlertTitle.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';

interface Subscription {
    stripe_price: string;
    stripe_status: string;
    ends_at: string | null;
    on_grace_period: boolean;
}

interface Props {
    plan?: Plan | null;
    plans?: Plan[] | null;
    ends_at?: string;
    is_on_grace_period: boolean;
    currentSubscription: Subscription | null;
    user: User;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: trans('ui.subscription_settings'),
        href: '/settings/subscription',
    },
];


// Cancel subscription form
const form = useForm({
    plan_id: props.plan?.id ?? null,
});
const cancel = () => {
    form.post(route('billing.cancel'), {
        preserveScroll: true,
        onSuccess: () => {
            // toast or flash can be used
        },
    });
};

// Update subscription form
const submit = () => {
    form.post(route('settings.subscription.update-own-plan'), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: currency.toUpperCase(),
    }).format(amount / 100);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head :title="trans('ui.subscription_settings')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title="trans('ui.subscription_information')"
                    :description="trans('ui.subscription_information_desc')" />

                <div v-if="plan" class="space-y-4">
                    <div class="border rounded-lg p-4">
                        <p class="text-lg font-medium">{{ plan.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ trans('ui.price') }}:
                            {{ (plan.unit_amount / 100).toFixed(2) }} {{ plan.currency.toUpperCase() }}
                            / {{ plan.interval }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ trans('ui.status') }}:
                            <span v-if="plan.active">{{ trans('ui.active') }}</span>
                            <span v-else>{{ trans('ui.inactive') }}</span>
                        </p>
                    </div>

                    <Button variant="destructive" :disabled="form.processing" @click="cancel">
                        {{ trans('ui.cancel_subscription') }}
                    </Button>

                    <!-- Helper text -->
                    <p class="text-sm text-muted-foreground mt-2" v-if="ends_at">
                        {{ trans('ui.cancelled_subscription_helper') }} {{ ends_at }}
                    </p>
                </div>

                <div v-else-if="is_on_grace_period">
                    <p class="text-sm text-muted-foreground" v-if="ends_at">
                        {{ trans('ui.cancelled_subscription_helper') }} {{ ends_at }}
                    </p>
                </div>

                <div v-else>
                    <p class="text-sm text-muted-foreground">
                        {{ trans('ui.no_active_subscription') }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" v-if="props.currentSubscription?.stripe_status == 'canceled'">
                <Card class="max-w-4xl mb-6">
                    <CardHeader>
                        <CardTitle>{{ currentSubscription ? trans('ui.change_plan') : trans('ui.assign_plan') }}
                        </CardTitle>
                        <CardDescription>
                            {{ currentSubscription ? trans('ui.change_plan_desc') : trans('ui.assign_plan_desc') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <Label for="plan_id">{{ trans('ui.select_plan') }}<span
                                        class="text-red-500">*</span></Label>
                                <Select v-model="form.plan_id" :disabled="form.processing">
                                    <SelectTrigger class="h-11 w-full">
                                        <SelectValue :placeholder="trans('ui.select_plan_placeholder')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="plan in plans" :key="plan.id" :value="plan.id">
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ plan.name }}</span>&nbsp;
                                                    <span class="text-sm text-gray-500">
                                                        {{ formatCurrency(plan.unit_amount, plan.currency) }} /
                                                        {{ plan.interval }}
                                                    </span>
                                                </div>
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.plan_id" />
                            </div>

                            <!-- Selected Plan Preview -->
                            <div v-if="form.plan_id" class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                                <h4 class="font-semibold mb-2">{{ trans('ui.selected_plan_preview') }}</h4>
                                <div v-for="plan in plans?.filter(p => p.id === form.plan_id)" :key="plan.id">
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.plan_name') }}</span>
                                            <span class="text-sm font-medium">{{ plan.name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.price') }}</span>
                                            <span class="text-sm font-medium">
                                                {{ formatCurrency(plan.unit_amount, plan.currency) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.billing_interval') }}</span>
                                            <span class="text-sm font-medium">{{ plan.interval }}</span>
                                        </div>
                                        <div v-if="plan.features && plan.features.length > 0" class="pt-2 border-t">
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400 block mb-2">{{ trans('ui.features') }}</span>
                                            <ul class="list-disc list-inside space-y-1">
                                                <li v-for="(feature, index) in plan.features" :key="index"
                                                    class="text-sm">
                                                    {{ feature }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning Alert -->
                            <Alert v-if="currentSubscription && form.plan_id !== plan?.id">
                                <AlertCircle class="h-4 w-4" />
                                <AlertTitle>{{ trans('ui.plan_change_warning') }}</AlertTitle>
                                <AlertDescription>
                                    {{ trans('ui.plan_change_warning_desc') }}
                                </AlertDescription>
                            </Alert>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-2 max-w-4xl">
                    <Button type="submit" :disabled="form.processing || !form.plan_id">
                        {{ form.processing ? trans('ui.updating') : (currentSubscription ? trans('ui.update_plan') : trans('ui.assign_plan')) }}
                    </Button>
                </div>
            </form>

        </SettingsLayout>
    </AppLayout>
</template>
