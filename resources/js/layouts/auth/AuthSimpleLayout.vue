<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

defineProps<{
    title?: string;
    description?: string;
    showRegisterLink?: boolean;
    showLogoutLink?: boolean;
}>();

const form = useForm({});
const submit = () => {
    form.post(route('logout'));
};
</script>

<template>
    <div
    class="flex flex-col min-h-svh items-center justify-center gap-6 bg-background relative p-6 md:p-10 bg-cover bg-center bg-fixed"
    style="background-image: url('/images/GirasoleFarm_Benvenuto_optimized.jpeg');"
  >

    <div class="items-end justify-end">
    <div class="flex items-center mb-5" v-if="showRegisterLink">
      <Link
        :href="route('register')"
        class="ml-auto rounded-sm bg-primary/90 hover:bg-primary text-white dark:bg-black/50 dark:hover:bg-black/60 dark:text-[#EDEDEC] border border-transparent px-5 py-1.5 text-sm leading-normal"
      >
        {{ trans('ui.do_you_have_an_account') }}
      </Link>
    </div>

        <div class="flex items-center mb-5" v-if="showLogoutLink">
            <Link @click.prevent="submit"
                class="ml-auto rounded-sm bg-primary/90 hover:bg-primary text-white dark:bg-black/50 dark:hover:bg-black/60 dark:text-[#EDEDEC] border border-transparent px-5 py-1.5 text-sm leading-normal"
            >
                {{ trans('ui.logout') }}
            </Link>
        </div>

    <div class="flex flex-col items-center justify-center gap-6 bg-background p-6 md:p-10 rounded-sm">
        <div class="w-full">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <div class="mb-1 flex h-12 w-full items-center justify-center rounded-md">
                            <AppLogoIcon class="size-9 fill-current text-[var(--foreground)] dark:text-white" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</div>

    </div>
</template>
