# Development Commands Reference

This comprehensive reference covers all available commands for Girasole development, organized by category for easy navigation.

## Quick Start Commands

```bash
# Start complete development environment
composer run dev

# Run all tests
php artisan test

# Build frontend for production
npm run build

# Clear all caches
php artisan optimize:clear
```

## Composer Scripts

### Development Environment
```bash
composer run dev        # Start complete development stack (server, queue, logs, vite)
composer run dev:ssr    # Start with Server-Side Rendering support
composer run test       # Run the complete test suite
```

**What `composer run dev` includes:**
- **PHP development server** (`php artisan serve`)
- **Queue worker** (`php artisan queue:listen`)
- **Log monitoring** (`php artisan pail`)
- **Vite development server** (`npm run dev`)

All services run concurrently with colored output for easy identification.

## Core Laravel Commands

### Application Management
```bash
php artisan serve                    # Start development server
php artisan serve --port=8001       # Start on custom port
php artisan down                     # Put app in maintenance mode
php artisan up                       # Bring app out of maintenance mode
php artisan about                    # Display application information
php artisan env                      # Show current environment
```

### Database Operations
```bash
# Migrations
php artisan migrate                  # Run pending migrations
php artisan migrate --seed           # Run migrations with seeders
php artisan migrate:fresh --seed     # Fresh migration with seed data
php artisan migrate:status           # Check migration status
php artisan migrate:rollback         # Rollback last migration
php artisan migrate:reset            # Rollback all migrations
php artisan migrate:refresh --seed   # Reset and re-run all migrations

# Database Inspection
php artisan db:show                  # Display database information
php artisan db:table users          # Show specific table structure
php artisan db:monitor               # Monitor database connections
php artisan db:wipe                  # Drop all tables, views, and types

# Seeders
php artisan db:seed                  # Run database seeders
php artisan db:seed --class=UserSeeder  # Run specific seeder
```

### Cache Management
```bash
# Application Caches
php artisan cache:clear              # Clear application cache
php artisan cache:forget key         # Remove specific cache key
php artisan config:cache             # Cache configuration files
php artisan config:clear             # Clear configuration cache

# Route Caching
php artisan route:cache              # Cache routes for performance
php artisan route:clear              # Clear route cache
php artisan route:list               # Display all registered routes

# View Caching
php artisan view:cache               # Cache Blade templates
php artisan view:clear               # Clear compiled view files

# Comprehensive Optimization
php artisan optimize                 # Cache bootstrap files
php artisan optimize:clear           # Clear all cached files
```

### Queue Management
```bash
# Workers
php artisan queue:work               # Start queue worker
php artisan queue:listen             # Listen to queue (development)
php artisan queue:work --queue=high,default  # Process specific queues
php artisan queue:restart            # Restart queue workers

# Queue Inspection
php artisan queue:failed             # List failed jobs
php artisan queue:retry all          # Retry all failed jobs
php artisan queue:retry 5            # Retry specific job ID
php artisan queue:forget 5           # Delete specific failed job
php artisan queue:flush              # Delete all failed jobs
php artisan queue:clear              # Delete all jobs from queue
php artisan queue:monitor            # Monitor queue sizes
```

## Girasole-Specific Commands

### Sensor Management
```bash
php artisan girasole:fetch-sensors   # Retrieve sensor data from external sources
php artisan girasole:temperature     # Generate random temperature data (testing)
```

### Geographic Data Import
```bash
# Import All Data
php artisan import:all               # Import all geographic data sequentially

# Individual Imports (run in order)
php artisan import:regions           # Import Italian regions
php artisan import:provinces         # Import Italian provinces  
php artisan import:cities            # Import Italian cities
php artisan import:postal-codes      # Import postal code data
```

### Cartography Operations
```bash
php artisan cartography:download     # Download cartographic data files
php artisan cartography:polygon      # Extract polygon data for specific regions
```

