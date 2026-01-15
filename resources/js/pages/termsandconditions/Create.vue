<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { CalendarIcon, AlertCircle } from 'lucide-vue-next';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import { ref, computed } from 'vue';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import { trans } from 'laravel-vue-i18n';
import WysiwygEditor from '@/components/ui/WysiwygEditor.vue';


const breadcrumbs = [
    {
        title: trans('ui.terms_and_conditions'),
        href: route('terms-and-conditions.index'),
    },
    {
        title: trans('ui.create'),
        href: route('terms-and-conditions.create'),
    },
];

const calendarDate = ref<CalendarDate>();

const form = useForm({
    version: '',
    description: '',
    summary: '',
    is_active: false as boolean,
    active_at: null as string | null,
});

const handleDateSelect = (date: CalendarDate | undefined) => {
    if (date) {
        calendarDate.value = date;
        const jsDate = date.toDate(getLocalTimeZone());
        form.active_at = format(jsDate, 'yyyy-MM-dd');
    } else {
        calendarDate.value = undefined;
        form.active_at = null;
    }
};

const handleActiveStatus = (value: boolean) => {
    form.is_active = value;
    if (value) {
        // default to today
        const active_at = today(getLocalTimeZone()).toDate(getLocalTimeZone());
        form.active_at = format(active_at, 'yyyy-MM-dd');
    } else {
        form.active_at = null;
        calendarDate.value = undefined;
    }
};

const submit = () => {
    form.post(route('terms-and-conditions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            //
        },
    });
};

const cancel = () => {
    router.visit(route('terms-and-conditions.index'));
};

const formattedDate = computed(() => {
    if (calendarDate.value) {
        const jsDate = calendarDate.value.toDate(getLocalTimeZone());
        return format(jsDate, 'PPP');
    }
    return null;
});
</script>

<template>

    <Head :title="trans('ui.terms_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.terms_create_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.terms_create_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.terms_create_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form  class="space-y-6">
                        <!-- Version -->
                        <div class="space-y-2">
                            <Label for="version">
                                {{ trans('ui.version') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input id="version" v-model="form.version" type="text" placeholder="e.g., 1.0.0"
                                :class="{ 'border-red-500': form.errors.version }" :disabled="form.processing" />
                            <p v-if="form.errors.version" class="text-sm text-red-500">
                                {{ form.errors.version }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">
                                {{ trans('ui.full_description') }}<span class="text-red-500">*</span>
                            </Label>
                            <!-- <Textarea id="description" v-model="form.description"
                                :placeholder="trans('ui.terms_description_placeholder')" :rows="10"
                                :class="{ 'border-red-500': form.errors.description }" :disabled="form.processing" /> -->
                                <!-- <WysiwygEditor v-model="form.description" /> -->
                                 <WysiwygEditor
                                    v-model="form.description"
                                    :disabled="form.processing"
                                    :placeholder="trans('ui.terms_description_placeholder')"
                                    />
                            <p v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Summary -->
                        <div class="space-y-2">
                            <Label for="summary">
                                {{ trans('ui.terms_summary') }} <span class="text-red-500">*</span>
                            </Label>
                            <Textarea id="summary" v-model="form.summary"
                                :placeholder="trans('ui.terms_summary_placeholder')" :rows="3"
                                :class="{ 'border-red-500': form.errors.summary }" :disabled="form.processing" />
                            <p class="text-sm text-gray-500">
                                {{ trans('ui.max_thousand_chars') }} ({{ form.summary.length }}/1000)
                            </p>
                            <p v-if="form.errors.summary" class="text-sm text-red-500">
                                {{ form.errors.summary }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <Switch id="is_active" :model-value="form.is_active"
                                    @update:model-value="(value: any) => handleActiveStatus(value)"
                                    :disabled="form.processing" />
                                <Label for="is_active" class="cursor-pointer">
                                    {{ trans('ui.set_as_active_version') }}
                                </Label>
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ trans('ui.terms_active_status_info') }}
                            </p>
                            <p v-if="form.errors.is_active" class="text-sm text-red-500">
                                {{ form.errors.is_active }}
                            </p>
                        </div>

                        <!-- Active Date -->
                        <div v-if="form.is_active" class="space-y-2">
                            <Label for="active_at">
                                {{ trans('ui.active_date') }}
                            </Label>
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button variant="outline" :class="cn(
                                        'w-full justify-start text-left font-normal',
                                        !calendarDate && 'text-muted-foreground',
                                        form.errors.active_at && 'border-red-500'
                                    )" :disabled="form.processing">
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ formattedDate || trans('ui.terms_pick_a_date') }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar :model-value="calendarDate"
                                        @update:model-value="(date: any) => handleDateSelect(date)" />
                                </PopoverContent>
                            </Popover>
                            <p v-if="form.errors.active_at" class="text-sm text-red-500">
                                {{ form.errors.active_at }}
                            </p>
                        </div>

                        <!-- Warning -->
                        <Alert v-if="form.is_active"
                            class="border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20">
                            <AlertCircle class="h-4 w-4 text-yellow-600" />
                            <AlertDescription class="text-yellow-800 dark:text-yellow-200">
                                {{ trans('ui.terms_active_warning') }}
                            </AlertDescription>
                        </Alert>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing" @click="submit">
                                {{ form.processing ? trans('ui.terms_creating') : trans('ui.create_terms') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
