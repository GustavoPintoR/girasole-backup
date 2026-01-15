<script setup lang="ts">
import { Eye, MoreHorizontal } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { ForecastLog } from '@/types';

const props = defineProps<{
    forecastLog: ForecastLog
}>()

const { can } = usePermissions();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">{{ trans('ui.open_menu') }}</span>

                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end">
            <DropdownMenuLabel>{{ trans('ui.actions') }}</DropdownMenuLabel>

            <DropdownMenuSeparator />

            <DropdownMenuItem v-if="can.read_forecast_log" class="p-0">
                <Link :href="route('forecast-logs.show', props.forecastLog.id)" class="w-full">
                <Button variant="ghost" size="sm" :title="trans('ui.view')" class="w-full justify-start">
                    <Eye class="h-4 w-4" /> {{ trans('ui.view') }}
                </Button>
                </Link>
            </DropdownMenuItem>

        </DropdownMenuContent>
    </DropdownMenu>
</template>
