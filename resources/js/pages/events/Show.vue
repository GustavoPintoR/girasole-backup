<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
  ArrowLeft,
  Edit,
  Calendar,
  Clock,
  FileText,
  Users,
  CalendarDays,
  User,
  InfoIcon
} from 'lucide-vue-next';
import { format, parseISO, formatDistanceToNow, isPast, isFuture, isToday } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';
import { it, enUS } from 'date-fns/locale';
import { CadastralGroup } from '@/types';

interface User {
  id: number;
  name: string;
  email: string;
  full_name: string;
}

interface Event {
  id: number;
  all_day: boolean;
  title: string;
  description: string | null;
  start: string | null;
  end: string | null;
  start_date: string | null;
  end_date: string | null;
  attendees?: User[];
  user?: User;
  cadastral_group?: CadastralGroup;
  created_at: string;
  updated_at: string;
  calendarId?: string;
}

const page = usePage();
const locale = computed(() => {
    return page.props.appLocale === 'it' ? it : enUS;
});

const props = defineProps<{
  event: Event;
  customFieldValues: Record<string, any>;
}>();

const breadcrumbs = [
  {
    title: trans('ui.events'),
    href: route('events.index'),
  },
  {
    title: props.event.title,
    href: route('events.show', props.event.id),
  },
];

const handleBack = () => {
  router.visit(route('events.index'));
};

const handleEdit = () => {
  router.visit(route('events.edit', props.event.id));
};

const { can } = usePermissions();

// Computed properties for event status
const eventStatus = computed(() => {
  if (!props.event.start && !props.event.start_date) return 'draft';

  const eventDate = props.event.all_day
    ? props.event.start_date
    : props.event.start;

  if (!eventDate) return 'draft';

  const date = parseISO(eventDate);

  if (props.event.all_day) {
    const endDate = props.event.end_date ? parseISO(props.event.end_date) : date;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (isToday(date) || (date <= today && endDate >= today)) return 'ongoing';
    if (isPast(endDate)) return 'past';
    if (isFuture(date)) return 'upcoming';
  } else {
    const endDate = props.event.end ? parseISO(props.event.end) : date;
    const now = new Date();

    if (date <= now && endDate >= now) return 'ongoing';
    if (isPast(endDate)) return 'past';
    if (isFuture(date)) return 'upcoming';
  }

  return 'draft';
});

const statusBadgeVariant = computed(() => {
  switch (eventStatus.value) {
    case 'ongoing': return 'default';
    case 'past': return 'secondary';
    case 'upcoming': return 'outline';
    default: return 'secondary';
  }
});

const statusBadgeClass = computed(() => {
  switch (eventStatus.value) {
    case 'ongoing': return 'bg-green-700';
    case 'past': return '';
    case 'upcoming': return '';
    default: return '';
  }
});

const statusLabel = computed(() => {
  switch (eventStatus.value) {
    case 'ongoing': return trans('ui.ongoing');
    case 'past': return trans('ui.past_event');
    case 'upcoming': return trans('ui.upcoming');
    default: return trans('ui.draft');
  }
});

// Format event date/time display
const formatEventDate = (date: string | null) => {
  if (!date) return trans('ui.not_set');
  return format(parseISO(date), 'PPP');
};

const formatEventDateTime = (date: string | null) => {
  if (!date) return trans('ui.not_set');
  return format(parseISO(date), 'PPP p');
};

const getTimeFromNow = (date: string | null) => {
  if (!date) return '';
  return formatDistanceToNow(parseISO(date), { addSuffix: true, locale: locale.value });
};

const calendarOptions = [
  { key: 'meetings', label: trans('ui.calendar_meetings') ?? 'Meetings', color: '#2563eb' },
  { key: 'deadlines', label: trans('ui.calendar_deadlines') ?? 'Deadlines', color: '#dc2626' },
  { key: 'holidays', label: trans('ui.calendar_holidays') ?? 'Holidays', color: '#16a34a' },
  { key: 'maintenance', label: trans('ui.calendar_maintenance') ?? 'Maintenance', color: '#f59e0b' },
  { key: 'training', label: trans('ui.calendar_training') ?? 'Training', color: '#7c3aed' },
]
const eventCalendar = computed(() => {
  return (
    calendarOptions.find(c => c.key === props.event.calendarId) ??
    calendarOptions[0]
  )
})


</script>