Example usage:
```bash
# Download cartography for a specific region
php artisan cartography:download --region=lombardia

# Extract polygon for specific cadastral data
php artisan cartography:polygon --region=01 --province=001 --city=001 --sheet=A --parcel=123
```

### Email Testing
```bash
php artisan girasole:mailer:test                    # Test with default settings
php artisan girasole:mailer:test user@example.com   # Test with specific email
```

## Code Generation Commands

### Model Creation
```bash
php artisan make:model Sensor                    # Model only
php artisan make:model Sensor -m                 # Model + Migration
php artisan make:model Sensor -mfs               # Model + Migration + Factory + Seeder
php artisan make:model Sensor -a                 # Model with all related files
php artisan make:model Sensor --pivot            # Pivot model
```

### Controller Creation
```bash
php artisan make:controller SensorController                    # Basic controller
php artisan make:controller SensorController --resource         # Resource controller
php artisan make:controller SensorController --api              # API resource controller
php artisan make:controller SensorController --model=Sensor     # Controller with model binding
```

### Database Files
```bash
php artisan make:migration create_sensors_table          # Create migration
php artisan make:migration add_status_to_sensors_table   # Modify table migration
php artisan make:seeder SensorSeeder                     # Database seeder
php artisan make:factory SensorFactory                   # Model factory
```

### Other Components
```bash
# Actions (Lorisleiva package)
php artisan make:action ProcessSensorData

# Livewire Components
php artisan make:livewire SensorDashboard
php artisan make:livewire Sensors.CreateSensor

# Form Requests
php artisan make:request StoreSensorRequest

# Resources (API)
php artisan make:resource SensorResource
php artisan make:resource SensorCollection

# Jobs & Events
php artisan make:job ProcessSensorData
php artisan make:event SensorDataReceived
php artisan make:listener NotifySensorAlert

# Middleware & Policies
php artisan make:middleware CheckSensorAccess
php artisan make:policy SensorPolicy

# Tests
php artisan make:test SensorTest                    # Feature test
php artisan make:test SensorTest --unit             # Unit test
php artisan pest:test SensorCalculationTest         # Pest-specific test
```

## Frontend Development

### NPM Scripts
```bash
# Development
npm run dev                          # Start Vite development server with HMR
npm run build                        # Build production assets
npm run build:ssr                    # Build with Server-Side Rendering

# Code Quality
npm run lint                         # ESLint with auto-fix
npm run format                       # Prettier code formatting
npm run format:check                 # Check formatting without changes
```

### TypeScript Generation
```bash
# Generate TypeScript definitions
php artisan wayfinder:generate                           # Generate all definitions
php artisan wayfinder:generate --path=resources/js/types # Custom output path
php artisan wayfinder:generate --skip-actions            # Skip controller methods
php artisan wayfinder:generate --skip-routes             # Skip route definitions

# Ziggy route definitions
php artisan ziggy:generate                               # Generate Ziggy routes
```

## Docker Commands

### Container Management
```bash
# Basic Operations
docker-compose up -d                 # Start all services in background
docker-compose down                  # Stop all services
docker-compose down -v               # Stop and remove volumes
docker-compose up -d --build         # Rebuild and start services

# Service Management
docker-compose restart app           # Restart specific service
docker-compose logs -f app           # Follow application logs
docker-compose ps                    # Show running containers
docker-compose exec app bash         # Access container shell
```

### Running Commands in Containers
```bash
# PHP Commands
docker-compose run --rm cli php artisan migrate
docker-compose run --rm cli php artisan test
docker-compose run --rm cli composer install --no-dev --optimize-autoloader

# Node Commands
docker-compose run --rm cli npm install
docker-compose run --rm cli npm run build

# Database Operations
docker-compose run --rm cli php artisan db:seed
```

## Monitoring & Debugging

