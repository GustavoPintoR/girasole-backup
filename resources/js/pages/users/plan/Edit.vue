<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, CreditCard, AlertCircle } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { trans } from 'laravel-vue-i18n';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';
import {
    Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import {
    Drawer, DrawerClose, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle
} from '@/components/ui/drawer';
import { useMediaQuery } from '@vueuse/core';
import { ref, computed, watch } from 'vue';
import Input from '@/components/ui/input/Input.vue';
import { Plan } from '@/types';
import { format, parseISO } from 'date-fns';


const isDesktop = useMediaQuery('(min-width: 768px)')
const isOpen = ref(false)
const quantity = ref<number>(1) // Reactive variable for quantity input

interface Subscription {
    stripe_price: string;
    stripe_status: string;
    ends_at: string | null;
    on_grace_period: boolean;
    type: string;
}

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
}

const props = defineProps<{
    user: User;
    plans: Plan[];
    currentSubscription: Subscription | null;
    currentPlan: Plan | null;
    hasActiveSub: boolean;
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
        title: trans('ui.manual_plan'),
        href: route('users.manage-plan', props.user.id),
    },
];

const selectedPlan = computed(() =>
  props.plans.find((p) => p.id === form.plan_id) ?? null
);

const initialEndsAtValue = props.currentSubscription?.ends_at
  ? format(parseISO(props.currentSubscription.ends_at), "yyyy-MM-dd'T'HH:mm")
  : '';

const form = useForm({
    plan_id: props.currentPlan?.id ?? null,
    quantity: 1, // Default quantity
    ends_at: initialEndsAtValue || null,
});

const endsAtError = ref<string | null>(null);

const validateEndsAt = () => {
  endsAtError.value = null;
  if (selectedPlan.value?.interval !== 'custom') return true;

  if (form.ends_at === null) {
    return true;
  }

  const chosen = new Date(form.ends_at as string);
  const now = new Date();

  if (isNaN(chosen.getTime()) || chosen <= now) {
    endsAtError.value = trans('ui.end_date_error_future') || 'End date must be in the future.';
    return false;
  }

  return true;
};

const originalEndsAt = props.currentSubscription?.ends_at ?? null;

watch(
  () => form.plan_id,
  () => {
    if (selectedPlan.value?.interval == 'custom') {
      endsAtError.value = null;
       if (form.ends_at === originalEndsAt) {
        form.ends_at = null;
      }
    }
  }
);

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

const openQuantityModal = () => {
    quantity.value = 1 // Reset quantity when opening modal
    isOpen.value = true
}

const handleBack = () => {
    router.visit(route('users.show', props.user.id));
};

const handleCancel = () => {
    router.visit(route('users.show', props.user.id));
};

const submit = () => {
  if (!validateQuantity() || !validateEndsAt()) {
    toast.error('Validation failed')
    return
  }

  form.quantity = quantity.value

  form.post(route('users.update-plan', props.user.id), {
    preserveScroll: true,
    onError: (errors) => {
      toast.error(errors.message || 'Something went wrong.')
    },
    onSuccess: () => {
      isOpen.value = false
    }
  })
}

const submitEndsAt = () => {
  if (!validateQuantity() || !validateEndsAt()) {
    toast.error('Validation failed');
    return;
  }
  
  form.post(route('users.set-plan-details', props.user.id), {
    preserveScroll: true,
    onError: (errors) => toast.error(errors.message || 'Error saving'),
  });
};

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: currency.toUpperCase(),
    }).format(amount / 100);
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

