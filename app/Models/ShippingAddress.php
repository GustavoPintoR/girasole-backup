<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'city_id',
        'province_id',
        'region_id',
        'postal_code_id',
        'address',
        'name',
        'phone_number',
        'same_as_billing'
    ];

    /**
     * Gets the company associated with this shipping address.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Gets the province associated with this shipping address.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Gets the city associated with this shipping address.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Gets the postal code associated with this shipping address.
     */
    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class);
    }
}
