<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert'
import { trans } from 'laravel-vue-i18n';

import axios from 'axios'
import { usePermissions } from '@/composables/usePermissions';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.withCredentials = true

type ImportType = 'regions' | 'provinces' | 'cities' | 'postal_codes'

interface ImportResult {
  success: boolean
  message: string
  batchId?: string
  count?: number
  startedAt?: string
}

const activeTab = ref<ImportType>('regions')

const files = ref<Record<ImportType, File | null>>({
  regions: null,
  provinces: null,
  cities: null,
  postal_codes: null,
})

const submitting = ref<Record<ImportType, boolean>>({
  regions: false,
  provinces: false,
  cities: false,
  postal_codes: false,
})

const lastResult = ref<Record<ImportType, ImportResult | null>>({
  regions: null,
  provinces: null,
  cities: null,
  postal_codes: null,
})

function onFileChange(kind: ImportType, e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  files.value[kind] = file
}

async function upload(kind: ImportType) {
  const file = files.value[kind]
  if (!file) {
    setResult(kind, false, trans('ui.select_a_file_warning'))
    return
  }

  submitting.value[kind] = true

  try {
    const formData = new FormData()
    formData.append('file', file)
    const { data } = await axios.post(`/imports/${kind}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    setResult(kind, true, data?.message || trans('ui.sucess_message'), data?.batchId)
  } catch (e: any) {
    const msg = e?.response?.data?.message || e?.message || trans('ui.failed_to_dispatch')
    setResult(kind, false, msg)
  } finally {
    submitting.value[kind] = false
  }
}

function setResult(kind: ImportType, success: boolean, message: string, batchId?: string) {
  lastResult.value[kind] = {
    success,
    message,
    batchId,
    startedAt: success ? new Date().toISOString() : undefined,
  }
}
const breadcrumbs = [
  { title: trans('ui.imports'), href: route('imports.index') }
];

function resetForm() {
  const kind = activeTab.value
  files.value[kind] = null
  lastResult.value[kind] = null
  submitting.value[kind] = false
}

const { can } = usePermissions();
</script>

<template>
    <Head title="Edit Region" />

  <AppLayout :breadcrumbs="breadcrumbs">
  <div class="mx-auto max-w-4xl p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-semibold tracking-tight">{{ trans('ui.import_header') }}</h1>
      <p class="text-muted-foreground text-sm">
        {{ trans('ui.import_header_text') }}
      </p>
    </div>

    <Tabs v-model="activeTab" class="w-full">
      <TabsList class="grid w-full grid-cols-4">
        <TabsTrigger v-if="can.region_import" value="regions" @click="resetForm">{{ trans('ui.regions') }}</TabsTrigger>
        <TabsTrigger v-if="can.province_import" value="provinces" @click="resetForm">{{ trans('ui.provinces') }}</TabsTrigger>
        <TabsTrigger v-if="can.city_import" value="cities" @click="resetForm">{{ trans('ui.cities') }}</TabsTrigger>
        <TabsTrigger v-if="can.postal_code_import" value="postal_codes" @click="resetForm">{{ trans('ui.postal_codes') }}</TabsTrigger>
      </TabsList>

      <TabsContent value="regions">
        <Card>
          <CardHeader>
            <CardTitle>{{ trans('ui.import_regions') }}</CardTitle>
            <CardDescription>{{trans('ui.csv_columns')}}: Codice Regione; Denominazione Regione</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid w-full items-center gap-2">
              <Label for="regions-file">CSV file</Label>
              <Input id="regions-file" type="file" accept=".csv,text/csv" @change="(e: Event) => onFileChange('regions', e)" />
              <p v-if="files.regions" class="text-sm text-muted-foreground">
                {{ trans('ui.selected') }}: {{ files.regions?.name }} ({{ Math.round((files.regions?.size || 0) / 1024) }} KB)
              </p>
            </div>

            <Separator />

            <Alert v-if="lastResult.regions" :variant="lastResult.regions.success ? 'default' : 'destructive'">
              <AlertTitle>{{ lastResult.regions.success ? trans('ui.import_started') : trans('ui.error_starting_import') }}</AlertTitle>
              <AlertDescription>
                <div>{{ lastResult.regions.message }}</div>
                <div v-if="lastResult.regions.startedAt" class="mt-2 text-xs text-muted-foreground">
                  {{ trans('ui.started') }}: {{ new Date(lastResult.regions.startedAt).toLocaleString() }}
                </div>
              </AlertDescription>
            </Alert>
          </CardContent>
          <CardFooter class="justify-between">
            <div class="text-xs text-muted-foreground">
              {{ trans('ui.import_footer_text') }}
            </div>
            <Button :disabled="submitting.regions || lastResult.regions?.success" @click="upload('regions')">
              {{ submitting.regions ? trans('ui.dispatching') : trans('ui.import') }}
            </Button>
          </CardFooter>
        </Card>
      </TabsContent>

      <TabsContent value="provinces">
        <Card>
          <CardHeader>
            <CardTitle>{{trans('ui.import_provinces')}}</CardTitle>
            <CardDescription>{{trans('ui.csv_columns')}}: Denominazione; Sigla automobilistica; Codice Regione</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid w-full items-center gap-2">
              <Label for="provinces-file">CSV file</Label>
              <Input id="provinces-file" type="file" accept=".csv,text/csv" @change="(e: Event) => onFileChange('provinces', e)" />
              <p v-if="files.provinces" class="text-sm text-muted-foreground">
                {{ trans('ui.selected') }}: {{ files.provinces?.name }} ({{ Math.round((files.provinces?.size || 0) / 1024) }} KB)
              </p>
            </div>

            <Separator />

            <Alert v-if="lastResult.provinces" :variant="lastResult.provinces.success ? 'default' : 'destructive'">
              <AlertTitle>{{ lastResult.provinces.success ? trans('ui.import_started') : trans('ui.error_starting_import') }}</AlertTitle>
              <AlertDescription>
                <div>{{ lastResult.provinces.message }}</div>
                <div v-if="lastResult.provinces.startedAt" class="mt-2 text-xs text-muted-foreground">
                  {{ trans('ui.started') }}: {{ new Date(lastResult.provinces.startedAt).toLocaleString() }}
                </div>
              </AlertDescription>
            </Alert>
          </CardContent>
          <CardFooter class="justify-between">
            <div class="text-xs text-muted-foreground">
              {{ trans('ui.import_footer_text') }}
            </div>
            <Button :disabled="submitting.provinces || lastResult.provinces?.success" @click="upload('provinces')">
              {{ submitting.provinces ? trans('ui.dispatching') : trans('ui.import') }}
            </Button>
          </CardFooter>
        </Card>
      </TabsContent>

      <TabsContent value="cities">
        <Card>
          <CardHeader>
            <CardTitle>{{trans('ui.import_cities')}}</CardTitle>
            <CardDescription>
              {{trans('ui.csv_columns')}}: Codice Catastale del comune; Denominazione in italiano; Sigla automobilistica; Codice Regione
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid w-full items-center gap-2">
              <Label for="cities-file">CSV file</Label>
              <Input id="cities-file" type="file" accept=".csv,text/csv" @change="(e: Event) => onFileChange('cities', e)" />
              <p v-if="files.cities" class="text-sm text-muted-foreground">
                {{ trans('ui.selected') }} : {{ files.cities?.name }} ({{ Math.round((files.cities?.size || 0) / 1024) }} KB)
              </p>
            </div>

            <Separator />

            <Alert v-if="lastResult.cities" :variant="lastResult.cities.success ? 'default' : 'destructive'">
              <AlertTitle>{{ lastResult.cities.success ? trans('ui.import_started') : trans('ui.error_starting_import') }}</AlertTitle>
              <AlertDescription>
                <div>{{ lastResult.cities.message }}</div>
                <div v-if="lastResult.cities.startedAt" class="mt-2 text-xs text-muted-foreground">
                  {{ trans('ui.started') }} : {{ new Date(lastResult.cities.startedAt).toLocaleString() }}
                </div>
              </AlertDescription>
            </Alert>
          </CardContent>
          <CardFooter class="justify-between">
            <div class="text-xs text-muted-foreground">
              {{ trans('ui.import_footer_text') }}
            </div>
            <Button :disabled="submitting.cities || lastResult.cities?.success" @click="upload('cities')">
              {{ submitting.cities ? trans('ui.dispatching') : trans('ui.import') }}
            </Button>
          </CardFooter>
        </Card>
      </TabsContent>

      <TabsContent value="postal_codes">
        <Card>
          <CardHeader>
            <CardTitle>{{trans('ui.import_postal_codes')}}</CardTitle>
            <CardDescription>
              {{trans('ui.csv_columns')}}: Cap; Codice Belfiore
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid w-full items-center gap-2">
              <Label for="postal-codes-file">CSV file</Label>
              <Input id="postal-codes-file" type="file" accept=".csv,text/csv" @change="(e: Event) => onFileChange('postal_codes', e)" />
              <p v-if="files.postal_codes" class="text-sm text-muted-foreground">
                {{ trans('ui.selected') }} : {{ files.postal_codes?.name }} ({{ Math.round((files.postal_codes?.size || 0) / 1024) }} KB)
              </p>
            </div>

            <Separator />

            <Alert v-if="lastResult.postal_codes" :variant="lastResult.postal_codes.success ? 'default' : 'destructive'">
              <AlertTitle>{{ lastResult.postal_codes.success ? trans('ui.import_started') : trans('ui.error_starting_import') }}</AlertTitle>
              <AlertDescription>
                <div>{{ lastResult.postal_codes.message }}</div>
                <div v-if="lastResult.postal_codes.startedAt" class="mt-2 text-xs text-muted-foreground">
                  {{ trans('ui.started') }} : {{ new Date(lastResult.postal_codes.startedAt).toLocaleString() }}
                </div>
              </AlertDescription>
            </Alert>
          </CardContent>
          <CardFooter class="justify-between">
            <div class="text-xs text-muted-foreground">
              {{ trans('ui.import_footer_text') }}
            </div>
            <Button :disabled="submitting.postal_codes || lastResult.postal_codes?.success" @click="upload('postal_codes')">
              {{ submitting.postal_codes ? trans('ui.dispatching') : trans('ui.import') }}
            </Button>
          </CardFooter>
        </Card>
      </TabsContent>
    </Tabs>
  </div>
  </AppLayout>
</template>

<style scoped>
.text-muted-foreground { color: rgb(108 115 125); }
</style>
