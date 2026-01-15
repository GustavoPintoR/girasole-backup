<?php

namespace App\Actions;

use App\Models\Plan;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Lorisleiva\Actions\Concerns\AsAction;
use Stripe\Exception\ApiErrorException;

class CreateStripeProductAndPriceAction
{
    use AsAction;

    public function handle(Plan $plan): void
    {
        // Skip if already linked (e.g., import/sync)
        if ($plan->stripe_product_id && $plan->stripe_price_id) {
            Log::channel('payments')->info('Plan already linked to Stripe, skipping.', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $plan->stripe_product_id,
                'stripe_price_id' => $plan->stripe_price_id,
            ]);
            return;
        }

        $startedAt = microtime(true);
        $stripe = Cashier::stripe();

        // Idempotency keys
        $productKey = 'plan_product_' . $plan->id . '_' . Str::uuid();
        $priceKey   = 'plan_price_' . $plan->id . '_' . Str::uuid();

        // Input summary for traceability (avoid logging PII)
        $inputContext = [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
            'interval' => $plan->interval,      // expected: day|week|month|year
            'currency' => strtolower($plan->currency),
            'unit_amount' => (int) round($plan->unit_amount, 0), // minor units
            'idempotency_product' => $productKey,
            'idempotency_price' => $priceKey,
        ];

        Log::channel('payments')->info('Stripe provisioning started for plan.', $inputContext);

        try {
            $product = $stripe->products->create([
                'name' => $plan->name,
                'metadata' => [
                    'app_plan_id' => (string) $plan->id,
                    'slug' => $plan->slug,
                    'features' => $plan->features ? implode(',', $plan->features) : []
                ],
            ], [
                'idempotency_key' => $productKey,
            ]);

            Log::channel('payments')->info('Stripe product created.', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $product->id,
                'idempotency_product' => $productKey,
            ]);

            $createParams = [
                    'unit_amount' => (int) round($plan->unit_amount, 0),
                    'currency' => strtolower($plan->currency),
                    'product' => $product->id,
                    'metadata' => [
                        'app_plan_name' => (string) $plan->name,
                        'app_plan_id' => (string) $plan->id,
                        'slug' => $plan->slug,
                    ],
                ];

                 if ($plan->interval !== 'custom') {
                    $createParams['recurring'] = [
                        'interval' => $plan->interval, // day|week|month|year
                        'interval_count' => 1,
                    ];
                }

            $price = $stripe->prices->create($createParams, ['idempotency_key' => $priceKey]);

            Log::channel('payments')->info('Stripe price created.', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id,
                'idempotency_price' => $priceKey,
                'interval' => $plan->interval,
                'currency' => strtolower($plan->currency),
                'unit_amount' => (int) round($plan->unit_amount, 0),
            ]);

            $plan->forceFill([
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id,
            ])->save();

            Log::channel('payments')->info('Plan updated with Stripe IDs.', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (Exception $e) {
            Log::channel('payments')->debug('Stripe API error during plan provisioning.', [
                'plan_id' => $plan->id,
                'message' => $e->getMessage(),
                'idempotency_product' => $productKey,
                'idempotency_price' => $priceKey,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
            // Optionally deactivate the plan to prevent selection until resolved.
            $plan->update(['active' => false]);
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Unexpected error during plan provisioning.', [
                'plan_id' => $plan->id,
                'message' => $e->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
            $plan->update(['active' => false]);
        }
    }
}
