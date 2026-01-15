<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlantDisease extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the cultivations associated with this plant disease.
     */
    public function cultivations(): BelongsToMany
    {
        return $this->belongsToMany(Cultivation::class, 'cultivation_plant_disease');
    }

    /**
     * The cadastral groups that belong to the plant disease.
     */
    public function cadastralGroups(): BelongsToMany
    {
        return $this->belongsToMany(CadastralGroup::class, 'cadastral_group_plant_disease')
            ->withTimestamps();
    }
}
