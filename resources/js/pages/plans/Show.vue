<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Grid, Edit, ArrowLeft } from 'lucide-vue-next'
import { trans } from 'laravel-vue-i18n'
import { usePermissions } from '@/composables/usePermissions'

interface Plan {
  id: number
  name: string
  slug: string
  interval: 'monthly' | 'yearly'
  currency: string
  unit_amount: number
  stripe_product_id?: string | null
  stripe_price_id: string
  active: boolean
  features?: string[] | null
  cadastral_units_number: number | null,
  field_groups_number: number | null,
  field_groups_max_area: number | null,
  hide_plan: boolean
}

const props = defineProps<{ plan: Plan }>()

const breadcrumbs = [
  { title: trans('ui.plans'), href: route('plans.index') },
  { title: props.plan.name, href: route('plans.show', props.plan.id) },
]

const handleBack = () => {
  router.visit(route('plans.index'))
}
const handleEdit = () => {
  router.visit(route('plans.edit', props.plan.id))
}

const { can } = usePermissions()
</script>

<template>
  <Head :title="props.plan.name" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ props.plan.name }}
          </h1>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_plan" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <!-- Plan Info -->
      <Card class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Grid class="h-5 w-5 text-gray-500" />
            {{ trans('ui.plan_details') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.name') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.plan.name }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.slug') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.plan.slug }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.interval') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.plan.interval }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.price') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{
                  new Intl.NumberFormat(undefined, { style: 'currency', currency: props.plan.currency })
                    .format(props.plan.unit_amount / 100)
                }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.currency') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.plan.currency }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{trans('ui.cadastral_units_number')}}
              </dt>
              <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-gray-100 break-all">
                {{ props.plan.cadastral_units_number  ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{trans('ui.field_groups_max_area')}}
              </dt>
              <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-gray-100 break-all">
                {{ props.plan.field_groups_max_area  ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{trans('ui.field_groups_number')}}
              </dt>
              <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-gray-100 break-all">
                {{ props.plan.field_groups_number  ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                Stripe Product ID
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.plan.stripe_product_id ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                Stripe Price ID
              </dt>
              <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-gray-100 break-all">
                {{ props.plan.stripe_price_id  ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.status') }}
              </dt>
              <dd class="text-lg font-semibold" :class="props.plan.active ? 'text-green-600' : 'text-zinc-400'">
                {{ props.plan.active ? trans('ui.active') : trans('ui.inactive') }}
              </dd>
            </div>

             <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.hide_plan') }}
              </dt>
              <dd class="text-lg font-semibold" :class="props.plan.hide_plan ? 'text-zinc-400' : 'text-green-600'">
                {{ props.plan.hide_plan == true ? trans('ui.hidden') : trans('ui.visible') }}
              </dd>
            </div>

            <div class="md:col-span-2" v-if="props.plan.features?.length">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.features') }}
              </dt>
              <dd class="text-gray-900 dark:text-gray-100">
                <ul class="list-disc pl-5">
                  <li v-for="f in props.plan.features" :key="f">{{ f }}</li>
                </ul>
              </dd>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
