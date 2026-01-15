<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

const sidebarNavItems: NavItem[] = [
    {
        title: trans('ui.profile'),
        href: '/settings/profile',
    },
    // {
    //     title: trans('ui.billing_info'),
    //     href: '/settings/billing/info',
    // },
    // {
    //     title: trans('ui.billing_address'),
    //     href: '/settings/billing/address',
    // },
    // {
    //     title: trans('ui.shipping_address'),
    //     href: '/settings/shipping/address',
    // },
    {
        title: trans('ui.password'),
        href: '/settings/password',
    },
    {
        title: trans('ui.appearance'),
        href: '/settings/appearance',
    },
    {
        title: trans('ui.subscription'),
        href: '/settings/subscription',
    },
];

const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading :title="trans('ui.settings')" :description="trans('ui.settings_desc')" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-58">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
