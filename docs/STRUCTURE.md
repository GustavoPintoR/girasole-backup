# Project Structure Guide

This document provides a detailed explanation of the Girasole project structure, following Laravel 12's streamlined architecture.

## Root Directory Overview

```
girasole/
├── app/                    # Application core logic
├── bootstrap/              # Application bootstrap files
├── config/                 # Configuration files
├── database/               # Database-related files
├── docs/                   # Project documentation
├── public/                 # Public web assets
├── resources/              # Raw resources (views, assets, lang)
├── routes/                 # Application routes
├── storage/                # Generated files, logs, cache
├── tests/                  # Test suites
├── vendor/                 # Composer dependencies
├── artisan                 # Artisan command-line tool
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
├── vite.config.ts         # Vite build configuration
├── docker-compose.yml     # Docker development environment
└── README.md              # Project overview
```

## Core Application Structure (`app/`)

### Actions (`app/Actions/`)
Single-purpose action classes using the [Laravel Actions](https://laravelactions.com/) package.

```
Actions/
├── FetchSensors.php           # Retrieve sensor data from external sources
├── ImportAllAction.php        # Bulk data import operations
├── ImportCartographyAction.php # Import cartographic data
├── ImportCitiesAction.php     # Import city data from CSV
├── ImportCitiesPipe.php       # Pipeline for city import processing
```

**Purpose**: Encapsulate business logic in reusable, testable classes that can be used as:
- Artisan commands
- Controller actions
- Job handlers
- Event listeners

### Console Commands (`app/Console/`)
Custom Artisan commands for application management.

Laravel 12 automatically discovers commands in this directory - no manual registration required.

### Enumerations (`app/Enums/`)
Type-safe enumerations for application constants.

```php
// Example: UserRole enum
enum UserRole: string
{
    case ADMIN = 'admin';
    case FARMER = 'farmer';
    case TECHNICIAN = 'technician';
    case VIEWER = 'viewer';
}
```

### HTTP Layer (`app/Http/`)

#### Controllers (`app/Http/Controllers/`)
Handle HTTP requests and coordinate application flow.

```
Controllers/
├── Api/                          # API controllers
├── Auth/                         # Authentication controllers
├── Settings/                     # Settings management
├── Stripe/                       # Payment processing
├── CadastralGroupController.php  # Cadastral group management
├── CadastralUnitController.php   # Cadastral unit operations
├── CityController.php            # City data management
├── CompanyController.php         # Company administration
├── CultivarController.php        # Crop variety management
├── CultivationController.php     # Cultivation tracking
├── DashboardController.php       # Main dashboard
├── EventController.php           # Agricultural events
├── ImportController.php          # Data import operations
├── IrrigationController.php      # Irrigation management
├── NotificationController.php    # User notifications
├── PermissionController.php      # Permission management
├── PlantDiseaseController.php    # Disease tracking
├── PlantingSchemeController.php  # Planting schemes
├── PostalCodeController.php      # Postal code data
├── ProvinceController.php        # Province management
├── RegionController.php          # Region management
├── RoleController.php            # Role management
├── SensorController.php          # Sensor operations
├── SensorFieldController.php     # Sensor field relationships
├── SensorOperationController.php # Sensor operations
├── SensorTypeController.php      # Sensor type management
├── TermsAndConditionsController.php # Legal documents
└── UserController.php            # User management
```

### Models (`app/Models/`)
Eloquent models representing database entities.

```
Models/
├── Scopes/                    # Query scopes
├── BillingAddress.php         # Billing information
├── BillingInfo.php           # Billing details
├── CadastralGroup.php        # Cadastral groupings
├── CadastralUnit.php         # Individual cadastral units
├── City.php                  # City entities
├── Company.php               # Company/organization data
├── Cultivar.php              # Crop varieties
├── Cultivation.php           # Cultivation records
├── Event.php                 # Agricultural events
├── Irrigation.php            # Irrigation systems
├── Notification.php          # User notifications
├── Permission.php            # System permissions
├── PlantDisease.php          # Plant disease records
├── PlantingScheme.php        # Planting schemes
├── PostalCode.php            # Postal code data
├── Province.php              # Province entities
├── Region.php                # Regional data
├── Role.php                  # User roles
├── Sensor.php                # Sensor devices
├── SensorField.php           # Sensor-field relationships
├── SensorOperation.php       # Sensor operations
├── SensorType.php            # Sensor classifications
├── TermsAndConditions.php    # Legal documents
└── User.php                  # User accounts
```

**Model Features**:
- **Eloquent relationships** for data integrity
- **Casts** for data transformation (JSON, dates, etc.)
- **Mutators and Accessors** for data formatting
- **Query Scopes** for reusable query logic
- **Model Events** for automated actions

### Supporting Components

#### Interfaces (`app/Interfaces/`)
Contracts defining application behavior standards.

#### Jobs (`app/Jobs/`)
Background job classes for queue processing.

#### Notifications (`app/Notifications/`)
Email, SMS, and in-app notification classes.

#### Observers (`app/Observers/`)
Model event observers for automated actions.

#### Policies (`app/Policies/`)
Authorization policies for model access control.

#### Providers (`app/Providers/`)
Service providers for dependency injection and configuration.

#### Repositories (`app/Repositories/`)
Data access layer abstraction for complex queries.

#### Rules (`app/Rules/`)
Custom validation rules for form and API validation.

#### Services (`app/Services/`)
Business logic services for complex operations.

#### Traits (`app/Traits/`)
Reusable functionality for models and classes.

#### Transformers (`app/Transformers/`)
Data transformation classes for API responses.

## Bootstrap (`bootstrap/`)

### Laravel 12 Bootstrap Structure
```
bootstrap/
├── app.php           # Application initialization and service registration
├── providers.php     # Service provider registration
└── cache/           # Framework cache files
```

**Key Changes in Laravel 12**:
- **Streamlined configuration** in `app.php`
- **No separate kernel files** - middleware registration in `app.php`
- **Automatic service discovery** for most components

## Configuration (`config/`)

Application configuration files organized by functionality:

```
config/
├── app.php                    # Core application settings
├── auth.php                   # Authentication configuration
├── cache.php                  # Caching configuration
├── cashier.php                # Stripe subscription settings
├── database.php               # Database connections
├── filesystems.php            # File storage configuration
├── inertia.php                # Inertia.js settings
├── laravel-impersonate.php    # User impersonation
├── logging.php                # Log channel configuration
├── mail.php                   # Email configuration
├── permission.php             # Spatie permissions
├── pulse.php                  # Laravel Pulse monitoring
├── queue.php                  # Queue configuration
├── services.php               # Third-party service APIs
└── session.php                # Session configuration
```

## Database (`database/`)

### Migrations (`database/migrations/`)
Database schema definitions and modifications.

**Migration Naming Convention**:
```
YYYY_MM_DD_HHMMSS_create_table_name_table.php
YYYY_MM_DD_HHMMSS_add_column_to_table_name_table.php
```

### Seeders (`database/seeders/`)
Database seeding for initial and test data.

```
seeders/
├── DatabaseSeeder.php    # Main seeder orchestrator
├── UserSeeder.php        # User account seeding
├── RegionSeeder.php      # Geographic data seeding
└── SensorSeeder.php      # Sample sensor data
```

### Factories (`database/factories/`)
Model factories for generating test data.

```php
// Example: SensorFactory
class SensorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['temperature', 'humidity', 'soil_moisture']),
            'latitude' => $this->faker->latitude(41, 47),  // Italy bounds
            'longitude' => $this->faker->longitude(6, 19),
            'owner_id' => User::factory(),
        ];
    }
}
```

## Frontend Resources (`resources/`)

### Stylesheets (`resources/css/`)
CSS and styling files.

```
css/
└── app.css              # Main application styles (Tailwind CSS)
```

### JavaScript Application (`resources/js/`)
Vue.js application structure with TypeScript.

```
js/
├── Components/          # Reusable Vue components
│   ├── Forms/          # Form components
│   ├── Maps/           # Mapping components
│   ├── Sensors/        # Sensor-related components
│   └── UI/             # General UI components
├── Layouts/            # Application layouts
│   ├── AppLayout.vue   # Main application layout
│   ├── AuthLayout.vue  # Authentication layout
│   └── GuestLayout.vue # Guest/public layout
├── Pages/              # Inertia.js page components
│   ├── Auth/           # Authentication pages
│   ├── Dashboard/      # Dashboard pages
│   ├── Sensors/        # Sensor management pages
│   ├── Settings/       # Settings pages
│   └── Welcome.vue     # Landing page
├── Stores/             # Pinia state management
├── Types/              # TypeScript type definitions
├── Utils/              # Utility functions
├── app.ts              # Application entry point
└── bootstrap.ts        # Application initialization
```

### Views (`resources/views/`)
Blade templates (minimal usage with Inertia.js).

```
views/
├── app.blade.php       # Main application template
├── components/         # Blade components
└── emails/            # Email templates
```

### Language Files (`resources/lang/`)
Internationalization files.

```
lang/
├── en/                # English translations
│   ├── auth.php       # Authentication messages
│   ├── pagination.php # Pagination labels
│   └── validation.php # Validation messages
└── it/                # Italian translations
    ├── auth.php
    ├── pagination.php
    └── validation.php
```

## Routing (`routes/`)

```
routes/
├── web.php              # Web application routes
├── api.php              # API routes
├── auth.php             # Authentication routes
├── console.php          # Console command routes
└── settings.php         # Settings management routes
```

**Route Organization**:
- **Resource routes** for CRUD operations
- **Route groups** for middleware and prefixes
- **Route model binding** for automatic model injection
- **Route caching** for production performance

## Storage (`storage/`)

```
storage/
├── app/                 # Application files
│   ├── private/        # Private file storage
│   └── public/         # Public file storage
├── framework/          # Framework cache and sessions
│   ├── cache/          # Application cache
│   ├── sessions/       # Session files
│   └── views/          # Compiled Blade templates
├── logs/               # Application logs
└── pail/               # Pail log viewer cache
```

## Testing (`tests/`)

```
tests/
├── Feature/            # Feature tests (end-to-end)
│   ├── Auth/          # Authentication tests
│   ├── Sensor/        # Sensor functionality tests
│   └── User/          # User management tests
├── Unit/              # Unit tests (isolated)
│   ├── Actions/       # Action class tests
│   ├── Models/        # Model tests
│   └── Services/      # Service class tests
├── Pest.php           # Pest configuration
└── TestCase.php       # Base test case
```

## Build Configuration

### Vite Configuration (`vite.config.ts`)
Modern build tool configuration for frontend assets.

```typescript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import wayfinder from '@laravel/vite-plugin-wayfinder';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            'ziggy-js': '/vendor/tightenco/ziggy',
        },
    },
});
```

### Docker Configuration (`docker-compose.yml`)
Development environment setup with required services.

## File Naming Conventions

### PHP Files
- **PascalCase** for classes: `SensorController.php`
- **snake_case** for migration files: `create_sensors_table.php`
- **kebab-case** for Blade views: `sensor-dashboard.blade.php`

### JavaScript/Vue Files
- **PascalCase** for components: `SensorDashboard.vue`
- **camelCase** for utilities: `formatDate.ts`
- **kebab-case** for pages: `sensor-management.vue`

### Database
- **snake_case** for table names: `sensor_operations`
- **snake_case** for column names: `created_at`
- **PascalCase** for model names: `SensorOperation`

This structure follows Laravel 12's conventions while providing clear organization for a complex agricultural sensor management application.