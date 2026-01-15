<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cultivation extends Model
{
    /** @use HasFactory<\Database\Factories\CultivationFactory> */
    use HasFactory;

    /**
     * I campi che possono essere assegnati in massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Ottieni le cultivar associate a questa coltivazione.
     */
    public function cultivars(): HasMany
    {
        return $this->hasMany(Cultivar::class);
    }

    /**
     * Get plant diseases associated with this crop.
     */
    public function plantDiseases(): BelongsToMany
    {
        return $this->belongsToMany(PlantDisease::class, 'cultivation_plant_disease');
    }
}
