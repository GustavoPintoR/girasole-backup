<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    SidebarMenuBadge,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const unreadNotifications = page.props.auth.unreadNotifications || 0;

function getPathname(url: string): string {
    try {
        const parsed = url.startsWith('http') ? new URL(url) : new URL(url, window.location.origin);
        return parsed.pathname;
    } catch {
        return url;
    }
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarMenu>
            <Collapsible
                v-for="item in items"
                :key="item.title"
                as-child
                :default-open="item.href ? getPathname(page.url).startsWith(getPathname(item.href)) : item.items?.some(subItem => getPathname(page.url).startsWith(getPathname(subItem.href)))"
                class="group/collapsible"
            >
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton
                            :tooltip="item.title"
                            as-child
                            :is-active="item.href && getPathname(page.url).startsWith(getPathname(item.href))"
                        >
                            <Link v-if="item.href" :href="item.href">
                                <component :is="item.icon" v-if="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight
                                    v-if="item.items?.length"
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                                <SidebarMenuBadge v-if="item.href === route('notifications.index') && unreadNotifications > 0">
                                    <Badge variant="destructive">
                                        {{ unreadNotifications }}
                                    </Badge>
                                </SidebarMenuBadge>
                            </Link>
                            <div v-else>
                                <component :is="item.icon" v-if="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight
                                    v-if="item.items?.length"
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                            </div>
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent v-if="item.items?.length">
                        <SidebarMenuSub>
                            <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                <SidebarMenuSubButton
                                    as-child
                                    :is-active="getPathname(page.url).startsWith(getPathname(subItem.href))"
                                    :tooltip="subItem.title"
                                >
                                    <Link :href="subItem.href">
                                        <component :is="subItem.icon" v-if="subItem.icon" />
                                        <span>{{ subItem.title }}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
</template>
