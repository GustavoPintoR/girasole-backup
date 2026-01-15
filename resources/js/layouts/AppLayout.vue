<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner'
import { router } from '@inertiajs/vue3';
import 'vue-sonner/style.css'

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

interface FlashMessages {
    success: string | null
    error: string | null
    warning: string | null
    info: string | null
}


withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Watch for flash messages
router.on('success', (event) => {
    const flash = event.detail.page.props.flash as FlashMessages

    if (flash?.success) {
        toast.success(flash.success)
        flash.success = null
    }
    if (flash?.error) {
        toast.error(flash.error)
        flash.error = null
    }
    if (flash?.warning) {
        toast.warning(flash.warning)
        flash.warning = null
    }
    if (flash?.info) {
        toast.info(flash.info)
        flash.info = null
    }
})

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <Toaster position="top-right" :expand="true" :rich-colors="true" :duration="5000"/>
    </AppLayout>
</template>
