<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, watch, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import CustomFieldsInput from '@/components/shared/CustomFieldsInput.vue';
import { trans } from 'laravel-vue-i18n';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { CadastralGroup, CustomField } from '@/types';

interface User {
  id: number;
  name: string;
  email: string;
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
  attendees?: CadastralGroup[];
  calendarId?: string;
}

const props = defineProps<{
  event: Event;
  cadastralGroups: CadastralGroup[];
  customFieldValues?: Record<string, any>;
}>();

const breadcrumbs = [
  {
    title: trans('ui.events'),
    href: route('events.index'),
  },
  {
    title: props.event.title,
    href: route('events.edit', props.event.id),
  },
];

// format ISO datetime string for datetime-local input
const formatDateTimeForInput = (dateString: string | null): string => {
  if (!dateString) return '';
  const date = new Date(dateString);

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${year}-${month}-${day}T${hours}:${minutes}`;
};

//format ISO date string for date input
const formatDateForInput = (dateString: string | null): string => {
  if (!dateString) return '';
  return dateString.split('T')[0];
};

const form = useForm({
  all_day: props.event.all_day,
  title: props.event.title,
  description: props.event.description || '',
  start: formatDateTimeForInput(props.event.start),
  end: formatDateTimeForInput(props.event.end),
  start_date: formatDateForInput(props.event.start_date),
  end_date: formatDateForInput(props.event.end_date),
  cadastral_group_ids: [] as number[],
  calendarId: props.event.calendarId ?? 'default',
  assign_to_all: false as boolean,
  custom_fields: props.customFieldValues || {} as Record<string, any>,
});

// Initialize cadastral_group_ids with current attendees
onMounted(() => {
  if (props.event.attendees && props.event.attendees.length) {
    form.cadastral_group_ids = props.event.attendees.map(
      (group) => group.id
    )

    // Optional: auto-check "assign to all"
    if (form.cadastral_group_ids.length === props.cadastralGroups.length) {
      form.assign_to_all = true
    }
  }
})

const showDateTimeInputs = computed(() => !form.all_day);
const showDateOnlyInputs = computed(() => form.all_day);
const selectedcadastralGroupsCount = computed(() => form.cadastral_group_ids.length);
const allCadastralGroupsSelected = computed(() => form.cadastral_group_ids.length === props.cadastralGroups.length);

function setCadastralGroupSelection(cadastralGroupId: number, selected: boolean) {
  const index = form.cadastral_group_ids.indexOf(cadastralGroupId);

  if (selected && index === -1) {
    form.cadastral_group_ids.push(cadastralGroupId);
  } else if (!selected && index > -1) {
    form.cadastral_group_ids.splice(index, 1);
  }
}

function selectAllCadastralGroups() {
  form.cadastral_group_ids = props.cadastralGroups.map(u => u.id);
}

function deselectAllCadastralGroups() {
  form.cadastral_group_ids = [];
}

function toggleSelectAll() {
  if (allCadastralGroupsSelected.value) {
    deselectAllCadastralGroups();
  } else {
    selectAllCadastralGroups();
  }
}

watch(() => form.assign_to_all, (newValue) => {
  if (newValue) {
    selectAllCadastralGroups();
  }
});

watch(() => form.all_day, (newValue) => {
  if (newValue) {
    // clear datetime fields when switching to all-day
    form.start = '';
    form.end = '';
  } else {
    // clear date-only fields when switching to datetime
    form.start_date = '';
    form.end_date = '';
  }
});

function submit() {
  form.put(route('events.update', props.event.id), {
    preserveScroll: true,
  });
}

const cancel = () => {
  router.visit(route('events.index'));
};

const calendarOptions = [
  { key: 'meetings', label: trans('ui.calendar_meetings') ?? 'Meetings', color: '#2563eb' },
  { key: 'deadlines', label: trans('ui.calendar_deadlines') ?? 'Deadlines', color: '#dc2626' },
  { key: 'holidays', label: trans('ui.calendar_holidays') ?? 'Holidays', color: '#16a34a' },
  { key: 'maintenance', label: trans('ui.calendar_maintenance') ?? 'Maintenance', color: '#f59e0b' },
  { key: 'training', label: trans('ui.calendar_training') ?? 'Training', color: '#7c3aed' },
]
</script>

<template>

  <Head :title="trans('ui.update_event')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.update_event') }}
      </h1>

      <Card class="max-w-4xl">
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- All Day Checkbox - Using native input for reliability -->
            <div class="flex items-center space-x-2">
              <input type="checkbox" id="all_day" v-model="form.all_day"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              <Label for="all_day" class="cursor-pointer">
                {{ trans('ui.all_day_event') }}
              </Label>
            </div>

            <!-- Title -->
            <div class="space-y-2">
              <Label for="title">{{ trans('ui.event_title') }}</Label>
              <Input id="title" v-model="form.title" type="text" placeholder="Enter event title"
                :class="{ 'border-destructive': form.errors.title }" />
              <p v-if="form.errors.title" class="text-sm text-destructive">
                {{ form.errors.title }}
              </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">{{ trans('ui.event_description') }}</Label>
              <textarea id="description" v-model="form.description" placeholder="Enter event description"
                class="w-full min-h-[100px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :class="{ 'border-destructive': form.errors.description }" rows="4"></textarea>
              <p v-if="form.errors.description" class="text-sm text-destructive">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Event Type / Calendar -->
            <div class="space-y-2">
              <Label>
                {{ trans('ui.event_type') ?? 'Event type' }}
              </Label>

              <Select v-model="form.calendarId">
                <SelectTrigger class="w-full">
                  <SelectValue>
                    <div class="flex items-center gap-2">
                      <span
                        class="h-3 w-3 rounded-full"
                        :style="{
                          backgroundColor: calendarOptions.find(o => o.key === form.calendarId)?.color
                        }"
                      />
                      <span>
                        {{ calendarOptions.find(o => o.key === form.calendarId)?.label }}
                      </span>
                    </div>
                  </SelectValue>
                </SelectTrigger>

                <SelectContent>
                  <SelectItem
                    v-for="option in calendarOptions"
                    :key="option.key"
                    :value="option.key"
                  >
                    <div class="flex items-center gap-2">
                      <span
                        class="h-3 w-3 rounded-full"
                        :style="{ backgroundColor: option.color }"
                      />
                      <span>{{ option.label }}</span>
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>

              <p v-if="form.errors.calendarId" class="text-sm text-destructive">
                {{ form.errors.calendarId }}
              </p>
            </div>

            <!-- Custom Fields Section -->
            <CustomFieldsInput v-if="customFields && customFields.length > 0" :fields="customFields"
              v-model="form.custom_fields" :errors="form.errors" />

            <!-- DateTime Inputs (shown if not all_day) -->
            <div v-if="showDateTimeInputs" class="space-y-4">
              <div class="space-y-2">
                <Label for="start">{{ trans('ui.event_start') }}</Label>
                <Input id="start" v-model="form.start" type="datetime-local"
                  :class="{ 'border-destructive': form.errors.start }" />
                <p v-if="form.errors.start" class="text-sm text-destructive">
                  {{ form.errors.start }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="end">{{ trans('ui.event_end') }}</Label>
                <Input id="end" v-model="form.end" type="datetime-local"
                  :class="{ 'border-destructive': form.errors.end }" />
                <p v-if="form.errors.end" class="text-sm text-destructive">
                  {{ form.errors.end }}
                </p>
              </div>
            </div>

            <!-- Date-only Inputs (shown if all_day) -->
            <div v-if="showDateOnlyInputs" class="space-y-4">
              <div class="space-y-2">
                <Label for="start_date">{{ trans('ui.event_start_date') }}</Label>
                <Input id="start_date" v-model="form.start_date" type="date"
                  :class="{ 'border-destructive': form.errors.start_date }" />
                <p v-if="form.errors.start_date" class="text-sm text-destructive">
                  {{ form.errors.start_date }}
                </p>
              </div>
              <div class="space-y-2">
                <Label for="end_date">{{ trans('ui.event_end_date') }}</Label>
                <Input id="end_date" v-model="form.end_date" type="date"
                  :class="{ 'border-destructive': form.errors.end_date }" />
                <p v-if="form.errors.end_date" class="text-sm text-destructive">
                  {{ form.errors.end_date }}
                </p>
              </div>
            </div>

            <!-- Cadastral group Assignment Section -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <Label>{{ trans('ui.assign_to_fields') }}</Label>

                <!-- Assign to All Checkbox -->
                <div class="flex items-center space-x-2">
                  <input type="checkbox" id="assign_to_all" v-model="form.assign_to_all"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  <Label for="assign_to_all" class="cursor-pointer font-normal">
                    {{ trans('ui.assign_to_all_fields') }}
                  </Label>
                </div>
              </div>

              <!-- Cadastral group Selection List -->
              <div class="border rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                <div class="flex items-center justify-between mb-2 pb-2 border-b">
                  <span class="text-sm text-gray-600">
                    {{ selectedcadastralGroupsCount }} {{ trans('ui.cadastral_groups_selected') }}
                  </span>
                  <Button type="button" variant="ghost" size="sm" @click="toggleSelectAll"
                    :disabled="form.assign_to_all">
                    {{ allCadastralGroupsSelected ? trans('ui.deselect_all') : trans('ui.select_all') }}
                  </Button>
                </div>

                <!-- Cadastral Group checkboxes  -->
                <div v-for="cadastralGroup in cadastralGroups" :key="cadastralGroup.id" class="flex items-center space-x-2">
                  <input type="checkbox" :id="`cadastralGroup-${cadastralGroup.id}`" :checked="form.cadastral_group_ids.includes(cadastralGroup.id)"
                    @change="(e) => setCadastralGroupSelection(cadastralGroup.id, (e.target as HTMLInputElement).checked)"
                    :disabled="form.assign_to_all"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                  <Label :for="`cadastralGroup-${cadastralGroup.id}`" class="cursor-pointer font-normal flex-1"
                    :class="{ 'opacity-50': form.assign_to_all }">
                    {{ cadastralGroup.name }}
                  </Label>
                </div>
              </div>

              <p v-if="form.errors.cadastral_group_ids" class="text-sm text-destructive">
                {{ form.errors.cadastral_group_ids }}
              </p>
              <!-- <p class="text-sm text-gray-500">
                {{ trans('ui.event_assignment_help') }}
              </p> -->
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.updating') || 'Updating...' : trans('ui.update') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
