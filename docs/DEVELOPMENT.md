# Development Guidelines

This document outlines the development standards, best practices, and workflows for contributing to the Girasole project.

## Code Standards

### PHP Code Standards

**PSR-12 Compliance**
- Follow PSR-12 coding standard for all PHP code
- Use Laravel Pint for automatic code formatting
- Run `vendor/bin/pint` before committing changes

**Type Declarations**
```php
// Always use explicit return type declarations
public function calculateDistance(Sensor $sensor1, Sensor $sensor2): float
{
    // Implementation
}

// Use type hints for parameters
public function createSensor(array $data, User $owner): Sensor
{
    // Implementation
}
```

**Constructor Property Promotion**
```php
// Use PHP 8+ constructor property promotion
class SensorService
{
    public function __construct(
        private SensorRepository $repository,
        private NotificationService $notifications,
    ) {}
}
```

**Enums for Constants**
```php
// Use enums instead of class constants
enum SensorType: string
{
    case TEMPERATURE = 'temperature';
    case HUMIDITY = 'humidity';
    case SOIL_MOISTURE = 'soil_moisture';
}
```

### Laravel Best Practices

**Eloquent Relationships**
```php
// Always use proper return type hints
public function sensors(): HasMany
{
    return $this->hasMany(Sensor::class);
}

// Use eager loading to prevent N+1 queries
$users = User::with(['sensors', 'company'])->get();
```

**Form Request Validation**
```php
// Create dedicated Form Request classes
class StoreSensorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(SensorType::class)],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
```

**Model Casts**
```php
// Use casts() method in Laravel 12
protected function casts(): array
{
    return [
        'metadata' => 'array',
        'coordinates' => Point::class,
        'status' => SensorStatus::class,
        'last_reading_at' => 'datetime',
    ];
}
```

**Action Classes**
```php
// Use Laravel Actions for reusable business logic
class ProcessSensorData extends Action
{
    public function handle(Sensor $sensor, array $data): SensorReading
    {
        // Validate data
        // Process sensor reading
        // Store in database
        // Trigger alerts if needed
        
        return $reading;
    }
}
```

### Frontend Code Standards

**TypeScript Usage**
```typescript
// Define interfaces for data structures
interface Sensor {
    id: number;
    name: string;
    type: SensorType;
    coordinates: {
        latitude: number;
        longitude: number;
    };
    last_reading?: SensorReading;
}

// Use type assertions carefully
const sensor = response.data as Sensor;
```

**Vue 3 Composition API**
```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Props with TypeScript
interface Props {
    sensors: Sensor[];
    filters?: FilterOptions;
}

const props = defineProps<Props>();

// Reactive data
const isLoading = ref(false);
const selectedSensor = ref<Sensor | null>(null);

// Computed properties
const activeSensors = computed(() => 
    props.sensors.filter(sensor => sensor.status === 'active')
);

// Lifecycle hooks
onMounted(async () => {
    await loadSensorData();
});
</script>
```

**Component Organization**
```vue
<template>
    <!-- Template content -->
</template>

<script setup lang="ts">
// Imports
// Interfaces
// Props/Emits
// Reactive data
// Computed properties
// Methods
// Lifecycle hooks
</script>

<style scoped>
/* Component-specific styles */
</style>
```

## Testing Standards

### Feature Tests with Pest

```php
use App\Models\{User, Sensor, Company};

it('allows company admin to create sensors', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->companyAdmin($company)->create();
    
    $response = $this->actingAs($admin)
        ->post('/sensors', [
            'name' => 'Temperature Sensor #1',
            'type' => 'temperature',
            'latitude' => 45.123456,
            'longitude' => 12.654321,
        ]);
    
    $response->assertSuccessful();
    expect(Sensor::count())->toBe(1);
    expect(Sensor::first())
        ->name->toBe('Temperature Sensor #1')
        ->type->toBe('temperature')
        ->company_id->toBe($company->id);
});

it('prevents unauthorized users from creating sensors', function () {
    $user = User::factory()->viewer()->create();
    
    $response = $this->actingAs($user)
        ->post('/sensors', [
            'name' => 'Temperature Sensor #1',
            'type' => 'temperature',
            'latitude' => 45.123456,
            'longitude' => 12.654321,
        ]);
    
    $response->assertForbidden();
    expect(Sensor::count())->toBe(0);
});
```

