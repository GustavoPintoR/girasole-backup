<script setup lang="ts" generic="TData">
import { type Table } from '@tanstack/vue-table'
import { computed } from 'vue'
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { trans } from 'laravel-vue-i18n'

interface DataTablePaginationProps {
    table: Table<TData>
    serverSideInfo?: {
        from: number
        to: number
        total: number
    }
    currentPageSize: number
    currentPageIndex: number
    pageCount: number
}
const props = defineProps<DataTablePaginationProps>()

const canGoPrevious = computed(() => props.currentPageIndex > 0)
const canGoNext = computed(() => props.currentPageIndex < props.pageCount - 1)
const canGoFirst = computed(() => props.currentPageIndex > 0)
const canGoLast = computed(() => props.currentPageIndex < props.pageCount - 1)
</script>

<template>
    <div class="flex items-center justify-between px-2 py-4">
        <div class="flex-1 text-sm text-muted-foreground">
            <template v-if="serverSideInfo">
                {{ trans('ui.pagination_info', { from: String(serverSideInfo.from), to: String(serverSideInfo.to), total: String(serverSideInfo.total) }) }}
            </template>
            <template v-else>
                {{ table.getFilteredSelectedRowModel().rows.length }} of
                {{ table.getFilteredRowModel().rows.length }} row(s) selected.
            </template>
        </div>
        <div class="flex items-center space-x-6 lg:space-x-8">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium">
                    {{ trans('ui.rows_per_page') }}
                </p>
                <Select :model-value="`${currentPageSize}`"
                    @update:model-value="(value) => table.setPageSize(Number(value))">
                    <SelectTrigger class="h-8 w-[70px] cursor-pointer">
                        <SelectValue :placeholder="`${currentPageSize}`" />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem v-for="pageSize in [10, 20, 30, 40, 50]" :key="pageSize" :value="`${pageSize}`">
                            {{ pageSize }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div class="flex w-[100px] items-center justify-center text-sm font-medium">
                {{ trans('ui.pagination_current_page', { page: String(currentPageIndex + 1), total: String(pageCount) }) }}
            </div>
            <div class="flex items-center space-x-2">
                <Button variant="outline" class="hidden w-8 h-8 p-0 lg:flex cursor-pointer" :disabled="!canGoFirst"
                    @click="table.setPageIndex(0)">
                    <span class="sr-only">{{ trans('ui.go_to_first_page') }}</span>
                    <ChevronsLeft class="w-4 h-4" />
                </Button>

                <Button variant="outline" class="w-8 h-8 p-0 cursor-pointer" :disabled="!canGoPrevious"
                    @click="table.previousPage()">
                    <span class="sr-only">{{ trans('ui.go_to_prev_page') }}</span>
                    <ChevronLeft class="w-4 h-4" />
                </Button>

                <Button variant="outline" class="w-8 h-8 p-0 cursor-pointer" :disabled="!canGoNext"
                    @click="table.nextPage()">
                    <span class="sr-only">{{ trans('ui.go_to_next_page') }}</span>
                    <ChevronRight class="w-4 h-4" />
                </Button>

                <Button variant="outline" class="hidden w-8 h-8 p-0 lg:flex cursor-pointer" :disabled="!canGoLast"
                    @click="table.setPageIndex(pageCount - 1)">
                    <span class="sr-only">{{ trans('ui.go_to_last_page') }}</span>
                    <ChevronsRight class="w-4 h-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
