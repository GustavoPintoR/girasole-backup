<?php

namespace App\Models;

use App\Observers\CadastralUnitObserver;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Data\Geometries\Polygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class CadastralUnit extends Model
{
    /** @use HasFactory<\Database\Factories\CadastralUnitFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'cadastral_group_id',
        'city_id',
        'section',
        'sheet',
        'parcel',
        'sub',
        'category',
        'class',
        'cadastral_area',
        'dominical_income',
        'agrarian_income',
        'notes',
        'geometry_source',
        'geometry',
        'centroid',
        'geometry_updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'geometry' => Polygon::class,
        'centroid' => Point::class,
        'geometry_updated_at' => 'datetime',
        'dominical_income' => 'decimal:2',
        'agrarian_income' => 'decimal:2',
    ];

    protected $appends = [
        'geometry_json',
        'centroid_json',
    ];

    public function getGeometryJsonAttribute(): ?string
    {
        return $this->geometry ? json_encode($this->geometry) : null;
    }

    public function getCentroidJsonAttribute(): ?string
    {
        return $this->centroid ? json_encode($this->centroid) : null;
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cadastral group this unit belongs to.
     */
    public function cadastralGroup(): BelongsTo
    {
        return $this->belongsTo(CadastralGroup::class);
    }

    /**
     * Check if this unit is in a group
     */
    public function isGrouped(): bool
    {
        return $this->cadastral_group_id !== null;
    }

    /**
     * The dataset parser returns a string of lat/lon pairs separated by spaces so we need to convert it to a GeoJSON polygon
     *
     * @return void
     */
    public static function posListToGeojson(string $posList): string
    {
        $posList = trim($posList);
        $coords = array_map('floatval', preg_split('/\s+/', $posList));

        $pairs = [];
        for ($i = 0; $i < count($coords); $i += 2) {
            $lat = $coords[$i];
            $lon = $coords[$i + 1];
            $pairs[] = [$lon, $lat];
        }

        $geojson = [
            'type' => 'Polygon',
            'coordinates' => [$pairs],
        ];

        return json_encode($geojson, JSON_PRETTY_PRINT);
    }
}
