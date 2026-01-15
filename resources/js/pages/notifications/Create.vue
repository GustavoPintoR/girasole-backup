<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/InputError.vue';
import { QuillEditor } from '@vueup/vue-quill';
import 'quill/dist/quill.snow.css';
import { User } from '@/types';
import { computed, watch } from 'vue';


const breadcrumbs = [
    { title: trans('ui.notifications'), href: route('notifications.index') },
    {
        title: trans('ui.create'),
        href: route('notifications.create')
    }
];

const form = useForm({
    title: '',
    description: '',
    send_to_email: false as boolean,
    user_ids: [] as number[],
    assign_to_all: false,
});

const props = defineProps<{
    users: User[];
}>();

const selectedUsersCount = computed(() => form.user_ids.length);
const allUsersSelected = computed(() => form.user_ids.length === props.users.length);

function setUserSelection(userId: number, selected: boolean) {
    const index = form.user_ids.indexOf(userId);

    if (selected && index === -1) {
        form.user_ids.push(userId);
    } else if (!selected && index > -1) {
        form.user_ids.splice(index, 1);
    }
}

function selectAllUsers() {
    form.user_ids = props.users.map(u => u.id);
}

function deselectAllUsers() {
    form.user_ids = [];
}

function toggleSelectAll() {
    if (allUsersSelected.value) {
        deselectAllUsers();
    } else {
        selectAllUsers();
    }
}

watch(() => form.assign_to_all, (newValue) => {
    if (newValue) {
        selectAllUsers();
    }
});

function submit() {
  form.post(route('notifications.store'), {
    preserveScroll: true,
    onSuccess: () => {
      //
    },
  });
}

const handleSwitch = (value: boolean) => {
    form.send_to_email = value;
};

const cancel = () => {
  router.visit(route('notifications.index'));
};
</script>

<template>
    <Head :title="trans('ui.notification_create_header')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ trans('ui.notification_create_header') }}
                </h1>
            </div>

            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>{{ trans('ui.notification_create_details') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.notification_create_desc') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="title">
                                {{ trans('ui.title') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="title"
                                type="text"
                                required
                                v-model="form.title"
                                :placeholder="trans('ui.title')"
                                class="h-11 w-[462px]"
                                :class="{ 'border-red-500': form.errors.title }"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">{{ trans('ui.description') }} <span class="text-red-500">*</span></Label>
                            <QuillEditor
                                id="description"
                                v-model:content="form.description"
                                content-type="html"
                                class="min-h-50"
                                theme="snow"
                                :disabled="form.processing"
                                :placeholder="trans('ui.description')"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <Switch id="send_to_email" :model-value="form.send_to_email"
                                        @update:model-value="(value: any) => handleSwitch(value)"
                                        :disabled="form.processing" />
                                <Label for="is_active" class="cursor-pointer">
                                    {{ trans('ui.send_to_email') }}
                                </Label>
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ trans('ui.notification_send_email_info') }}
                            </p>
                            <InputError :message="form.errors.send_to_email" />

                        </div>


                        <!-- User Assignment Section -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <Label>{{ trans('ui.assign_to_users') }}</Label>

                                <!-- Assign to All Checkbox -->
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="assign_to_all" v-model="form.assign_to_all"
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <Label for="assign_to_all" class="cursor-pointer font-normal">
                                        {{ trans('ui.assign_to_all_users') }}
                                    </Label>
                                </div>
                            </div>

                            <!-- User Selection List -->
                            <div class="border rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                                <div class="flex items-center justify-between mb-2 pb-2 border-b">
                  <span class="text-sm text-gray-600">
                    {{ selectedUsersCount }} {{ trans('ui.users_selected') }}
                  </span>
                                    <Button type="button" variant="ghost" size="sm" @click="toggleSelectAll"
                                            :disabled="form.assign_to_all">
                                        {{ allUsersSelected ? trans('ui.deselect_all') : trans('ui.select_all') }}
                                    </Button>
                                </div>

                                <!-- User checkboxes  -->
                                <div v-for="user in users" :key="user.id" class="flex items-center space-x-2">
                                    <input type="checkbox" :id="`user-${user.id}`" :checked="form.user_ids.includes(user.id)"
                                           @change="(e) => setUserSelection(user.id, (e.target as HTMLInputElement).checked)"
                                           :disabled="form.assign_to_all"
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                                    <Label :for="`user-${user.id}`" class="cursor-pointer font-normal flex-1"
                                           :class="{ 'opacity-50': form.assign_to_all }">
                                        {{ user.name }} ({{ user.email }})
                                    </Label>
                                </div>
                            </div>

                            <p v-if="form.errors.user_ids" class="text-sm text-destructive">
                                {{ form.errors.user_ids }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ trans('ui.event_assignment_help') }}
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                                {{ trans('ui.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? trans('ui.creating') : trans('ui.create_notification') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
.ql-snow {
    margin:0;
}
</style>
