<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';
import { usePage } from '@inertiajs/vue3';

const appLogo = usePage().props.appLogo as string;
const appName = usePage().props.name as string;

const props = defineProps<{
    termsAndConditions: {
        id: number;
        version: string;
        description: string;
        description_html: string;
        summary: string;
        summary_html: string;
        is_active: boolean;
        active_at: string;
    };
}>();

const handleLogout = () => {
    router.post(route('logout'));
    router.flushAll();
};

const handleAccept = () => {
    router.post(route('terms-and-conditions.confirm-accept', { id: props.termsAndConditions.id }));
}
</script>

<template>

    <Head :title="trans('ui.terms_acceptance_header')" />

    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background relative p-6 md:p-10 bg-cover bg-center bg-fixed"
        style="background-image: url('/images/GirasoleFarm_Benvenuto_optimized.jpeg');">
        <div class="absolute inset-0 bg-black/50"></div>

        <div class="relative z-10 w-full max-w-lg lg:max-w-3xl xl:max-w-4xl">
            <Card>
                <CardHeader class="text-center">
                    <div>
                        <img :src="appLogo" class="w-16 h-16 mx-auto" :alt="appName" />
                    </div>
                    <CardTitle>{{ trans('ui.terms_acceptance_header') }}</CardTitle>
                    <CardDescription>
                        {{ trans('ui.terms_acceptance_header_desc') }}
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <article>
                        <span>{{ trans('ui.terms_acceptance_info') }}</span>

                        <div
                            class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-700 mt-4">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-sm dark:prose-invert max-w-none"
                                v-html="props.termsAndConditions.description_html">
                            </p>
                        </div>
                    </article>

                    <article>
                        <h4 class="my-4">{{ trans('ui.terms_summary') }}</h4>

                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-sm dark:prose-invert max-w-none"
                                v-html="props.termsAndConditions.summary_html">
                            </p>
                        </div>
                    </article>
                </CardContent>

                <CardFooter>
                    <div class="flex flex-col gap-4 w-full">
                        <div class="flex justify-end gap-3 items-center w-full flex-wrap">
                            <Button variant="destructive" @click="handleLogout"
                                class="cursor-pointer">{{ trans('ui.reject_and_exit') }}</Button>

                            <Button variant="default" @click="handleAccept" class="cursor-pointer">
                                {{ trans('ui.i_accept_terms') }}
                            </Button>
                        </div>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>