<template>

  <Head :title="props.event.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ props.event.title }}
          </h1>
          <Badge :variant="statusBadgeVariant" :class="statusBadgeClass">
            {{ statusLabel }}
          </Badge>
          <Badge v-if="props.event.all_day" variant="outline">
            <CalendarDays class="h-3 w-3 mr-1" />
            {{ trans('ui.event_all_day') }}
          </Badge>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="handleBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            {{ trans('ui.back') }}
          </Button>
          <Button v-if="can.update_events" @click="handleEdit">
            <Edit class="h-4 w-4 mr-2" />
            {{ trans('ui.edit') }}
          </Button>
        </div>
      </div>

      <!-- Status Alert -->
      <Alert v-if="eventStatus === 'ongoing'"
        class="mb-6 border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20 max-w-4xl">
        <InfoIcon class="h-4 w-4 text-green-600" />
        <AlertDescription class="text-green-800 dark:text-green-200">
          {{ trans('ui.event_is_ongoing') }}
        </AlertDescription>
      </Alert>

      <!-- Event Timing -->
      <Card class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Calendar class="h-5 w-5 text-gray-500" />
            {{ trans('ui.event_timing') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- All Day Event Dates -->
            <template v-if="props.event.all_day">
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.event_start_date') }}
                </dt>
                <dd class="text-lg text-gray-900 dark:text-gray-100">
                  {{ formatEventDate(props.event.start_date) }}
                  <span v-if="props.event.start_date" class="text-sm text-gray-500 block mt-1">
                    {{ getTimeFromNow(props.event.start_date) }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.event_end_date') }}
                </dt>
                <dd class="text-lg text-gray-900 dark:text-gray-100">
                  {{ formatEventDate(props.event.end_date) }}
                  <span v-if="props.event.end_date" class="text-sm text-gray-500 block mt-1">
                    {{ getTimeFromNow(props.event.end_date) }}
                  </span>
                </dd>
              </div>
            </template>

            <!-- Regular Event Times -->
            <template v-else>
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.event_start') }}
                </dt>
                <dd class="text-lg text-gray-900 dark:text-gray-100">
                  {{ formatEventDateTime(props.event.start) }}
                  <span v-if="props.event.start" class="text-sm text-gray-500 block mt-1">
                    {{ getTimeFromNow(props.event.start) }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                  {{ trans('ui.event_end') }}
                </dt>
                <dd class="text-lg text-gray-900 dark:text-gray-100">
                  {{ formatEventDateTime(props.event.end) }}
                  <span v-if="props.event.end" class="text-sm text-gray-500 block mt-1">
                    {{ getTimeFromNow(props.event.end) }}
                  </span>
                </dd>
              </div>
            </template>
          </div>
        </CardContent>
      </Card>

      <!-- Description -->
      <Card v-if="props.event.description" class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <FileText class="h-5 w-5 text-gray-500" />
            {{ trans('ui.event_description') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
              {{ props.event.description }}
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Custom Fields -->
      <Card v-if="customFieldValues && Object.keys(customFieldValues).length > 0" class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle>{{ trans('ui.custom_fields') }}</CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="(field, key) in customFieldValues" :key="key">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ field.label }}
                <span v-if="field.unit" class="text-xs">({{ field.unit }})</span>
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ field.value || '—' }}
              </dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <!-- Attendees -->
      <Card class="max-w-4xl mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Users class="h-5 w-5 text-gray-500" />
            {{ trans('ui.attendees') }}
          </CardTitle>
          <CardDescription>
            {{ trans('ui.event_attendees_desc') }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="props.event.attendees && props.event.attendees.length > 0" class="space-y-3">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">
              {{ props.event.attendees.length }}
              {{ props.event.attendees.length === 1 ? trans('ui.attendee') : trans('ui.attendees') }}
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div v-for="attendee in props.event.attendees" :key="attendee.id"
                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <User class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ attendee.name }}
                  </p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                    {{ attendee.email }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500 dark:text-gray-400">
            {{ trans('ui.no_attendees') }}
          </div>
        </CardContent>
      </Card>

      <!-- Metadata -->
      <Card class="max-w-4xl">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Clock class="h-5 w-5 text-gray-500" />
            {{ trans('ui.metadata') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-if="props.event">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ trans('ui.created_by') }}
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ props.event.user?.full_name }} ({{ props.event.user?.email }})
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ trans('ui.created_at') }}
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ format(parseISO(props.event.created_at), 'PPP p', { locale: locale }) }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ trans('ui.last_updated') }}
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ format(parseISO(props.event.updated_at), 'PPP p', { locale: locale }) }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ trans('ui.event_id') }}
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                #{{ props.event.id }}
              </dd>
            </div>
            <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              {{ trans('ui.event_type') ?? 'Event type' }}
            </dt>
            <dd class="mt-1 flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
              <span
                class="h-2.5 w-2.5 rounded-full"
                :style="{ backgroundColor: eventCalendar.color }"
              />
              {{ eventCalendar.label }}
            </dd>
          </div>
          </dl>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