### Unit Tests

```php
use App\Services\SensorDataProcessor;
use App\Models\{Sensor, SensorReading};

it('processes valid sensor data correctly', function () {
    $sensor = Sensor::factory()->create();
    $processor = new SensorDataProcessor();
    
    $data = [
        'temperature' => 25.5,
        'humidity' => 60.0,
        'timestamp' => now()->toISOString(),
    ];
    
    $reading = $processor->process($sensor, $data);
    
    expect($reading)
        ->toBeInstanceOf(SensorReading::class)
        ->sensor_id->toBe($sensor->id)
        ->temperature->toBe(25.5)
        ->humidity->toBe(60.0);
});
```

### Frontend Testing

```typescript
// Component testing example
import { mount } from '@vue/test-utils';
import SensorCard from '@/Components/Sensors/SensorCard.vue';

describe('SensorCard', () => {
    it('displays sensor information correctly', () => {
        const sensor = {
            id: 1,
            name: 'Test Sensor',
            type: 'temperature',
            status: 'active',
            last_reading: {
                value: 25.5,
                unit: '°C',
                timestamp: '2023-01-01T12:00:00Z'
            }
        };

        const wrapper = mount(SensorCard, {
            props: { sensor }
        });

        expect(wrapper.text()).toContain('Test Sensor');
        expect(wrapper.text()).toContain('25.5°C');
        expect(wrapper.find('[data-testid="sensor-status"]').text()).toBe('active');
    });
});
```

## Git Workflow

### Branch Naming Convention

```bash
# Feature branches
feature/sensor-alert-system
feature/advanced-mapping

# Bug fixes
fix/sensor-data-validation
fix/dashboard-loading-issue

# Hotfixes
hotfix/critical-security-update

# Chores
chore/update-dependencies
chore/refactor-sensor-service
```

### Commit Message Format

```bash
# Format: type(scope): description
feat(sensors): add real-time data streaming
fix(dashboard): resolve loading spinner issue
docs(api): update sensor endpoint documentation
test(sensors): add unit tests for data processor
refactor(auth): simplify user role checking
style(frontend): apply consistent spacing
chore(deps): update Laravel to 12.1
```

### Pull Request Process

1. **Create Feature Branch**
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/your-feature-name
   ```

2. **Development Process**
   ```bash
   # Make changes
   # Run tests
   php artisan test
   npm run lint
   
   # Format code
   vendor/bin/pint
   npm run format
   
   # Commit changes
   git add .
   git commit -m "feat(scope): description"
   ```

3. **Pre-PR Checklist**
   - [ ] All tests pass
   - [ ] Code follows style guidelines
   - [ ] Documentation updated if needed
   - [ ] No console errors or warnings
   - [ ] Database migrations tested
   - [ ] Environment variables documented

4. **Create Pull Request**
   - Clear description of changes
   - Link to related issues
   - Screenshots for UI changes
   - Migration and deployment notes

## Database Guidelines

### Migration Best Practices

```php
// Always make migrations reversible
public function up(): void
{
    Schema::create('sensor_readings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sensor_id')->constrained()->cascadeOnDelete();
        $table->decimal('value', 10, 4);
        $table->string('unit', 10);
        $table->timestamp('recorded_at');
        $table->timestamps();
        
        $table->index(['sensor_id', 'recorded_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('sensor_readings');
}
```

### Model Relationships

```php
// Define clear, typed relationships
class Sensor extends Model
{
    // One-to-many
    public function readings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }
    
    // Many-to-one
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    
    // Many-to-many
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(CadastralUnit::class, 'sensor_fields')
            ->withPivot(['installed_at', 'notes'])
            ->withTimestamps();
    }
}
```

## Performance Guidelines

### Database Performance

```php
// Use eager loading to prevent N+1 queries
$sensors = Sensor::with(['owner', 'readings' => function ($query) {
    $query->latest()->limit(1);
}])->get();

