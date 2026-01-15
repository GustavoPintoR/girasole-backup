<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlantingScheme extends Model
{
    use HasFactory, HasPermissions;

    /**
     * Accessors to append to the model's array and JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'distance',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'row_spacing',
        'plant_spacing',
        'pattern',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'row_spacing' => 'float',
        'plant_spacing' => 'float',
        'pattern' => \App\Enums\Pattern::class,
    ];

    /**
     * Get a formatted spacing description
     */
    protected function distance(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->row_spacing && ! $this->plant_spacing) {
                    return '—';
                }

                return sprintf(
                    '%s x %s',
                    $this->row_spacing ? number_format($this->row_spacing, 2).' m' : '—',
                    $this->plant_spacing ? number_format($this->plant_spacing, 2).' m' : '—'
                );
            }
        );
    }

    /**
     * The cadastral groups that belong to the planting scheme.
     */
    public function cadastralGroups(): BelongsToMany
    {
        return $this->belongsToMany(CadastralGroup::class, 'cadastral_group_planting_scheme')
            ->withTimestamps();
    }
}