### Performance Monitoring
```bash
# Laravel Pulse
php artisan pulse:check              # Take performance snapshot
php artisan pulse:work               # Process Pulse data stream
php artisan pulse:clear              # Clear all Pulse data
php artisan pulse:restart            # Restart Pulse workers
```

### Logging & Debugging
```bash
# Real-time Logs
php artisan pail                     # Interactive log viewer
php artisan pail --timeout=0         # Continuous monitoring
php artisan pail --filter=error      # Filter by log level

# Application Debugging
php artisan tinker                   # Interactive shell
php artisan inspire                  # Get inspired!
```

### Schedule Management
```bash
php artisan schedule:list            # List all scheduled tasks
php artisan schedule:run             # Run scheduled commands
php artisan schedule:work            # Keep schedule worker running
php artisan schedule:test            # Test specific scheduled command
```

## Testing Commands

### Running Tests
```bash
# Basic Test Execution
php artisan test                     # Run full test suite
vendor/bin/pest                     # Direct Pest execution
php artisan test --parallel         # Run tests in parallel

# Filtered Testing
php artisan test --filter=UserTest                    # Run specific test class
php artisan test --filter=test_user_can_login        # Run specific test method
php artisan test tests/Feature/UserTest.php          # Run specific file
php artisan test --group=integration                 # Run test group

# Test Options
php artisan test --coverage         # Generate coverage report
php artisan test --coverage-html=coverage  # HTML coverage report
php artisan test --stop-on-failure  # Stop on first failure
```

### Test Data Management
```bash
# Database for Testing
php artisan migrate:fresh --env=testing     # Fresh test database
php artisan db:seed --env=testing           # Seed test database
```

## Code Quality Commands

### PHP Code Formatting
```bash
vendor/bin/pint                      # Fix code style issues
vendor/bin/pint --dirty              # Only check modified files
vendor/bin/pint --test               # Check without fixing
vendor/bin/pint --config=pint.json   # Use custom config
```

### Package Management
```bash
# Composer
composer install                     # Install PHP dependencies
composer update                      # Update dependencies
composer dump-autoload               # Regenerate autoloader
composer audit                       # Check for security issues

# NPM
npm install                          # Install Node dependencies
npm audit                            # Check for vulnerabilities
npm audit fix                        # Fix security issues
npm outdated                         # Check for outdated packages
```

## Environment & Configuration

### Environment Management
```bash
php artisan env:encrypt --key=KEY    # Encrypt environment file
php artisan env:decrypt --key=KEY    # Decrypt environment file
php artisan key:generate             # Generate application key
```

### Configuration
```bash
php artisan config:show app          # Show app configuration
php artisan config:show database     # Show database configuration
php artisan config:publish           # Publish config files
```

## Advanced Commands

### Storage & Links
```bash
php artisan storage:link             # Create storage symbolic links
php artisan storage:unlink           # Remove storage links
```

### Package Discovery
```bash
php artisan package:discover         # Rebuild package manifest
php artisan vendor:publish           # Publish vendor assets
php artisan vendor:publish --tag=config  # Publish specific assets
```

### Model Operations
```bash
php artisan model:show User          # Show model information
php artisan model:prune              # Prune old model records
```

## Custom Command Examples

### Creating Custom Commands
```bash
php artisan make:command ProcessSensorData
```

Example custom command structure:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessSensorData extends Command
{
    protected $signature = 'sensors:process {--type=all : Sensor type to process}';
    protected $description = 'Process sensor data';

    public function handle()
    {
        $type = $this->option('type');
        $this->info("Processing {$type} sensors...");
        
        // Command logic here
        
        $this->info('Sensor processing completed!');
    }
}
```

## Performance Tips

### Development Performance
```bash
# Cache everything for better performance
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear when making changes
php artisan optimize:clear
```

### Database Performance
```bash
# Monitor slow queries
php artisan db:monitor

# Check database size
php artisan db:show --counts
```

This command reference provides comprehensive coverage of all development operations you'll need when working with Girasole.