<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { trans } from 'laravel-vue-i18n';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const showPassword = ref(false)
</script>

<template>
    <AuthBase :title="trans('ui.login_to_your_account')" :description="trans('ui.enter_your_email')" :showRegisterLink="true">
      <Head title="Log in" />

      <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
          <!-- Email -->
          <div class="grid gap-2">
            <Label for="email">{{ trans('ui.email_address') }}</Label>
            <Input id="email" type="email" required autofocus :tabindex="1" autocomplete="email"
              v-model="form.email" placeholder="email@example.com" />
            <InputError :message="form.errors.email" />
          </div>

          <!-- Password with toggle -->
          <div class="grid gap-2">
            <div class="flex items-center justify-between">
              <Label for="password">{{ trans('ui.password') }}</Label>
              <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                {{ trans('ui.forgot_password') }}
              </TextLink>
            </div>

            <div class="relative">
              <Input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                required
                :tabindex="2"
                autocomplete="current-password"
                v-model="form.password"
                :placeholder="trans('ui.password')"
                class="pr-10"
              />
              <button
                type="button"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                class="absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground hover:text-foreground"
                @click="showPassword = !showPassword"
                :tabindex="6"
              >
                <Eye v-if="!showPassword" class="h-5 w-5" />
                <EyeOff v-else class="h-5 w-5" />
              </button>
            </div>

            <InputError :message="form.errors.password" />
          </div>

          <!-- Remember -->
          <div class="flex items-center justify-between">
            <Label for="remember" class="flex items-center space-x-3">
              <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
              <span>{{ trans('ui.remember_me') }}</span>
            </Label>
          </div>

          <!-- Submit -->
          <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
            {{ trans('ui.login') }}
          </Button>
        </div>
      </form>
    </AuthBase>
</template>
