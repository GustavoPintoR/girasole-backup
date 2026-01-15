<?php

namespace App\Models;

use App\Traits\CompanyAware;
use App\Interfaces\Companyable;
use App\Traits\ExportsGeometry;
use App\Interfaces\CompanyInterface;
use App\Traits\HasGeometryOperations;
use App\Services\CadastralGroupService;
use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CadastralGroup extends Model implements Companyable
{
    use HasFactory,
        HasGeometryOperations,
        ExportsGeometry,
        CompanyAware;

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'description',
        'total_area',
        'units_count',
        'color',
        'creation_method',
        'original_geojson',
        'boundary_geometry',
        'centroid',
    ];

    protected $casts = [
        'boundary_geometry' => MultiPolygon::class,
        'centroid' => Point::class,
    ];

    protected $appends = [
        'boundary_geometry_json',
        'centroid_json',
    ];

    public function getBoundaryGeometryJsonAttribute(): ?string
    {
        return $this->boundary_geometry ? json_encode($this->boundary_geometry) : null;
    }

    public function getCentroidJsonAttribute(): ?string
    {
        return $this->centroid ? json_encode($this->centroid) : null;
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function cadastralUnits(): HasMany
    {
        return $this->hasMany(CadastralUnit::class);
    }

    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    public function cultivars(): BelongsToMany
    {
        return $this->belongsToMany(Cultivar::class, 'cadastral_group_cultivar')
            ->withTimestamps();
    }

    public function forecastSetup(): HasMany
    {
        return $this->hasMany(ForecastSetup::class, 'field_id');
    }

    public function forecastLogs(): HasMany
    {
        return $this->hasMany(ForecastLog::class, 'field_id');
    }

    public function plantingSchemes(): BelongsToMany
    {
        return $this->belongsToMany(PlantingScheme::class, 'cadastral_group_planting_scheme')
            ->withTimestamps();
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)->withTimestamps();
    }
    
    public function plantDiseases(): BelongsToMany
    {
        return $this->belongsToMany(PlantDisease::class, 'cadastral_group_plant_disease')
            ->withTimestamps();
    }

    public function irrigations(): BelongsToMany
    {
        return $this->belongsToMany(Irrigation::class, 'cadastral_group_irrigation')
            ->withTimestamps();
    }

    /**
     * Helper methods
     */
    public function usesCadastralUnits(): bool
    {
        return $this->creation_method === 'units';
    }

    /**
     * Scope to get groups visible to a user
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        return $query->where(function ($q) use ($user, $companyIds) {
            $q->where('user_id', $user->id);

            if (!empty($companyIds)) {
                $q->orWhereIn('company_id', $companyIds);
            }
        });
    }


    /**
     * Check if user can manage this group
     */
    public function canBeMangedBy(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->company_id && $user->companies()->where('companies.id', $this->company_id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Add units to this group
     */
    public function addUnits(array $unitIds): array
    {
        return app(CadastralGroupService::class)->addUnitsToGroup($this, $unitIds);
    }

    public function getCentroidLongitude(): ?float
    {
        return $this->centroid?->getX();
    }

    public function getCentroidLatitude(): ?float
    {
        return $this->centroid?->getY();
    }
}
