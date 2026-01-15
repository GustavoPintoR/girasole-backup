<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensorOperation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'label'];

    /**
     * @return BelongsToMany
     */
    public function sensorTypes(): BelongsToMany
    {
        return $this->belongsToMany(SensorType::class, 'sensor_operation_sensor_type', 'sensor_operation_id', 'sensor_type_id');
    }
}
