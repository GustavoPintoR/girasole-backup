<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;

    /**
     * I campi che possono essere assegnati in massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'cadastral_code',
        'province_id',
        'region_id',
    ];

    /**
     * Gets the province associated with this city.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Gets the region associated with this city
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the postal codes associated with this city.
     */
    public function postalCodes(): BelongsToMany
    {
        return $this->belongsToMany(PostalCode::class)
            ->withPivot('zone', 'notes')
            ->withTimestamps();
    }
}
