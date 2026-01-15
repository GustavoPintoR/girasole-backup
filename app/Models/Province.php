<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    /** @use HasFactory<\Database\Factories\ProvinceFactory> */
    use HasFactory;

    /**
     * I campi che possono essere assegnati in massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'region_id',
    ];

    /**
     * Ottiene la regione associata a questa provincia.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Ottiene i comuni associati a questa provincia.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
