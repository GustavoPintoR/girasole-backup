<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
    Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle
} from '@/components/ui/card'
import { toast } from 'vue-sonner'
import { trans } from 'laravel-vue-i18n'
import AuthBase from '@/layouts/AuthLayout.vue'
import type { Plan } from '@/types'
import { router } from '@inertiajs/vue3'
import 'vue-sonner/style.css'
import { Toaster } from '@/components/ui/sonner'
import {
    Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle
} from '@/components/ui/dialog'
import {
    Drawer, DrawerClose, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle
} from '@/components/ui/drawer'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useMediaQuery } from '@vueuse/core'

interface FlashMessages {
    success: string | null
    error: string | null
    warning: string | null
    info: string | null
}

// Watch for flash messages
router.on('success', (event) => {
    const flash = event.detail.page.props.flash as FlashMessages
    if (flash?.success) {
        toast.success(flash.success)
        flash.success = null
    }
    if (flash?.error) {
        toast.error(flash.error)
        flash.error = null
    }
    if (flash?.warning) {
        toast.warning(flash.warning)
        flash.warning = null
    }
    if (flash?.info) {
        toast.info(flash.info)
        flash.info = null
    }
})

const props = defineProps<{
    plans: Plan[]
    hasActiveSub: boolean
    activePlanId: string | null
}>()

const form = useForm({
    price_id: '',
    quantity: 1 // Default quantity
})

const isDesktop = useMediaQuery('(min-width: 768px)')
const isOpen = ref(false)
const selectedPlanId = ref<number | null>(null)
const quantity = ref<number>(1) // Reactive variable for quantity input

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

const openQuantityModal = (planId: number) => {
    if (props.hasActiveSub) {
        toast.info(trans('ui.already_subscribed'))
        return
    }
    selectedPlanId.value = planId
    quantity.value = 1 // Reset quantity when opening modal
    isOpen.value = true
}

const subscribe = () => {
    if (!selectedPlanId.value) return
    if (!validateQuantity()) {
        toast.error(quantityError.value || 'Invalid quantity')
        return
    }

    form.quantity = quantity.value
    form.post(route('billing.subscribe', selectedPlanId.value), {
        onError: (errors) => {
            toast.error(errors.message || 'Something went wrong.')
        },
        onSuccess: () => {
            toast.success('Redirecting to Stripe checkout...')
            isOpen.value = false
        }
    })
}

const grouped = computed(() => {
    return props.plans
})

const formatPrice = (amountCents: number, currency: string, locale?: string) =>
    new Intl.NumberFormat(locale || undefined, {
        style: 'currency',
        currency,
        currencyDisplay: 'narrowSymbol',
        maximumFractionDigits: 2,
        minimumFractionDigits: 2,
    }).format((amountCents || 0) / 100)

const formatCadastralLimit = (n?: null) => {
  // null or 0 => Unlimited
  if (!n) return trans('ui.unlimited') // add this key to i18n
  return trans('ui.up_to_n_cadastral_units', { n }) // add this key to i18n
}

const formatArea = (n: number, locale?: string) => {
  // keep decimals if you need (change to 0 if you want integers)
  const formatted = new Intl.NumberFormat(locale || undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(n || 0)

  return `${formatted} ha`
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

    <AuthBase :title="trans('ui.account_subscription')" :description="trans('ui.account_subscription_description')"
        :showLogoutLink="true">

        <Head :title="trans('ui.pricing')" />

        <div class="mx-auto py-8">
            <h1 class="text-2xl font-semibold mb-6">{{ trans('ui.choose_plan') }}</h1>

            <div class="grid gap-2 md:gap-4 lg:gap-6 place-items-center" :class="{
                'md:grid-cols-2': grouped.length === 2,
                'lg:grid-cols-3': grouped.length >= 3
            }">
                <Card v-for="plan in grouped" :key="plan.stripe_price_id" class="flex md:min-w-[18rem] xl:min-w-[23rem]">
                <CardHeader>
                    <CardTitle class="flex items-baseline justify-between">
                    <span>{{ plan.name }}</span>
                    </CardTitle>

                    <CardTitle class="flex items-baseline justify-between">
                    <span class="text-2xl font-semibold">
                        {{ formatPrice(plan.unit_amount, plan.currency) }}
                    </span>
                    </CardTitle>

                    <CardDescription class="space-y-1">
                    <div class="text-sm text-muted-foreground">
                        {{ trans('ui.per') }} {{ trans('ui.' + plan.interval) }}
                    </div>
                    <div class="text-sm">
                        <strong>{{ trans('ui.cadastral_max_area') }}:</strong>
                        {{ formatCadastralMaxArea(plan.field_groups_max_area ?? null) }}
                    </div>
                    <div class="text-sm">
                        <strong>{{ trans('ui.cadastral_groups_limit') }}:</strong>
                        {{ formatCadastralGroupsLimit(plan.field_groups_number ?? null) }}
                    </div>
                    <div class="text-sm">
                        <strong>{{ trans('ui.cadastral_units_limit') }}:</strong>
                        {{ formatCadastralLimit(plan.cadastral_units_number) }}
                    </div>
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-2" v-if="plan.features && plan.features.length > 0">
                    <ul class="list-disc pl-5">
                        <li v-for="f in plan.features" :key="f">{{ f }}</li>
                    </ul>
                </CardContent>

                <CardFooter class="mt-auto">
                    <Button
                    class="w-full"
                    :disabled="form.processing || (props.hasActiveSub && plan.stripe_price_id == props.activePlanId)"
                    @click="openQuantityModal(plan.id)"
                    >
                    {{ props.hasActiveSub && plan.stripe_price_id == props.activePlanId ? trans('ui.current_plan') : trans('ui.subscribe') }}
                    </Button>
                </CardFooter>
                </Card>
            </div>
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
                    <Button @click="subscribe" :disabled="form.processing || !!quantityError">
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
                        <Button @click="subscribe" :disabled="form.processing || !!quantityError">
                            {{ trans('ui.confirm_subscription') }}
                        </Button>
                        <DrawerClose as-child>
                            <Button variant="outline">{{ trans('ui.cancel') }}</Button>
                        </DrawerClose>
                    </DrawerFooter>
                </form>
            </DrawerContent>
        </Drawer>
    </AuthBase>
</template>
