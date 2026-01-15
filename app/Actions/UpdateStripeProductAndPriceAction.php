<?php

namespace App\Actions;

use App\Models\Plan;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Lorisleiva\Actions\Concerns\AsAction;
use Stripe\Exception\ApiErrorException;

class UpdateStripeProductAndPriceAction
{
    use AsAction;

    /**
     * Sync changes from the Plan model to Stripe:
     * - Update Product name if changed.
     * - If amount/currency/interval changed, create a new Price and update plan.stripe_price_id.
     */
    public function handle(Plan $plan, bool $justUpdate, bool $priceInputsChanged): void
    {
        $startedAt = microtime(true);
        $stripe = Cashier::stripe();

        // Ensure we have a Stripe product/price to update
        if (!$plan->stripe_product_id || !$plan->stripe_price_id) {
            Log::channel('payments')->warning('Plan missing Stripe IDs; cannot update. Consider provisioning first.', [
                'plan_id' => $plan->id,
            ]);
            return;
        }

        Log::channel('payments')->info('Stripe sync started for plan update.', [
            'plan_id' => $plan->id,
            'name_changed' => $justUpdate,
            'price_inputs_changed' => $priceInputsChanged,
            'stripe_product_id' => $plan->stripe_product_id,
            'stripe_price_id_old' => $plan->stripe_price_id,
            'unit_amount' => (int) round($plan->unit_amount, 0),
            'currency' => strtolower($plan->currency),
            'interval' => $plan->interval,
        ]);

        try {
            // Update product name
            if ($justUpdate) {
                $stripe->products->update($plan->stripe_product_id, [
                        'name' => $plan->name,
                        'metadata' => [
                            'features' => $plan->features ? implode(',', $plan->features) : []
                        ]
                ]);

                Log::channel('payments')->info('Stripe product updated (name).', [
                    'plan_id' => $plan->id,
                    'stripe_product_id' => $plan->stripe_product_id,
                    'new_name' => $plan->name,
                ]);

                $this->syncPlanStatusWithStripe($plan);
            }

            // Prices are immutable for amount/currency/interval => create a new price if those changed
            if ($priceInputsChanged) {
                $createParams = [
                    'unit_amount' => (int) round($plan->unit_amount, 0),
                    'currency' => strtolower($plan->currency),
                    'product' => $plan->stripe_product_id,
                    'metadata' => [
                        'app_plan_id' => (string) $plan->id,
                        'slug' => $plan->slug,
                        'features' => $plan->features ? implode(',', $plan->features) : []
                    ],
                ];

                if ($plan->interval !== 'custom') {
                    $createParams['recurring'] = [
                        'interval' => $plan->interval, // day|week|month|year
                        'interval_count' => 1,
                    ];
                }

                $newPrice = $stripe->prices->create($createParams);

                Log::channel('payments')->info('Stripe price created for updated plan inputs.', [
                    'plan_id' => $plan->id,
                    'stripe_product_id' => $plan->stripe_product_id,
                    'stripe_price_id_new' => $newPrice->id,
                    'unit_amount' => (int) round($plan->unit_amount, 0),
                    'currency' => strtolower($plan->currency),
                    'interval' => $plan->interval,
                ]);

                // Point plan to the new price
                $plan->forceFill([
                    'stripe_price_id' => $newPrice->id,
                ])->save();

                Log::channel('payments')->info('Plan updated to new Stripe price.', [
                    'plan_id' => $plan->id,
                    'stripe_price_id_new' => $newPrice->id,
                ]);
            }

            Log::channel('payments')->info('Stripe sync completed for plan update.', [
                'plan_id' => $plan->id,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (Exception $e) {
            Log::channel('payments')->error('Stripe API error during plan update sync.', [
                'plan_id' => $plan->id,
                'message' => $e->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            $plan->update(['active' => false]);

        } catch (\Throwable $e) {
            Log::channel('payments')->error('Unexpected error during plan update sync.', [
                'plan_id' => $plan->id,
                'message' => $e->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            $plan->update(['active' => false]);

        }
    }

    public function syncPlanStatusWithStripe(Plan $plan)
{
    $stripe = Cashier::stripe();

    try {
        // Attempt to retrieve the product from Stripe
        $stripeProduct = $stripe->products->retrieve($plan->stripe_product_id);

        if (!$stripeProduct->active && $plan->active) {
            // Product exists but is archived/deactivated → reactivate it
            $stripe->products->update($plan->stripe_product_id, [
                'active' => true,
            ]);

            Log::channel('payments')->info('Stripe product reactivated.', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $plan->stripe_product_id,
            ]);
        } elseif ($stripeProduct->active !== $plan->active) {
            // Product exists, status differs → update to match local plan
            $stripe->products->update($plan->stripe_product_id, [
                'active' => $plan->active,
            ]);

            Log::channel('payments')->info('Stripe product updated (active status).', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $plan->stripe_product_id,
                'new_active_status' => $plan->active,
            ]);
        }

    } catch (\Stripe\Exception\InvalidRequestException $e) {
        // Product does not exist on Stripe → dispatch creation
        Log::channel('payments')->warning('Stripe product not found. Dispatching creation.', [
            'plan_id' => $plan->id,
            'stripe_product_id' => $plan->stripe_product_id,
            'exception' => $e->getMessage(),
        ]);

        // Dispatch a job to create product on Stripe
        $plan->stripe_price_id = null; // Clear invalid price ID
        $plan->stripe_product_id = null; // Clear invalid product ID
        $plan->save();
        
        CreateStripeProductAndPriceAction::dispatch($plan);
    } catch (\Exception $e) {

        Log::channel('payments')->error('Error syncing plan with Stripe.', [
            'plan_id' => $plan->id,
            'exception' => $e->getMessage(),
        ]);
    }
}
}
