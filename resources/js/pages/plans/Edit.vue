<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Card from '@/components/ui/card/Card.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import { trans } from 'laravel-vue-i18n'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ref } from 'vue'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'

enum PlanInterval {
  Day = 'day',
  Week = 'week',
  Month = 'month',
  Year = 'year',
  Custom = 'custom'
  }

interface Plan {
  id: number
  name: string
  slug: string
  interval: PlanInterval
  currency: string
  unit_amount: number // cents
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
  { title: 'Plans', href: route('plans.index') },
  { title: props.plan.name, href: route('plans.show', props.plan.id) },
  { title: 'Edit', href: route('plans.edit', props.plan.id) },
]

const formatCurrency = (amount: number) => {
    return (amount / 100);
};

// Initialize form with existing plan values
const form = useForm({
  name: props.plan.name,
  slug: props.plan.slug,
  interval: props.plan.interval,
  currency: props.plan.currency,
  unit_amount: formatCurrency(props.plan.unit_amount),
  stripe_product_id: props.plan.stripe_product_id ?? '',
  stripe_price_id: props.plan.stripe_price_id,
  active: props.plan.active,
  features: Array.isArray(props.plan?.features) 
    ? props.plan.features 
    : [],
  cadastral_units_number: props.plan.cadastral_units_number ?? '',
  field_groups_number: props.plan.field_groups_number ?? '',
  field_groups_max_area:props.plan.field_groups_max_area ?? '',
  hide_plan: props.plan.hide_plan ?? false
})

// Local state for adding a new line
const newFeature = ref('')

function addFeature() {
  const val = (newFeature.value || '').trim()
  if (!val) return
  form.features = [...(form.features || []), val]
  newFeature.value = ''
}

function removeFeature(index: number) {
  form.features.splice(index, 1)
}

function moveFeature(from: number, to: number) {
  if (to < 0 || to >= form.features.length) return
  const copy = [...form.features]
  const [item] = copy.splice(from, 1)
  copy.splice(to, 0, item)
  form.features = copy
}

// Submit update
function submit() {
  form.put(route('plans.update', props.plan.id), {
    preserveScroll: true,
  })
}

// Cancel
const cancel = () => {
  router.visit(route('plans.index'))
}

</script>