const formatArea = (n: number, locale?: string) => {
  // keep decimals if you need (change to 0 if you want integers)
  const formatted = new Intl.NumberFormat(locale || undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(n || 0)

  return `${formatted} ha`
}

const formatCadastralLimit = (n?: null) => {
  // null or 0 => Unlimited
  if (!n) return trans('ui.unlimited') // add this key to i18n
  return trans('ui.up_to_n_cadastral_units', { n }) // add this key to i18n
}

const formatCadastralMaxArea = (n?: number | null) => {
  // 0 or null => Unlimited (ignore check)
  if (!n) return trans('ui.unlimited')
  return trans('ui.up_to_n_cadastral_area', { n: formatArea(n) })
}

const formatCadastralGroupsLimit = (n?: null) => {
  if (!n) return trans('ui.unlimited')
  return trans('ui.up_to_n_cadastral_groups', { n })
}

</script>

<template>
    <Toaster position="top-right" :expand="true" :rich-colors="true" />

    <Head :title="trans('ui.manual_plan')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ trans('ui.manage_subscription') }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ trans('ui.back') }}
                    </Button>
                    <Link :href="route('users.view-plan', user.id)" v-if="props.hasActiveSub">
                    <Button type="button" variant="outline" @click="handleCancel" :disabled="form.processing">
                        {{ trans('ui.view_subscription') }}
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- User Info Alert -->
            <Alert class="max-w-4xl mb-6">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>{{ trans('ui.managing_subscription_for') }}</AlertTitle>
                <AlertDescription>
                    {{ user.first_name }} {{ user.last_name }} ({{ user.email }})
                </AlertDescription>
            </Alert>

            <!-- Current Subscription Info -->
            <Card v-if="currentSubscription" class="max-w-4xl mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <CreditCard class="h-5 w-5 text-gray-500" />
                        {{ trans('ui.current_subscription') }}
                    </CardTitle>
                    <CardDescription>
                        {{ trans('ui.current_subscription_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="currentPlan" class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h3 class="font-semibold text-lg">{{ currentPlan.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatCurrency(currentPlan.unit_amount, currentPlan.currency) }} /
                                    {{ currentPlan.interval }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <Badge :variant="getStatusBadgeVariant(currentSubscription.stripe_status)">
                                    {{ currentSubscription.stripe_status }}
                                </Badge>
                                <Badge v-if="currentSubscription.on_grace_period" variant="destructive">
                                    {{ trans('ui.current_subscription') }}<p v-if="currentSubscription.ends_at">({{ format(parseISO(currentSubscription.ends_at), 'PPP', { locale: it }) }})</p> 
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <div v-if="currentSubscription?.type === 'manual' && currentPlan?.interval == 'custom'" class="max-w-4xl mt-6 border-0">
                <CardHeader>
                    <CardTitle>{{ trans('ui.subscription_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.subscription_details_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="grid gap-4">
                        <div class="space-y-2">
                            <Label for="ends_at">{{ trans('ui.ends_at') }}</Label>
                            <Input id="ends_at" type="datetime-local"  v-model="form.ends_at" @change="validateEndsAt"
                                :class="{ 'border-red-500': endsAtError }"
                                :placeholder="trans('ui.end_date_placeholder') || currentSubscription.ends_at" />
                            <p v-if="endsAtError" class="text-red-500 text-sm">{{ endsAtError }}</p>
                        </div>
                        <div class="flex justify-end">
                            <Button @click="submitEndsAt" :disabled="form.processing">
                                {{ trans('ui.save') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </div>
            </Card>
            
            <!-- Plan Selection Form -->
            <!-- <form @submit.prevent="submit" v-if="currentSubscription?.stripe_status != 'active'"> -->
                <Card class="max-w-4xl mb-6" v-if="currentSubscription?.type == 'manual' || !currentSubscription">
                    <CardHeader>
                        <CardTitle>{{ currentSubscription ? trans('ui.change_plan') : trans('ui.assign_plan') }}
                        </CardTitle>
                        <CardDescription>
                            {{ currentSubscription ? trans('ui.change_plan_desc') : trans('ui.assign_plan_desc') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-4">
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
                                                        {{ trans('ui.'+plan.interval) }}
                                                    </span>
                                                    
                                                    <!-- <span class="text-sm text-gray-500">
                                                        <strong>{{ trans('ui.cadastral_groups_limit') }}:</strong>
                                                        {{ formatCadastralGroupsLimit(plan.field_groups_number ?? null) }}
                                                    </span>
                                                    <span class="text-sm text-gray-500">
                                                        <strong>{{ trans('ui.cadastral_units_limit') }}:</strong>
                                                        {{ formatCadastralLimit(plan.cadastral_units_number) }}
                                                    </span> -->
                                                </div>
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.plan_id" />
                            </div>

                            <!-- Selected Plan Preview -->
                            <div v-if="selectedPlan" class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                                <h4 class="font-semibold mb-2">{{ trans('ui.selected_plan_preview') }}</h4>
                                <div v-for="plan in plans.filter(p => p.id === form.plan_id)" :key="plan.id">
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
                                        <div class="flex justify-between">
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.cadastral_max_area') }}</span>
                                            <span class="text-sm font-medium">{{ formatCadastralMaxArea(plan.field_groups_max_area ?? null) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.cadastral_groups_limit') }}</span>
                                            <span class="text-sm font-medium">{{ formatCadastralGroupsLimit(plan.field_groups_number ?? null) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400">{{ trans('ui.cadastral_units_limit') }}</span>
                                            <span class="text-sm font-medium">{{ formatCadastralLimit(plan.cadastral_units_number) }}</span>
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

                             <Card
                                v-if="selectedPlan && selectedPlan.interval === 'custom'"
                                class="max-w-4xl my-6"
                                >
                                <CardHeader>
                                    <CardTitle>{{ trans('ui.custom_end_date') || 'Custom End Date' }}</CardTitle>
                                    <CardDescription>
                                    {{ trans('ui.custom_end_date_desc') || 'Set a specific end date for this subscription.' }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="space-y-2">
                                    <Label for="ends_at">{{ trans('ui.end_date') || 'End date & time' }}</Label>
                                    <Input
                                        id="ends_at"
                                        type="datetime-local"
                                         v-model="form.ends_at"
                                        @change="validateEndsAt"
                                        :class="{ 'border-red-500': endsAtError }"
                                        :placeholder="trans('ui.end_date_placeholder') || 'YYYY-MM-DDTHH:MM'"
                                    />
                                    <p v-if="endsAtError" class="text-red-500 text-sm">{{ endsAtError }}</p>
                                    </div>
                                </CardContent>
                                </Card>

                            <!-- Warning Alert -->
                            <Alert v-if="currentSubscription && form.plan_id !== currentPlan?.id">
                                <AlertCircle class="h-4 w-4" />
                                <AlertTitle>{{ trans('ui.plan_change_warning') }}</AlertTitle>
                                <AlertDescription>
                                    {{ trans('ui.plan_change_warning_desc') }}
                                </AlertDescription>
                            </Alert>

                            <!-- <Alert v-if="currentSubscription && form.plan_id !== currentPlan?.id">
                                <Checkbox class="h-4 w-4" id="one_time_charge" v-model="form.one_time_charge" for="one_time_charge" />
                                <AlertTitle class="ml-6">{{ trans('ui.one_time_charge') }}</AlertTitle>
                                <AlertDescription class="ml-6">
                                    {{ trans('ui.one_time_charge_desc') }}
                                </AlertDescription>
                            </Alert> -->
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 max-w-4xl">
                            <Button type="button" variant="outline" @click="handleCancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button :disabled="form.processing || !selectedPlan"
                                    @click="openQuantityModal(form.plan_id)"
                            >
                                {{ form.processing ? trans('ui.updating') : (currentSubscription ? trans('ui.update_plan') : trans('ui.assign_plan')) }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            <!-- </form> -->
        </div>
        <!-- Dialog for Desktop -->
        <Dialog v-if="isDesktop" v-model:open="isOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ trans('ui.select_probes') }}</DialogTitle>
                    <DialogDescription>
                        {{ trans('ui.enter_probes_description') }}
                    </DialogDescription>
                </DialogHeader>
                <form class="grid gap-4">
                    <div class="grid gap-2">
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
                    <Button @click="submit" :disabled="form.processing || !!quantityError">
                        {{ trans('ui.confirm_subscription') }}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Drawer for Mobile -->
        <Drawer v-else v-model:open="isOpen">
            <DrawerContent>
                <DrawerHeader class="text-left">
                    <DrawerTitle>{{ trans('ui.select_probes') }}</DrawerTitle>
                    <DrawerDescription>
                        {{ trans('ui.enter_probes_description') }}
                    </DrawerDescription>
                </DrawerHeader>
                <form class="grid gap-4 px-4">
                    <div class="grid gap-2">
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
                    <DrawerFooter class="pt-2">
                        <Button @click="submit" :disabled="form.processing || !!quantityError">
                            {{ trans('ui.confirm_subscription') }}
                        </Button>
                        <DrawerClose as-child>
                            <Button variant="outline">{{ trans('ui.cancel') }}</Button>
                        </DrawerClose>
                    </DrawerFooter>
                </form>
            </DrawerContent>
        </Drawer>
    </AppLayout>
</template>
