<?php

namespace App\Observers;

use App\Actions\CreateStripeProductAndPriceAction;
use App\Actions\DeleteStripeProductAndPriceAction;
use App\Actions\UpdateStripeProductAndPriceAction;
use Exception;
use App\Models\Plan;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Log;

class PlanObserver
{
    public function created(Plan $plan): void
    {
        CreateStripeProductAndPriceAction::dispatch($plan);
    }

    public function updated(Plan $plan): void
    {
        $justUpdate = $plan->wasChanged('name', 'active', 'features');
        $priceInputsChanged = $plan->wasChanged(['unit_amount', 'currency', 'interval']);

        UpdateStripeProductAndPriceAction::dispatch($plan, $justUpdate, $priceInputsChanged);
    }

    public function deleted(Plan $plan): void
    {
        // Note: Stripe products/prices are not deleted but archived to preserve historical data.
        $plan->active = false;
        $plan->save();
    }
}