// Use database indexes for frequently queried columns
Schema::table('sensors', function (Blueprint $table) {
    $table->index(['status', 'type']);
    $table->index(['latitude', 'longitude']);
});

// Use database-level constraints
Schema::table('sensor_readings', function (Blueprint $table) {
    $table->check('value >= -50 AND value <= 100', 'temperature_range');
});
```

### Frontend Performance

```typescript
// Use computed properties for derived data
const averageTemperature = computed(() => {
    return sensors.value.reduce((sum, sensor) => 
        sum + (sensor.last_reading?.temperature || 0), 0
    ) / sensors.value.length;
});

// Lazy load components
const SensorChart = defineAsyncComponent(() => 
    import('@/Components/Charts/SensorChart.vue')
);

// Use v-memo for expensive renders
<template>
    <div v-memo="[sensor.id, sensor.last_reading?.timestamp]">
        <SensorCard :sensor="sensor" />
    </div>
</template>
```

## Security Guidelines

### Input Validation

```php
// Always validate input data
class StoreSensorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-_]+$/'],
            'type' => ['required', Rule::enum(SensorType::class)],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'metadata' => ['sometimes', 'array', 'max:10'],
            'metadata.*' => ['string', 'max:1000'],
        ];
    }
}
```

### Authorization

```php
// Use policies for authorization
class SensorPolicy
{
    public function view(User $user, Sensor $sensor): bool
    {
        return $user->company_id === $sensor->company_id;
    }
    
    public function update(User $user, Sensor $sensor): bool
    {
        return $user->can('manage_sensors') && 
               $user->company_id === $sensor->company_id;
    }
}

// Check authorization in controllers
public function update(UpdateSensorRequest $request, Sensor $sensor)
{
    $this->authorize('update', $sensor);
    
    // Update logic
}
```

### Data Protection

```php
// Use encrypted casts for sensitive data
protected function casts(): array
{
    return [
        'api_credentials' => 'encrypted:array',
        'private_notes' => 'encrypted',
    ];
}

// Hash sensitive data before storage
public function setApiKeyAttribute($value): void
{
    $this->attributes['api_key'] = Hash::make($value);
}
```

## Documentation Standards

### Code Documentation

```php
/**
 * Process incoming sensor data and create a new reading record.
 * 
 * This method validates the incoming data, processes it according to
 * the sensor type, and stores it in the database. It also triggers
 * any configured alerts based on threshold values.
 *
 * @param Sensor $sensor The sensor that generated the data
 * @param array $data Raw sensor data including value, unit, and timestamp
 * @return SensorReading The created sensor reading record
 * 
 * @throws InvalidSensorDataException When data validation fails
 * @throws SensorOfflineException When sensor is not responding
 */
public function processSensorData(Sensor $sensor, array $data): SensorReading
{
    // Implementation
}
```

### API Documentation

```php
/**
 * @api {post} /api/sensors Create Sensor
 * @apiName CreateSensor
 * @apiGroup Sensors
 * @apiVersion 1.0.0
 * 
 * @apiParam {String} name Sensor name (max 255 characters)
 * @apiParam {String="temperature","humidity","soil_moisture"} type Sensor type
 * @apiParam {Number} latitude Latitude coordinate (-90 to 90)
 * @apiParam {Number} longitude Longitude coordinate (-180 to 180)
 * @apiParam {Object} [metadata] Additional sensor metadata
 * 
 * @apiSuccess {Number} id Sensor ID
 * @apiSuccess {String} name Sensor name
 * @apiSuccess {String} type Sensor type
 * @apiSuccess {Object} coordinates Sensor coordinates
 * @apiSuccess {String} created_at Creation timestamp
 * 
 * @apiError 422 Validation Error
 * @apiError 403 Insufficient Permissions
 */
```

This development guide ensures consistent, maintainable, and secure code across the Girasole project.