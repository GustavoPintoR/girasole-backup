<script setup lang="ts">

import { trans } from 'laravel-vue-i18n';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button"
import { LogOut } from "lucide-vue-next"
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
} from "@/components/ui/card"
const form = useForm({});

const submit = () => {
    form.post(route('logout'));
};
</script>

<template>
    <AuthBase :title="trans('ui.account_waiting')" :description="trans('ui.account_waiting_description')">
        <Head :title="trans('ui.waiting')" />

        <Card>
            <CardHeader>
                <CardDescription>{{ trans('ui.account_waiting_text') }}</CardDescription>
            </CardHeader>
            <CardContent class="grid gap-4">
                <div class="flex items-center space-x-4 rounded-md p-4">
                    <Button @click.prevent="submit" class="w-full">
                        <LogOut class="mr-2 h-4 w-4" /> {{ trans('ui.logout') }}
                    </Button>
                </div>
            </CardContent>
            <CardFooter>
                <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
                    {{ trans('ui.have_questions') }}
                    <a href="mailto:support@girasole.farm" class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                        {{ trans('ui.contact_us') }}
                    </a>
                </div>
            </CardFooter>
        </Card>
    </AuthBase>
</template>

<style scoped>

</style>
