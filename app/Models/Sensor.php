<?php

namespace App\Models;

use App\Interfaces\Companyable;
use App\Traits\CompanyAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Clickbar\Magellan\Data\Geometries\Point;
use App\Models\User;

class Sensor extends Model implements Companyable
{
     use HasFactory, CompanyAware;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'serial_number',
        'urn',
        'iccid',
        'transmission_module_identification',
        'description',
        'latitude',
        'longitude',
        'owner_id',
        'company_id',
        'metadata',
        'firmware',
        'sensor_type_id',
        'cadastral_group_id',
    ];

     /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'json',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function sensorType(): BelongsTo
    {
        return $this->belongsTo(SensorType::class);
    }

    /**
     * @return BelongsTo
     */
    public function cadastralGroup(): BelongsTo
    {
        return $this->belongsTo(CadastralGroup::class, 'cadastral_group_id');
    }

    public function scopeType($query, ?string $type)
    {
        if ($type) {
            $query->whereHas('sensorType', function ($query) use ($type) {
                $query->where('name', $type);
            });
        }
        return $query;
    }

    public function scopeOwnedBy($query, ?int $userId)
    {
        if ($userId) {
            $query->where('owner_id', $userId);
        }
        return $query;
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        return $query->where(function ($q) use ($user, $companyIds) {
            $q->where('owner_id', $user->id);

            if (!empty($companyIds)) {
                $q->orWhereIn('company_id', $companyIds);
            }
        });
    }
}
