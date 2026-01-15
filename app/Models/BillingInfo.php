<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingInfo extends Model
{
    protected $fillable = [
        'business_name',
        'fiscal_type',
        'fiscal_code',
        'sdi',
        'vat_number',
        'company_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
