import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    permissions: string[];
    can: Record<string, boolean>;
    canImpersonate: boolean;
    isImpersonating: boolean;
    isSuperAdmin: boolean;
    unreadNotifications: number;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    permission?: string;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    breadcrumbs: BreadcrumbItem[];
    mapBox: { accessToken: string };
};

export interface Permission {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
}

export interface Notification {
    id: string;
    type: string;
    notifiable_type: string;
    notifiable_id: number;
    data: string | {
        created_by: number | string | null;
        context: {
            title: string;
            description: string;
            send_to_email: boolean;
        };
    };
    user: User;
    read_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface PlantingScheme {
    id: number;
    name: string;
    description?: string;
    plant_spacing: number;
    distance: number;
    row_spacing: number;
    pattern: string;
    created_at: string;
    updated_at: string;
}

export interface Cultivation {
    id: number;
    name: string;
    description?: string;
    created_at: string;
    updated_at: string;
}

export interface PlantDisease {
    id: number;
    name: string;
    description?: string;
    cultivations?: Cultivation[];
    created_at: string;
    updated_at: string;
}

export interface SensorType {
    id: number;
    name: string;
    description?: string;
    sensor_operations?: SensorOperation[];
    created_at: string;
    updated_at: string;
}

export interface SensorOperation {
    id: number;
    name?: string;
    label: string;
    created_at: string;
    updated_at: string;
}

export interface SensorField {
    id: number;
    name?: string;
    label: string;
    unit?: string;
    created_at: string;
    updated_at: string;
}

export interface Irrigation {
    id: number;
    type: string;
    description?: string;
    created_at: string;
    updated_at: string;
}

export interface Company {
    id: number;
    name: string;
    description?: string;
    owner_id?: number | null;
    owner?: User | null;
    users?: User[] | null;
    assign_to_all?: boolean;
    billing_info?: BillingInfo | null;
    billing_address?: BillingAddress | null;
    shipping_address?: ShippingAddress | null;
    is_main?: boolean;
    created_at: string;
    updated_at: string;
}
export interface Role {
    id: number;
    name: string;
    permissions: Permission[];
    created_at: string;
    updated_at: string;
}

export interface BillingInfo {
    fiscal_type?: string;
    fiscal_code?: string;
    vat_number?: string;
    sdi?: string;
    business_name?: string;
}

export interface Region {
    id: number;
    name: string;
}

export interface Province {
    id: number;
    name: string;
    code: string;
    region_id: number;
}

export interface City {
    id: number;
    name: string;
    cadastral_code: string;
    province_id: number;
}

export interface PostalCode {
    id: number;
    code: string;
    city_id: number;
}

export interface BillingAddress {
    street?: string;
    street_number?: string;
    city_id?: number;
    city?: City;
    province_id?: number;
    province?: Province;
    region_id?: number;
    region?: Region;
    postal_code?: PostalCode;
    state?: string;
    country_code?: string;
    mobile_number?: string;
}

export interface ShippingAddress {
    address?: string;
    street?: string;
    name?: string;
    phone_number?: string;
    mobile_number?: string;
    same_as_billing?: boolean;
    postal_code?: PostalCode | null;
    city_id?: number | null;
    city?: City;
    province_id?: number | null;
    province?: Province;
    region_id?: number | null;
    region?: Region;
}

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string,
    mobile_number: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    is_admin: boolean;
    isSuperAdmin: boolean;
    billing_info: {
        fiscal_type?: string;
        fiscal_code?: string;
        vat_number?: string;
        sdi?: string;
        business_name?: string;
    };
}

export interface CadastralUnit {
    id: number;
    sheet: string;
    parcel: string;
    cadastral_area: number;
    selected?: boolean;
    city: {
        name: string;
        province: string;
        province_code?: string;
        region: string;
    };
    geometry_json: string | null;
    centroid_json: string | null;
}

export interface CadastralGroup {
    id: number;
    name: string;
    description?: string;
    total_area: number;
    units_count: number;
    color: string;
    creation_method: 'units' | 'manual' | 'import';
    original_geojson?: string | null;
    boundary_geometry_json?: string;
    centroid_json?: string;
    latitude?: number;
    longitude?: number;
    created_at: string;
    updated_at: string;
    cadastral_units: CadastralUnit[];
    user?: User | null;
    company?: Company | null;
}

export type ForecastStatus = 'pending' | 'success' | 'failure';

export interface ForecastLog {
    id: number;
    field_id: number;
    field?: CadastralGroup;
    status: ForecastStatus;
    data: Record<string, any> | null;
    ran_at: string | null;
    parameters: Record<string, any> | null;
    created_at: string;
    updated_at: string;
}

export interface Sensor {
    id: number;
    urn: string;
    name: string;
    type: string;
    serial_number?: string;
    iccid: string;
    transmission_module_identification: string;
    firmware: string;
    description: string;
    latitude: string;
    longitude: string;
    owner?: User | null;
    sensor_type?: SensorType | null;
    cadastral_group?: CadastralGroup | null;
    metadata?: Record<string, any>;
}

export interface MarkerData {
    id: number;
    coordinates: [number, number];
    popupContent: {
        name?: string;
        type: string;
        serial?: string;
        description: string;
        latitude: number;
        longitude: number;
    };
}
export interface PersonalAccessToken {
    id?: number;
    name: string;
    token: string;
    abilities: string[];
    last_used_at?: string | null;
    expires_at?: string | null;
    tokenable_id?: number;
    tokenable_type?: string;
    created_at?: string;
    updated_at?: string;
    user: User;

    can(ability: string): boolean;
    cant(ability: string): boolean;
}

export interface Plan {
    id: number;
    name: string;
    description?: string;
    features: string[];
    currency: string;   // e.g., 'EUR'
    unit_amount: number; // integer cents
    stripe_price_id: string; // Stripe Price ID
    interval: 'month' | 'year' | 'day' | 'week' | 'custom';
    active: boolean;
    cadastral_units_number?: null
    field_groups_number?:   null
    field_groups_max_area?: number | null
    hide_plan: boolean
};

interface CustomField {
  id: number;
  key: string;
  label: string;
  type: string;
  unit?: string;
  is_required: boolean;
  description?: string;
  options?: string[];
  model_type?: string;
  order?: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
