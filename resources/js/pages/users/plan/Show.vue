<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Edit, ArrowLeft, CreditCard, Calendar, DollarSign, Package } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { it } from 'date-fns/locale';
import { capitalize, ref, watch } from 'vue';
import { Plan } from '@/types';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import { toast } from 'vue-sonner';
import { isEmpty } from '@unovis/ts';


interface Subscription {
    id: number;
    stripe_id: string;
    stripe_price: string;
    stripe_status: string;
    quantity: number;
    trial_ends_at: string | null;
    ends_at: string | null;
    created_at: string;
    updated_at: string;
    on_trial: boolean;
    on_grace_period: boolean;
    canceled: boolean;
    active: boolean;
    type?: string;
}

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
}

const props = defineProps<{
    user: User;
    currentSubscription: Subscription | null;
    currentPlan: Plan | null;
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
    {
        title: trans('ui.view_plan'),
        href: route('users.view-plan', props.user.id),
    },
];

const handleBack = () => {
    router.visit(route('users.show', props.user.id));
};

const handleManagePlan = () => {
    router.visit(route('users.manage-plan', props.user.id));
};

const handleViewPlanDetails = () => {
    if (props.currentPlan) {
        router.visit(route('plans.show', props.currentPlan.id));
    }
};

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'trialing':
            return 'secondary';
        case 'canceled':
        case 'incomplete':
        case 'incomplete_expired':
        case 'past_due':
        case 'unpaid':
            return 'destructive';
        default:
            return 'outline';
    }
};

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: currency.toUpperCase(),
    }).format(amount / 100);
};

const { can } = usePermissions();

const initialEndsAtValue = props.currentSubscription?.ends_at
  ? format(parseISO(props.currentSubscription.ends_at), "yyyy-MM-dd'T'HH:mm")
  : '';

  const form = useForm({
    plan_id: props.currentPlan?.id ?? null,
    ends_at: initialEndsAtValue || null,
    quantity: props.currentSubscription?.quantity ?? null  as number | null,
});

const endsAtError = ref<string | null>(null);
const quantity = ref<number | null>(props.currentSubscription?.quantity) // Reactive variable for quantity input

const submit = () => {
  if (!validateQuantity() || !validateEndsAt()) {
    toast.error('Validation failed');
    return;
  }
  
  form.quantity = quantity.value

  form.post(route('users.set-plan-details', props.user.id), {
    preserveScroll: true,
    onError: (errors) => toast.error(errors.message || 'Error saving'),
  });
};

const validateEndsAt = () => {
  endsAtError.value = null;
  if (props.currentPlan?.interval !== 'custom') return true;

  const chosen = isEmpty(form.ends_at) ? null : new Date(form.ends_at as string);
  const now = new Date();

  if (chosen && (isNaN(chosen.getTime()) || chosen <= now)) {
    endsAtError.value = trans('ui.end_date_error_future') || 'End date must be in the future.';
    return false;
  }

  return true;
};

// Validation for quantity
const quantityError = ref<string | null>(null)

const validateQuantity = () => {
    if (!Number.isInteger(Number(quantity.value)) || quantity.value < 1) {
        quantityError.value = trans('ui.invalid_quantity')
        return false
    }
    quantityError.value = null
    return true
}

watch(
  () => form.plan_id,
  () => {
    if (props.currentPlan?.interval !== 'custom') {
    //   endsAtLocal.value = '';
      endsAtError.value = null;
      form.ends_at = null;
    }
  }
);
</script>

