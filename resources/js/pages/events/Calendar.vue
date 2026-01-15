<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useAppearance } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import { ScheduleXCalendar } from '@schedule-x/vue'
import {
    createCalendar,
    createViewDay,
    createViewMonthAgenda,
    createViewMonthGrid,
    createViewWeek,
    createViewList
} from '@schedule-x/calendar'
import { createEventModalPlugin } from '@schedule-x/event-modal'
import 'temporal-polyfill/global'

import '@schedule-x/theme-shadcn/dist/index.css'
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { computed } from 'vue';
import Label from '@/components/ui/label/Label.vue';
import { ChevronDown, MapPin } from 'lucide-vue-next';
import Badge from '@/components/ui/badge/Badge.vue';

const page = usePage();
const { appearance } = useAppearance();
const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

const breadcrumbs = [
    {
        title: trans('ui.dashboard'),
        href: route('dashboard'),
    },
    {
        title: trans('ui.calendar'),
        href: route('events.calendar'),
    },
];

interface Event {
    id: string;
    title: string;
    description: string;
    calendarId: string;
    start: string;
    end: string;
}

interface GroupData {
    id: number;
    name: string;
    total_area: number;
    units_count: number;
    creation_method: 'units' | 'manual' | 'import';
    color: string;
    boundary_geometry_json: string | null;
    centroid_json: string | null;
    sensors?: any[];
    company?: {
        id: number;
        name: string;
    };
    events?: any[];
}

const events = ref<Event[]>([]);

const props = defineProps({
    userTimezone: {
        type: String,
        default: 'UTC',
    },
});

const cadastralGroups = ref<GroupData[]>([]);
const selectedGroupId = ref<number | null>(null);
const shouldShowCompany = ref(false);
const dropdownOpen = ref(false);
const selectedGroup = computed(() =>
    selectedGroupId.value ? cadastralGroups.value.find(g => g.id === selectedGroupId.value) : null
);
const enforceScroll = ref(true);
let _mutationObserver: MutationObserver | null = null;
let _enforceTimeout: number | null = null;

onMounted(async () => {
    loadGroupsData();
    document.addEventListener('click', handleClickOutside);
});

const isLoadingCalendar = ref(true);

const loadGroupsData = async (groupId?: number) => {
    try {
        isLoadingCalendar.value = true

        const response = await fetch(route('api.internal.cadastral-groups.mapData', {
            user: page.props.auth.user.id,
            group_id: groupId ?? null
        }))

        const data = await response.json()

        cadastralGroups.value = data.groups || []
        shouldShowCompany.value = data.shouldShowCompany || false

        if (!selectedGroupId.value && cadastralGroups.value.length) {
            selectedGroupId.value = cadastralGroups.value[0].id
        }

        if (cadastralGroups.value.length && selectedGroupId.value) {
            const group = data.groups.find(g => g.id === selectedGroupId.value);
            if (group && group.events) {
                events.value = group.events;
            }
        }
        
        calendarApp.events.set(
            formatEventsWithTemporal(events.value) as any
        )

    } catch (error) {
        console.error('Error loading groups data:', error)
    } finally {
        isLoadingCalendar.value = false
    }
}


const getCreationMethodLabel = (method: string): string => {
    const labels: Record<string, string> = {
        units: trans('ui.from_units'),
        manual: trans('ui.drawn'),
        import: trans('ui.imported')
    };
    return labels[method] || method;
};

const getCreationMethodVariant = (method: string): string => {
    const variants: Record<string, string> = {
        units: 'default',
        manual: 'secondary',
        import: 'outline'
    };
    return variants[method] || 'outline';
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.custom-dropdown')) {
        dropdownOpen.value = false;
    }
};

onMounted(() => {
    loadGroupsData();
    document.addEventListener('click', handleClickOutside);

    try {
        window.scrollTo(0, 0);

        enforceScroll.value = true;
        _mutationObserver = new MutationObserver(() => {
            if (enforceScroll.value) {
                window.scrollTo(0, 0);
            }
        });
        _mutationObserver.observe(document.body, { childList: true, subtree: true, attributes: true, characterData: true });

        // quit enforcing after 2 sexs
        _enforceTimeout = window.setTimeout(() => {
            enforceScroll.value = false;
            if (_mutationObserver) {
                _mutationObserver.disconnect();
                _mutationObserver = null;
            }
            if (_enforceTimeout) {
                _enforceTimeout = null;
            }
        }, 2000);
    } catch (e) {
        // ignore if window/document not available in some environments
        console.error('scroll error:', e);
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);

    if (_mutationObserver) {
        _mutationObserver.disconnect();
        _mutationObserver = null;
    }
    if (_enforceTimeout) {
        clearTimeout(_enforceTimeout);
        _enforceTimeout = null;
    }
});

const selectGroup = async (id: number) => {
    selectedGroupId.value = id
    dropdownOpen.value = false

    await loadGroupsData(id)
}

const formatEventsWithTemporal = (events: Event[]) => {
    return events.map(event => {
        const baseEvent = {
            id: String(event.id),
            title: event.title || 'Untitled',
            description: event.description,
            calendarId: event.calendarId || 'default',
        };

        if (event.start && event.start.length === 10) {
            return {
                ...baseEvent,
                start: Temporal.PlainDate.from(event.start),
                end: Temporal.PlainDate.from(event.end || event.start),
            };
        }

        if (event.start) {
            const startDateTime = event.start.replace(' ', 'T') + ':00Z';
            const endDateTime = event.end ? event.end.replace(' ', 'T') + ':00Z' : startDateTime;

            const startInstant = Temporal.Instant.from(startDateTime);
            const endInstant = Temporal.Instant.from(endDateTime);

            return {
                ...baseEvent,
                start: startInstant.toZonedDateTimeISO(props.userTimezone),
                end: endInstant.toZonedDateTimeISO(props.userTimezone),
            };
        }

        return baseEvent;
    });
};

