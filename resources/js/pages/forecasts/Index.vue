<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, h, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3';
import DataTable from '@/components/DataTable/DataTable.vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { Button } from '@/components/ui/button'
import { Table, TableHead, TableHeader, TableBody, TableRow, TableCell } from '@/components/ui/table'
import { trans } from 'laravel-vue-i18n'
import IntCell from '@/components/DataTable/IntCell.vue'

interface ForecastSetupRow {
    id: number
    field: {
        id: number
        name: string
        owner_name: string | null
        company_name: string | null
    }
    days: Record<string, boolean>
    retention: number | null
}

const breadcrumbs = [
    {
        title: trans('ui.dashboard'),
        href: route('dashboard'),
    },
    {
        title: trans('ui.weather_forecast_setup'),
        href: route('forecasts.index'),
    },
];

const page = usePage()
const setups = page.props.setups as any
const sorting = page.props.sorting as any
const filtering = page.props.filtering as any
const remainingCalls = page.props.remainingCalls as Record<string, number>
const logsWeek = page.props.logsWeek as any
const forecastLogsStats = page.props.forecastLogsStats as any

const modifiedSetups = ref<Record<number, { days: Record<string, boolean>, retention: number | null }>>({})

function toggleDay(row: ForecastSetupRow, day: string) {
    const fieldId = row.field.id
    if (!modifiedSetups.value[fieldId]) {
        modifiedSetups.value[fieldId] = { days: { ...row.days }, retention: row.retention }
    }
    modifiedSetups.value[fieldId].days[day] = !modifiedSetups.value[fieldId].days[day]
}

function updateRetention(row: ForecastSetupRow, value: number | null) {
    const fieldId = row.field.id

    if (!modifiedSetups.value[fieldId]) {
        modifiedSetups.value[fieldId] = { days: { ...row.days }, retention: row.retention }
    }
    modifiedSetups.value[fieldId].retention = value
}

const hasChanges = computed(() => Object.keys(modifiedSetups.value).length > 0)

function saveChanges() {
    const changes = Object.entries(modifiedSetups.value).map(([id, data]) => ({
        id: parseInt(id),
        days: data.days,
        retention: data.retention
    }))

    router.post(route('forecasts.update'), { changes }, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            modifiedSetups.value = {}
        }
    })
}

const dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
const dayLabels: Record<string, string> = {
    monday: trans('ui.monday_short'),
    tuesday: trans('ui.tuesday_short'),
    wednesday: trans('ui.wednesday_short'),
    thursday: trans('ui.thursday_short'),
    friday: trans('ui.friday_short'),
    saturday: trans('ui.saturday_short'),
    sunday: trans('ui.sunday_short'),
}

const dayTotals = computed(() => {
    const totals: Record<string, number> = {}
    for (const day of dayOrder) {
        totals[day] = setups.data.filter((r: ForecastSetupRow) => r.days[day]).length
    }
    return totals
})

const columns: ColumnDef<ForecastSetupRow>[] = [
    {
        id: 'field_name',
        accessorKey: 'field.name',
        header: () => h('div', { class: 'font-semibold' }, trans('ui.field')),
        cell: ({ row }) => h('div', row.original.field.name)
    },
    {
        id: 'user_name',
        accessorKey: 'field.owner_name',
        header: () => h('div', { class: 'font-semibold' }, trans('ui.owner')),
        cell: ({ row }) => h('div', row.original.field.owner_name ?? '-')
    },
    {
        id: 'company_name',
        accessorKey: 'field.company_name',
        header: () => h('div', { class: 'font-semibold' }, trans('ui.company')),
        cell: ({ row }) => h('div', row.original.field.company_name ?? '-')
    },
    ...dayOrder.map(day => ({
        id: day,
        accessorFn: (row: ForecastSetupRow) => {
            const fieldId = row.field.id
            if (modifiedSetups.value[fieldId] && modifiedSetups.value[fieldId].days[day] !== undefined) {
                return modifiedSetups.value[fieldId].days[day] ? 1 : 0
            }
            return row.days[day] ? 1 : 0
        },
        header: () => h('div', { class: 'text-center font-semibold w-8' }, dayLabels[day]),
        cell: ({ row }: any) => {
            const fieldId = row.original.field.id
            const isChecked = modifiedSetups.value[fieldId]
                ? modifiedSetups.value[fieldId].days[day]
                : row.original.days[day]

            return h('div', { class: 'flex justify-center w-8' }, [
                h('input', {
                    type: 'checkbox',
                    class: 'cursor-pointer h-4 w-4 accent-primary',
                    checked: isChecked,
                    onClick: () => {
                        toggleDay(row.original, day)
                    }
                })
            ])
        }
    })),
    {
        id: 'retention',
        accessorKey: 'retention',
        header: () => h('div', { class: 'text-center font-semibold w-16' }, trans('ui.retention')),
        cell: ({ row }) => {
            const fieldId = row.original.field.id
            const value = modifiedSetups.value[fieldId]
                ? modifiedSetups.value[fieldId].retention
                : row.original.retention

            return h(IntCell, {
                modelValue: value,
                min: 0,
                'onUpdate:modelValue': (val: number | null) => updateRetention(row.original, val)
            })
        }
    }
]

