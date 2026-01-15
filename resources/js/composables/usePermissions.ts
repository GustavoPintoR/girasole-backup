import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();
    const can = computed(() => page.props.auth.can);

    return { can };
}

export function isSuperAdmin(){
    const page = usePage();
    const isAdmin = computed(() => Boolean(page.props.auth?.isSuperAdmin));

    return { isAdmin };
}
