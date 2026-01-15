<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, PaginationState, SortingState } from '@tanstack/vue-table'
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core'
import {
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import { ChevronsUpDown, ChevronUp, ChevronDown, Search } from 'lucide-vue-next';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

import Pagination from './Pagination.vue'
import { trans } from 'laravel-vue-i18n';

interface ServerPaginationState {
    pageIndex: number
    pageSize: number
    pageCount: number
    total: number
    from: number
    to: number
}

interface ServerSortingState {
    sortBy: string | null
    sortOrder: 'asc' | 'desc' | null
}

interface ServerFilterState {
    search?: string
    searchableColumns?: string[]
}

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    pagination: ServerPaginationState
    sorting?: ServerSortingState
    filtering?: ServerFilterState
    route: string
    searchPlaceholder?: string
    striped?: boolean
}>()

const paginationState = computed<PaginationState>(() => ({
    pageIndex: props.pagination.pageIndex,
    pageSize: props.pagination.pageSize,
}))

const sortingState = computed<SortingState>(() => {
    if (!props.sorting?.sortBy) return []
    return [{
        id: props.sorting.sortBy,
        desc: props.sorting.sortOrder === 'desc'
    }]
})

const searchValue = ref(props.filtering?.search || '')

const handlePageChange = (page: number) => {
    router.get(props.route, {
        page,
        per_page: props.pagination.pageSize,
        sort_by: props.sorting?.sortBy,
        sort_order: props.sorting?.sortOrder,
        search: searchValue.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePageSizeChange = (pageSize: number) => {
    router.get(props.route, {
        page: 1,
        per_page: pageSize,
        sort_by: props.sorting?.sortBy,
        sort_order: props.sorting?.sortOrder,
        search: searchValue.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSortingChange = (sortBy: string, sortOrder: 'asc' | 'desc' | null) => {
    router.get(props.route, {
        page: 1,
        per_page: props.pagination.pageSize,
        sort_by: sortOrder ? sortBy : null,
        sort_order: sortOrder,
        search: searchValue.value || undefined,
    }, {
        preserveState: false,
        preserveScroll: true,
    });
};

const handleSearch = useDebounceFn((value: string) => {
    router.get(props.route, {
        page: 1,
        per_page: props.pagination.pageSize,
        sort_by: props.sorting?.sortBy,
        sort_order: props.sorting?.sortOrder,
        search: value || undefined,
    }, {
        preserveState: false,
        preserveScroll: true,
    });
}, 300)

watch(searchValue, (newValue) => {
    handleSearch(newValue)
})

const table = useVueTable({
    get data() { return props.data },
    get columns() { return props.columns },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    pageCount: props.pagination.pageCount,
    state: {
        get pagination() { return paginationState.value },
        get sorting() { return sortingState.value }
    },
    onPaginationChange: (updater) => {
        const currentState = paginationState.value
        const newState = typeof updater === 'function'
            ? updater(currentState)
            : updater;

        if (newState.pageIndex !== currentState.pageIndex) {
            handlePageChange(newState.pageIndex + 1);
        }
        if (newState.pageSize !== currentState.pageSize) {
            handlePageSizeChange(newState.pageSize);
        }
    },
    onSortingChange: (updater) => {
        const currentState = sortingState.value
        const newState = typeof updater === 'function'
            ? updater(currentState)
            : updater;

        if (newState.length === 0) {
            // Sorting was cleared
            handleSortingChange('', null);
        } else {
            const sort = newState[0];
            handleSortingChange(sort.id, sort.desc ? 'desc' : 'asc');
        }
    },
})
</script>

<template>
    <div class="space-y-4">
        <!-- Search Input -->
        <div v-if="filtering?.searchableColumns && filtering.searchableColumns.length > 0" class="flex items-center">
            <div class="relative max-w-sm">
                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input v-model="searchValue" :placeholder="searchPlaceholder || trans('ui.search_placeholder')"
                    type="search" class="pl-8" />
            </div>
        </div>

        <!-- Table -->
        <div class="border rounded-md">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <div v-if="!header.isPlaceholder && header.column.getCanSort()"
                                class="flex items-center cursor-pointer select-none justify-start px-4 py-2"
                                @click="header.column.toggleSorting()">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" class="truncate" />
                                <Button variant="ghost" size="sm" class="ml-2 h-6 w-6 p-0 cursor-pointer">
                                    <ChevronUp v-if="header.column.getIsSorted() === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-else-if="header.column.getIsSorted() === 'desc'" class="h-4 w-4" />
                                    <ChevronsUpDown v-else class="h-4 w-4" />
                                </Button>
                            </div>
                            <div v-else-if="!header.isPlaceholder" class="px-4 py-2">
                                <FlexRender :props="header.getContext()" :render="header.column.columnDef.header" />
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined"
                            :class="striped ? 'even:bg-muted/50' : ''">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="py-2 px-6">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-24 text-center">
                                {{ trans('ui.no_results') }}
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <Pagination :table="table" :server-side-info="{
            from: pagination.from,
            to: pagination.to,
            total: pagination.total
        }" :current-page-size="pagination.pageSize" :current-page-index="pagination.pageIndex"
            :page-count="pagination.pageCount" />
    </div>
</template>