<template>

  <Head title="Edit Plan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.edit_plan') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">{{ trans('ui.name') }}</Label>
              <Input id="name" v-model="form.name" type="text" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <!-- Slug -->
            <div class="space-y-2">
              <Label for="slug">{{ trans('ui.slug') }}</Label>
              <Input id="slug" v-model="form.slug" type="text" :class="{ 'border-destructive': form.errors.slug }" />
              <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
            </div>

            <!-- Interval -->
            <div class="space-y-2">
              <Label for="interval">{{ trans('ui.interval') }} <span class="text-red-500">*</span></Label>
              <Select v-model="form.interval" :required="true">
                <SelectTrigger id="interval" class="w-full">
                  <SelectValue :placeholder="trans('ui.interval')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="value in Object.values(PlanInterval)" :key="value" :value="value">
                    {{ trans(`ui.${value}`) }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.interval" class="text-sm text-destructive">{{ form.errors.interval }}</p>
            </div>

             <!-- Currency -->
            <div class="space-y-2">
              <Label for="currency">{{ trans('ui.currency') }} <span class="text-red-500">*</span></Label>

              <Select v-model="form.currency" :required="true">
                <SelectTrigger id="currency" class="w-full" :class="{ 'border-destructive': form.errors.currency }" :aria-invalid="!!form.errors.currency">
                  <SelectValue :placeholder="trans('ui.currency')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="EUR" selected>EUR</SelectItem>
                </SelectContent>
              </Select>

              <p v-if="form.errors.currency" class="text-sm text-destructive">{{ form.errors.currency }}</p>
            </div>

            <!-- Price (cents) -->
            <div class="space-y-2">
              <Label for="unit_amount">{{ trans('ui.price_cents') }}</Label>
              <Input id="unit_amount" v-model.number="form.unit_amount" type="number" min="0" step="0.01"
                :class="{ 'border-destructive': form.errors.unit_amount }" />
              <p v-if="form.errors.unit_amount" class="text-sm text-destructive">{{ form.errors.unit_amount }}</p>
            </div>

            <!-- Features (add lines, save as array) -->
            <div class="space-y-2">
              <Label for="featureInput">{{ trans('ui.features') }}</Label>

              <div class="flex gap-2">
                <Input id="featureInput" v-model="newFeature" type="text"
                  :placeholder="trans('ui.features_placeholder')" @keydown.enter.prevent="addFeature"
                  class="flex-1" />
                <Button type="button" @click="addFeature">{{ trans('ui.add_feature') }}</Button>
              </div>
              <p class="text-xs text-muted-foreground">
                {{ trans('ui.features_hint') }}
              </p>

              <ul class="space-y-2">
                <li v-for="(f, idx) in form.features" :key="`${idx}-${f}`" class="flex items-center gap-2">
                  <Input v-model="form.features[idx]" type="text" class="flex-1"
                    :aria-label="`${trans('ui.feature')} ${idx + 1}`" />
                  <Button type="button" variant="destructive" @click="removeFeature(idx)">
                    {{ trans('ui.remove') }}
                  </Button>
                  <Button type="button" variant="secondary" @click="moveFeature(idx, idx - 1)" :disabled="idx === 0"
                    aria-label="Move up">↑</Button>
                  <Button type="button" variant="secondary" @click="moveFeature(idx, idx + 1)"
                    :disabled="idx === form.features.length - 1" aria-label="Move down">↓</Button>
                </li>
              </ul>

              <p v-if="form.errors.features" class="text-sm text-destructive">
                {{ form.errors.features }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="cadastral_units_number">{{ trans('ui.cadastral_units_number') }} </Label>
              <Input id="cadastral_units_number" v-model.number="form.cadastral_units_number" type="number" min="0" step="1"
                :class="{ 'border-destructive': form.errors.cadastral_units_number }" required />
              <p v-if="form.errors.cadastral_units_number" class="text-sm text-destructive">{{ form.errors.cadastral_units_number }}</p>
            </div>

            <div class="space-y-2">
              <Label for="field_groups_number">{{ trans('ui.field_groups_number') }} </Label>
              <Input id="field_groups_number" v-model.number="form.field_groups_number" type="number" min="0" step="1"
                :class="{ 'border-destructive': form.errors.field_groups_number }" required />
              <p v-if="form.errors.field_groups_number" class="text-sm text-destructive">{{ form.errors.field_groups_number }}</p>
            </div>

            <div class="space-y-2">
              <Label for="field_groups_max_area">{{ trans('ui.field_groups_max_area') }} </Label>
              <Input id="field_groups_max_area" v-model.number="form.field_groups_max_area" type="number" min="0" step="0.01"
                :class="{ 'border-destructive': form.errors.cadastral_units_number }" required />
              <p v-if="form.errors.field_groups_max_area" class="text-sm text-destructive">{{ form.errors.field_groups_max_area }}</p>
            </div>

            <!-- Stripe Product ID (optional) 
            <div class="space-y-2">
              <Label for="stripe_product_id">Stripe Product ID</Label>
              <Input id="stripe_product_id" v-model="form.stripe_product_id" type="text"
                :class="{ 'border-destructive': form.errors.stripe_product_id }" />
              <p v-if="form.errors.stripe_product_id" class="text-sm text-destructive">{{ form.errors.stripe_product_id
                }}</p>
            </div>

             Stripe Price ID 
            <div class="space-y-2">
              <Label for="stripe_price_id">Stripe Price ID</Label>
              <Input id="stripe_price_id" v-model="form.stripe_price_id" type="text"
                :class="{ 'border-destructive': form.errors.stripe_price_id }" />
              <p v-if="form.errors.stripe_price_id" class="text-sm text-destructive">{{ form.errors.stripe_price_id }}
              </p>
            </div> -->

            <!-- Active -->
            <div class="space-y-2">
              <Label for="active">{{ trans('ui.status') }}</Label>
              <Select :model-value="form.active ? '1' : '0'" @update:model-value="val => form.active = val === '1'">
                <SelectTrigger id="active" class="w-full">
                  <SelectValue :placeholder="trans('ui.status')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="1">{{ trans('ui.active') }}</SelectItem>
                  <SelectItem value="0">{{ trans('ui.inactive') }}</SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.active" class="text-sm text-destructive">{{ form.errors.active }}</p>
            </div>

            <!-- Hide -->
            <div class="space-y-2 ">
              <Label for="hide_plan">{{ trans('ui.hide_plan') }}</Label>
              <Checkbox id="hide_plan" v-model="form.hide_plan" type="text" :placeholder="trans('ui.hide_plan')"
                :class="{ 'border-destructive': form.errors.hide_plan }" />
              <p v-if="form.errors.hide_plan" class="text-sm text-destructive">{{ form.errors.hide_plan }}</p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-4">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.updating') : trans('ui.update_plan') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
