<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    FileUp,
    LayoutGrid,
    Map,
    Mail,
    LandPlot,
    UserCog,
    Calendar,
    Sprout, Flower, Grid, MapPinned, MapPin, Earth, Tractor, Shield, Key, Users, Skull,
    PaintBucket,
    Building2, Bell,
    SatelliteDish,
    Proportions, Cpu, TrafficCone, Antenna, Shapes, FingerprintIcon, CircleDollarSign,
    CloudSunRain, Database, CloudHail
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

const page = usePage();
const permissions = page.props.auth.permissions;
const navMain: NavItem[] = [
    {
        title: trans('ui.dashboard'),
        href: route('dashboard'),
        icon: LayoutGrid,
        isActive: page.url === route('dashboard'),
    },
    {
        title: trans('ui.notifications'),
        href: route('notifications.index'),
        icon: Bell,
        permission: 'read_notification',
        isActive: page.url === route('notifications.index'),
    },
    {
        title: trans('ui.calendar'),
        href: route('events.calendar'),
        icon: Calendar,
        isActive: page.url === route('events.calendar'),
    },
    {
        title: trans('ui.companies'),
        href: route('companies.index'),
        icon: Building2,
        permission: 'read_company',
        isActive: page.url === route('companies.index'),
    },
    {
        title: trans('ui.cadastral_groups'),
        href: route('cadastral-groups.index'),
        icon: Proportions,
        permission: 'read_cadastral_group',
        isActive: page.url === route('cadastral-groups.index'),
    },
    {
        title: trans('ui.administration'),
        icon: Shield,
        isActive: computed(() => [
            route('users.index'),
            route('roles.index'),
            route('permissions.index'),
        ].includes(page.url)),
        items: [
            {
                title: trans('ui.users'),
                href: route('users.index'),
                permission: 'read_user',
                icon: Users,
                isActive: page.url === route('users.index'),
            },
            {
                title: trans('ui.roles'),
                href: route('roles.index'),
                permission: 'read_role',
                icon: UserCog,
                isActive: page.url === route('roles.index'),
            },
            {
                title: trans('ui.permissions'),
                href: route('permissions.index'),
                permission: 'read_permission',
                icon: FingerprintIcon,
                isActive: page.url === route('permissions.index'),
            },
            {
                title: trans('ui.api_keys'),
                href: route('api-keys.index'),
                permission: 'read_personal_access_token',
                icon: Key,
                isActive: page.url === route('api-keys.index'),
            },
            {
                title: trans('ui.plans'),
                href: route('plans.index'),
                permission: 'read_plan',
                icon: CircleDollarSign,
                isActive: page.url === route('plans.index'),
            },
            {
                title: trans('ui.custom_fields'),
                href: route('custom-fields.index'),
                icon: Shapes,
                permission: 'read_custom_field',
                isActive: page.url === route('custom-fields.index'),
            },
        ].filter(item => !item.permission || (permissions || []).includes(item.permission)),
    },
    {
        title: trans('ui.sensor_options'),
        icon: Cpu,
        isActive: computed(() => [
            route('sensors.index'),
            route('sensor-operations.index'),
            route('sensor-types.index'),
            route('sensor-fields.index'),
        ].includes(page.url)),
        items: [
            {
                title: trans('ui.sensors'),
                href: route('sensors.index'),
                permission: 'read_sensor',
                icon: SatelliteDish,
                isActive: page.url === route('sensors.index'),
            },
            {
                title: trans('ui.sensor_operations'),
                href: route('sensor-operations.index'),
                permission: 'read_sensor_operation',
                icon: TrafficCone,
                isActive: page.url === route('sensor-operations.index'),
            },
            {
                title: trans('ui.sensor_types'),
                href: route('sensor-types.index'),
                permission: 'read_sensor_type',
                icon: Shapes,
                isActive: page.url === route('sensor-types.index'),
            },
            {
                title: trans('ui.sensor_fields'),
                href: route('sensor-fields.index'),
                permission: 'read_sensor_field',
                icon: Antenna,
                isActive: page.url === route('sensor-fields.index'),
            },
        ].filter(item => !item.permission || (permissions || []).includes(item.permission)),
    },
    {
        title: trans('ui.geographic_options'),
        icon: Earth,
        isActive: computed(() => [
            route('regions.index'),
            route('provinces.index'),
            route('cities.index'),
            route('postal-codes.index'),
        ].includes(page.url)),
        items: [
            {
                title: trans('ui.regions'),
                href: route('regions.index'),
                permission: 'read_region',
                icon: Map,
                isActive: page.url === route('regions.index'),
            },
            {
                title: trans('ui.provinces'),
                href: route('provinces.index'),
                permission: 'read_province',
                icon: MapPinned,
                isActive: page.url === route('provinces.index'),
            },
            {
                title: trans('ui.cities'),
                href: route('cities.index'),
                permission: 'read_city',
                icon: MapPin,
                isActive: page.url === route('cities.index'),
            },
            {
                title: trans('ui.postal_codes'),
                href: route('postal-codes.index'),
                permission: 'read_postal_code',
                icon: Mail,
                isActive: page.url === route('postal-codes.index'),
            },
            {
                title: trans('ui.imports'),
                href: route('imports.index'),
                icon: FileUp,
                permission: 'read_import',
                isActive: page.url === route('imports.index'),
            },
        ].filter(item => !item.permission || (permissions || []).includes(item.permission)),
    },
    {
        title: trans('ui.agricultural_options'),
        icon: Tractor,
        isActive: computed(() => [
            route('cultivations.index'),
            route('cultivars.index'),
            route('planting-schemes.index'),
            route('plant-diseases.index'),
            route('irrigations.index'),
        ].includes(page.url)),
        items: [
            {
                title: trans('ui.cultivations'),
                href: route('cultivations.index'),
                icon: Sprout,
                permission: 'read_cultivation',
                isActive: page.url === route('cultivations.index'),
            },
            {
                title: trans('ui.cultivars'),
                href: route('cultivars.index'),
                permission: 'read_cultivar',
                icon: Flower,
                isActive: page.url === route('cultivars.index'),
            },
            {
                title: trans('ui.planting_schemes'),
                href: route('planting-schemes.index'),
                permission: 'read_planting_scheme',
                icon: Grid,
                isActive: page.url === route('planting-schemes.index'),
            },
            {
                title: trans('ui.plant_diseases'),
                href: route('plant-diseases.index'),
                permission: 'read_plant_disease',
                icon: Skull,
                isActive: page.url === route('plant-diseases.index'),
            },
            {
                title: trans('ui.irrigations'),
                href: route('irrigations.index'),
                permission: 'read_irrigation',
                icon: PaintBucket,
                isActive: page.url === route('irrigations.index'),
            },
        ].filter(item => !item.permission || (permissions || []).includes(item.permission)),
    },
    {
        title: trans('ui.weather_options'),
        icon: CloudHail,
        isActive: computed(() => [
            route('forecasts.index'),
            route('forecast-logs.index'),
        ].includes(page.url)),
        items: [
            {
                title: trans('ui.weather_forecast_setup'),
                href: route('forecasts.index'),
                icon: CloudSunRain,
                permission: 'read_forecast_setup',
                isActive: page.url === route('forecasts.index'),
            },
            {
                title: trans('ui.forecast_logs'),
                href: route('forecast-logs.index'),
                icon: Database,
                permission: 'read_forecast_log',
                isActive: page.url === route('forecast-logs.index'),
            },
        ].filter(item => !item.permission || (permissions || []).includes(item.permission)),
    },
    {
        title: trans('ui.land_registry'),
        href: route('cadastral-units.index'),
        icon: LandPlot,
        permission: 'read_cadastral_unit',
        isActive: page.url === route('cadastral-units.index'),
    },
    {
        title: trans('ui.events'),
        href: route('events.index'),
        icon: Mail,
        permission: 'read_event',
        isActive: page.url === route('events.index'),
    },
    {
        title: trans('ui.terms_and_conditions'),
        href: route('terms-and-conditions.index'),
        icon: BookOpen,
        permission: 'read_terms_and_conditions',
        isActive: page.url === route('terms-and-conditions.index'),
    },
].filter(item => !item.permission || (permissions || []).includes(item.permission)).filter(item => !item.items || item.items.length > 0);

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')" class="px-0 py-0 rounded-b-none">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="navMain" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