</script>

<template>

    <Head :title="trans('ui.weather_forecast_setup')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- counters card -->
                    <div class="space-y-6">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ trans('ui.weather_forecast_setup') }}
                            </h1>
                        </div>

                        <div class="mb-6 w-full">
                            <div class="bg-card text-card-foreground rounded-lg border p-4 shadow-sm">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="w-full">
                                        <div class="text-sm font-medium text-muted-foreground mb-1">
                                            {{ trans('ui.total') }}
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-sm">
                                            <span v-for="day in dayOrder" :key="day"
                                                class="inline-flex items-center px-2 py-1 bg-muted rounded text-muted-foreground">
                                                <span class="font-semibold mr-2">{{ dayLabels[day] }}:</span>
                                                <span>{{ dayTotals[day] }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="w-full">
                                        <div class="text-sm font-medium text-muted-foreground mb-1">
                                            {{ trans('ui.remaining') }}
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-sm">
                                            <span v-for="day in dayOrder" :key="`rem-${day}`"
                                                class="inline-flex items-center px-2 py-1 bg-muted rounded text-muted-foreground">
                                                <span class="font-semibold mr-2">{{ dayLabels[day] }}:</span>
                                                <span>{{ remainingCalls[day] }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- log table and filters -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="text-sm text-muted-foreground">
                                <div class="text-sm font-medium mb-1">{{ trans('ui.current_week') }}</div>
                                <div class="pt-1 flex items-center justify-center">{{ logsWeek.start }} - {{ logsWeek.end }}</div>
                            </div>
                        </div>

                        <!-- log table -->
                        <div class="bg-card text-card-foreground rounded-lg border shadow-sm overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="text-left px-3 py-2">{{ trans('ui.status') }}</TableHead>
                                        <TableHead v-for="day in forecastLogsStats.days" :key="'h-' + day"
                                            class="text-center px-3 py-2 uppercase">{{ dayLabels[day] }}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell class="px-3 py-2 font-medium">{{ trans('ui.done') }}</TableCell>
                                        <TableCell v-for="day in forecastLogsStats.days" :key="'d-' + day"
                                            class="px-3 py-2 text-center">
                                            {{ forecastLogsStats.rows.done[day] }}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell class="px-3 py-2 font-medium">{{ trans('ui.errors') }}</TableCell>
                                        <TableCell v-for="day in forecastLogsStats.days" :key="'e-' + day"
                                            class="px-3 py-2 text-center">
                                            {{ forecastLogsStats.rows.errors[day] }}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell class="px-3 py-2 font-medium">{{ trans('ui.total') }}</TableCell>
                                        <TableCell v-for="day in forecastLogsStats.days" :key="'t-' + day"
                                            class="px-3 py-2 text-center">
                                            {{ forecastLogsStats.rows.total[day] }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>

                <!-- setups -->
                <div>
                    <DataTable :columns="columns" :data="setups.data" :pagination="setups.pagination" :sorting="sorting"
                        :filtering="filtering" :route="route('forecasts.index')"
                        :search-placeholder="trans('ui.search_placeholder')" striped />

                    <div class="mt-4 flex justify-end" v-if="hasChanges">
                        <Button @click="saveChanges">{{ trans('ui.save') }}</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
