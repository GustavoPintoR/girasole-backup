<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Grid, Edit, ArrowLeft } from 'lucide-vue-next'
import { trans } from 'laravel-vue-i18n'
import { usePermissions } from '@/composables/usePermissions'

interface Province {
  id: number
  name: string
  code: string
  region: { id: number; name: string; code: string }
}
interface Region {
  id: number
  name: string
  code: string
}
interface City {
  id: number
  name: string
  cadastral_code: string
  region: Region
  province: Province
}

const props = defineProps<{ city: City }>()

const breadcrumbs = [
  { title: trans('ui.cities'), href: route('cities.index') },
  { title: props.city.name, href: route('cities.show', props.city.id) },
]

const handleBack = () => {
  router.visit(route('cities.index'))
}
const handleEdit = () => {
  router.visit(route('cities.edit', props.city.id))
}

const { can } = usePermissions()
</script>

<template>
  <Head :title="props.city.name" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ props.city.name }}
          </h1>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_city" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <!-- City Info -->
      <Card class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Grid class="h-5 w-5 text-gray-500" />
            {{ trans('ui.city_details') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.name') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.city.name }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.cadastral_code') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ props.city.cadastral_code ?? '-' }}
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.province') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                <Link
                  v-if="props.city.province"
                  :href="route('provinces.show', props.city.province.id)"
                  class="text-primary hover:underline"
                >
                  {{ props.city.province.name }}
                </Link>
                <span v-else>-</span>
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ trans('ui.region') }}
              </dt>
              <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                <Link
                  v-if="props.city.region"
                  :href="route('regions.show', props.city.region.id)"
                  class="text-primary hover:underline"
                >
                  {{ props.city.region.name }}
                </Link>
                <span v-else>-</span>
              </dd>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>