const eventModalPlugin = createEventModalPlugin();

const calendarApp = createCalendar({
  theme: 'shadcn',
  views: [
    createViewDay(),
    createViewWeek(),
    createViewMonthGrid(),
    createViewMonthAgenda(),
    createViewList(),
  ],
  defaultView: 'month-grid',
  calendars: {
    meetings: {
      colorName: 'meetings',
      lightColors: {
        main: '#2563eb',
        container: '#dbeafe',
        onContainer: '#1e3a8a',
      },
      darkColors: {
        main: '#93c5fd',
        container: '#1e40af',
        onContainer: '#dbeafe',
      },
    },
    deadlines: {
      colorName: 'deadlines',
      lightColors: {
        main: '#dc2626',
        container: '#fee2e2',
        onContainer: '#7f1d1d',
      },
      darkColors: {
        main: '#fca5a5',
        container: '#991b1b',
        onContainer: '#fee2e2',
      },
    },
    holidays: {
      colorName: 'holidays',
      lightColors: {
        main: '#16a34a',
        container: '#dcfce7',
        onContainer: '#14532d',
      },
      darkColors: {
        main: '#86efac',
        container: '#166534',
        onContainer: '#dcfce7',
      },
    },
    maintenance: {
      colorName: 'maintenance',
      lightColors: {
        main: '#f59e0b',
        container: '#fef3c7',
        onContainer: '#78350f',
      },
      darkColors: {
        main: '#fcd34d',
        container: '#92400e',
        onContainer: '#fef3c7',
      },
    },
    training: {
      colorName: 'training',
      lightColors: {
        main: '#7c3aed',
        container: '#ede9fe',
        onContainer: '#4c1d95',
      },
      darkColors: {
        main: '#c4b5fd',
        container: '#5b21b6',
        onContainer: '#ede9fe',
      },
    },
  },
  events: formatEventsWithTemporal(events.value || []) as any,
  plugins: [eventModalPlugin],
  isResponsive: true,
  locale: page.props.appLocale === 'it' ? 'it-IT' : 'en-US',
})

onMounted(() => {
    let theme: 'light' | 'dark';

    if (appearance.value === 'system') {
        theme = systemTheme;
    } else {
        theme = appearance.value;
    }

    calendarApp.setTheme(theme);
})

</script>

<template>

    <Head :title="trans('ui.events_calendar')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="rounded-lg">
                <div class="p-4">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ trans('ui.events_calendar') }}
                    </h2>
                </div>
                
                <!-- Cadastral Group Selection -->
                <div class="bg-white dark:bg-black border border-gray-200 dark:border-black p-6 mb-6">
                    <Label class="mb-2 font-medium text-gray-700 dark:text-gray-300 block">{{ trans('ui.cadastral_group') }}</Label>
                    <!-- Custom dropdown selector -->
                    <div class="custom-dropdown relative w-full md:w-[300px]">
                        <button @click="dropdownOpen = !dropdownOpen"
                            class="w-auto px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors text-left">
                            <span v-if="selectedGroup" class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded flex-shrink-0" :style="{ backgroundColor: selectedGroup.color }" />
                                <span class="truncate">
                                    {{ selectedGroup.name }}
                                    <span v-if="shouldShowCompany && selectedGroup['company']?.name" class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ selectedGroup['company'].name }})</span>
                                </span>
                            </span>
                            <span v-else class="text-gray-500 dark:text-gray-400">{{ trans('ui.select_cadastral_group') }}</span>
                            <ChevronDown class="h-4 w-4 text-gray-400 flex-shrink-0" />
                        </button>

                        <!-- Dropdown menu -->
                        <div v-if="dropdownOpen"
                            class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 max-h-[300px] overflow-y-auto z-50">
                            <div v-for="group in cadastralGroups" :key="group.id" @click="selectGroup(group.id)"
                                class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <div class="w-3 h-3 rounded flex-shrink-0" :style="{ backgroundColor: group.color }" />
                                <span class="flex-1 truncate">
                                    {{ group.name }}
                                    <span v-if="shouldShowCompany && group['company']?.name" class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ group['company'].name }})</span>
                                </span>
                                <div class="flex items-center gap-1">
                                    <Badge v-if="group.sensors?.length" variant="outline" class="text-xs">
                                        <MapPin class="h-3 w-3 mr-1" />
                                        {{ group.sensors.length }}
                                    </Badge>
                                    <Badge :variant="getCreationMethodVariant(group.creation_method) as any" class="text-xs">
                                        {{ getCreationMethodLabel(group.creation_method) }}
                                    </Badge>
                                </div>
                            </div>
                            <div v-if="cadastralGroups.length === 0" class="px-3 py-2 text-gray-500 text-sm">
                                {{ trans('ui.no_groups_available') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-if="isLoadingCalendar"
                    class="min-h-[600px] flex items-center justify-center bg-white dark:bg-black border border-gray-200 dark:border-black"
                >
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white"></div>
                </div>

                <!-- Calendar -->
                <div v-else class="min-h-[600px]">
                    <ScheduleXCalendar :calendar-app="calendarApp" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.sx__calendar-wrapper) {
    height: 600px;
}

:deep(.sx__event-modal) {
    z-index: 9999;
}

:deep(.sx__event-modal-overlay) {
    background-color: rgba(0, 0, 0, 0.5);
}

:deep(.sx__event-modal-content) {
    max-width: 500px;
    margin: 2rem auto;
}
</style>
