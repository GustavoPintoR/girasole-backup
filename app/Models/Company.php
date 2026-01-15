<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\RegionFactory> */
    use HasFactory;

    /**
     * I campi che possono essere assegnati in massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    public function owner() : BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class)->orderBy('last_name');
    }

    public function billingInfo(): HasOne
    {
        return $this->hasOne(BillingInfo::class);
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(BillingAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeIncludingOrphaned($query)
    {
        return $query; // Returns all companies including orphaned ones
    }

    public function scopeOrphaned($query)
    {
        return $query->whereNull('owner_id');
    }

    public function isOrphaned(): bool
    {
        return $this->owner_id === null;
    }
}