<template>

    <Head :title="trans('ui.view_plan')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.subscription_plan') }}
                    </h1>
                    <Badge v-if="currentSubscription"
                        :variant="getStatusBadgeVariant(currentSubscription.stripe_status)">
                        {{ currentSubscription.stripe_status }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Button v-if="can.update_user" @click="handleManagePlan">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ trans('ui.manual_plan') }}
                    </Button>
                </div>
            </div>

            <!-- No Subscription -->
            <Card v-if="!currentSubscription" class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Package class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.no_subscription') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.no_subscription_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-center py-6">
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ trans('ui.user_not_subscribed') }}
                        </p>
                        <Button v-if="can.update_user" @click="handleManagePlan">
                            {{ trans('ui.assign_plan') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Current Plan Details -->
            <Card v-if="currentPlan" class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Package class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.current_plan') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.current_plan_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.plan_name') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                {{ currentPlan.name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.billing_interval') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ trans("ui."+currentPlan.interval) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <DollarSign class="h-4 w-4" />
                                {{ trans('ui.price') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                {{ formatCurrency(currentPlan.unit_amount, currentPlan.currency) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.stripe_price_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                {{ currentPlan.stripe_price_id }}
                            </dd>
                        </div>
                        <div v-if="currentPlan.features && currentPlan.features.length > 0" class="col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                {{ trans('ui.features') }}</dt>
                            <dd class="mt-1">
                                <ul class="list-disc list-inside space-y-1">
                                    <li v-for="(feature, index) in currentPlan.features" :key="index"
                                        class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ feature }}
                                    </li>
                                </ul>
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <Button variant="outline" @click="handleViewPlanDetails">
                                {{ trans('ui.view_plan_details') }}
                            </Button>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Subscription Details -->
            <Card v-if="currentSubscription" class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <CreditCard class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.subscription_details') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.subscription_details_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.subscription_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                {{ currentSubscription.stripe_id }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.status') }}
                            </dt>
                            <dd class="mt-1">
                                <Badge :variant="getStatusBadgeVariant(currentSubscription.stripe_status)">
                                    {{ capitalize(currentSubscription.stripe_status) }}
                                </Badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{
                                trans('ui.number_of_probes') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ currentSubscription.quantity }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.type') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <Badge :variant="getStatusBadgeVariant(currentSubscription.type as string)">
                                    {{ capitalize(currentSubscription.type as string) }}
                                </Badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                {{trans('ui.cadastral_units_number')}}
                            </dt>
                            <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-gray-100 break-all">
                                {{ props.currentPlan?.cadastral_units_number ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                {{trans('ui.field_groups_max_area')}}
                            </dt>
                            <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-gray-100 break-all">
                                {{ props.currentPlan?.field_groups_max_area ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                {{trans('ui.field_groups_number')}}
                            </dt>
                            <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-gray-100 break-all">
                                {{ props.currentPlan?.field_groups_number ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <Calendar class="h-4 w-4" />
                                {{ trans('ui.started_at') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(currentSubscription.created_at), 'PPP', { locale: it }) }}
                            </dd>
                        </div>
                        <!-- <div v-if="currentSubscription.trial_ends_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.trial_ends_at') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(currentSubscription.trial_ends_at), 'PPP', { locale: it }) }}
                            </dd>
                        </div> -->
                        <div v-if="currentSubscription.ends_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.ends_at') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ format(parseISO(currentSubscription.ends_at), 'PPP', { locale: it }) }}
                            </dd>
                        </div>
                        <!-- <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('ui.on_trial') }}
                            </dt>
                            <dd class="mt-1">
                                <Badge :variant="currentSubscription.on_trial ? 'default' : 'secondary'">
                                    {{ currentSubscription.on_trial ? trans('ui.yes') : trans('ui.no') }}
                                </Badge>
                            </dd>
                        </div> -->
                        <div v-if="currentSubscription.type != 'manual'">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ trans('ui.on_grace_period') }}</dt>
                            <dd class="mt-1">
                                <Badge :variant="currentSubscription.on_grace_period ? 'destructive' : 'secondary'">
                                    {{ currentSubscription.on_grace_period ? trans('ui.yes') : trans('ui.no') }}
                                </Badge>
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
            <Card v-if="currentSubscription?.type === 'manual'" class="max-w-4xl mt-6">
                <CardHeader>
                    <CardTitle>{{ trans('ui.subscription_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.subscription_details_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="grid gap-4">
                        <div class="space-y-2">
                                    <Label for="quantity">{{ trans('ui.number_of_probes') }}</Label>
                                    <Input
                            id="quantity"
                            type="number"
                            v-model.number="quantity"
                            min="1"
                            step="1"
                            @input="validateQuantity"
                            :class="{ 'border-red-500': quantityError }"
                        />
                        <p v-if="quantityError" class="text-red-500 text-sm">{{ quantityError }}</p>
                                    </div>
                        <div class="sm:max-w-[425px]">
                            <Button @click="submit" :disabled="form.processing">
                                {{ trans('ui.save') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
