<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingAddress extends Model
{
    protected $fillable = [
        'street',
        'street_number',
        'postal_code_id',
        'mobile_number',
        'city_id',
        'province_id',
        'region_id',
        'state',
        'company_id',
    ];

    /**
     * Gets the company associated with this billing address.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Gets the region associated with this billing address.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Gets the province associated with this billing address.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Gets the city associated with this billing address.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Gets the postal code associated with this billing address.
     */
    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class);
    }
}
