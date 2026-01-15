<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensorType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * @return HasMany
     */
    public function sensor(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    /**
     * @return BelongsToMany
     */
    public function sensorOperations(): BelongsToMany
    {
        return $this->belongsToMany(SensorOperation::class);
    }
}
