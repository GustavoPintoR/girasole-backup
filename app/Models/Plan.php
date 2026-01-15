<?php

namespace App\Models;

use App\Casts\PriceCast;
use App\Observers\PlanObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy([PlanObserver::class])]
class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name','slug','interval','currency','unit_amount',
        'stripe_product_id','stripe_price_id','features','active',
        'cadastral_units_number', 'field_groups_number', 'field_groups_max_area',
        'hide_plan', 'expired_at'
    ];

    protected $casts = [
        'features' => 'array',
        'active' => 'boolean',
        'unit_amount' => PriceCast::class,
        'expired_at' => 'datetime'
    ];
    
    /**
     * Scope a query to only include active plans
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', true);
    }

     /**
     * Scope a query to only include visible plans
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('hide_plan', false);
    }

     /**
     * Scope a query to only include admin users.
     */
    #[Scope]
    protected function hasPrice(Builder $query, string $priceId): void
    {
        $query->where('stripe_price_id', $priceId);
    }
}
