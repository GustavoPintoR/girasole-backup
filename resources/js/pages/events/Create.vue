<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import CustomFieldsDialog from '@/components/shared/CustomFieldsDialog.vue';
import { trans } from 'laravel-vue-i18n';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { CustomField } from '@/types';

interface User {
  id: number;
  name: string;
  email: string;
  full_name: string;
}

interface CadastralGroup {
  id: number;
  name: string;
  user: User;
}

const props = defineProps<{
  cadastralGroups: CadastralGroup[];
  customFields?: CustomField[];
}>();

const breadcrumbs = [
  {
    title: trans('ui.events'),
    href: route('events.index'),
  },
  {
    title: trans('ui.create_event'),
    href: route('events.create'),
  },
];

const form = useForm({
  all_day: false,
  title: '',
  description: '',
  start: '',
  end: '',
  start_date: '',
  end_date: '',
  calendarId: '',
  cadastral_group_ids: [] as number[],
  assign_to_all: false,
  custom_fields: {} as Record<string, any>,
});

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
  form.post(route('events.store'), {
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

  <Head :title="trans('ui.create_event')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ trans('ui.create_event') }}
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
              <Input id="title" v-model="form.title" type="text" :placeholder="trans('ui.event_title_placeholder')"
                :class="{ 'border-destructive': form.errors.title }" />
              <p v-if="form.errors.title" class="text-sm text-destructive">
                {{ form.errors.title }}
              </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">{{ trans('ui.event_description') }}</Label>
              <textarea id="description" v-model="form.description"
                :placeholder="trans('ui.event_description_placeholder')"
                class="w-full min-h-[100px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :class="{ 'border-destructive': form.errors.description }" rows="4"></textarea>
              <p v-if="form.errors.description" class="text-sm text-destructive">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Event Type / Calendar -->
            <div class="space-y-2">
              <Label>
                {{ trans('ui.event_type') }}
              </Label>

              <Select v-model="form.calendarId">
              <SelectTrigger class="w-full">
                <SelectValue :placeholder="trans('ui.select_event_type')">
                  <template v-if="form.calendarId">
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
                  </template>
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
            <CustomFieldsDialog v-if="customFields && customFields.length > 0" :fields="customFields"
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
            <div class="space-y-4" v-if="cadastralGroups && cadastralGroups.length > 0">
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

                <!-- User checkboxes  -->
                <div v-for="cadastralGroup in cadastralGroups" :key="cadastralGroup.id" class="flex items-center space-x-2">
                  <input type="checkbox" :id="`cadastralGroup-${cadastralGroup.id}`" :checked="form.cadastral_group_ids.includes(cadastralGroup.id)"
                    @change="(e) => setCadastralGroupSelection(cadastralGroup.id, (e.target as HTMLInputElement).checked)"
                    :disabled="form.assign_to_all"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                  <Label :for="`cadastralGroup-${cadastralGroup.id}`" class="cursor-pointer font-normal flex-1"
                    :class="{ 'opacity-50': form.assign_to_all }">
                    {{ cadastralGroup.name }} ({{ cadastralGroup.user?.full_name }} - {{ cadastralGroup.user?.email }})
                  </Label>
                </div>
              </div>

              <p v-if="form.errors.cadastral_group_ids" class="text-sm text-destructive">
                {{ form.errors.cadastral_group_ids }}
              </p>
              <p class="text-sm text-gray-500">
                {{ trans('ui.event_assignment_help') }}
              </p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
              <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                {{ trans('ui.cancel') }}
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? trans('ui.creating') || 'Creating...' : trans('ui.create') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
