<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import { Proportions, SatelliteDish } from 'lucide-vue-next';

interface Metrics {
  cadastralGroups: number;
  sensors: number;
}

defineProps<{
  metrics: Metrics;
}>();
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Cadastral Groups Metric -->
    <Card class="relative overflow-hidden">
      <CardHeader class="pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">
          {{ trans('ui.cadastral_groups') }}
        </CardTitle>
      </CardHeader>
      <CardContent class="pb-4">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <div class="text-3xl font-bold text-primary">
              {{ metrics.cadastralGroups }}
            </div>
            <p class="text-xs text-muted-foreground mt-1">
              {{ trans('ui.total_groups') }}
            </p>
          </div>
          <div class="w-12 h-12 flex items-center justify-center bg-green-100 dark:bg-green-900/20 rounded-full">
            <Proportions class="w-6 h-6 text-green-600 dark:text-green-400" />
          </div>
        </div>
        <!-- Progress indicator -->
        <div class="mt-3">
          <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
            <div 
              class="h-full bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all duration-500"
              :style="{ width: metrics.cadastralGroups > 0 ? '100%' : '0%' }"
            ></div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Sensors Metric -->
    <Card class="relative overflow-hidden">
      <CardHeader class="pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">
          {{ trans('ui.sensors') }}
        </CardTitle>
      </CardHeader>
      <CardContent class="pb-4">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <div class="text-3xl font-bold text-primary">
              {{ metrics.sensors }}
            </div>
            <p class="text-xs text-muted-foreground mt-1">
              {{ trans('ui.active_sensors') }}
            </p>
          </div>
          <div class="w-12 h-12 flex items-center justify-center bg-blue-100 dark:bg-blue-900/20 rounded-full">
            <SatelliteDish class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
        <!-- Progress indicator -->
        <div class="mt-3">
          <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
            <div 
              class="h-full bg-gradient-to-r from-blue-400 to-blue-600 rounded-full transition-all duration-500"
              :style="{ width: metrics.sensors > 0 ? '100%' : '0%' }"
            ></div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>