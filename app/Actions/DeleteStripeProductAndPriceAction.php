<?php

namespace App\Actions;

use App\Models\Plan;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteStripeProductAndPriceAction
{
    use AsAction;

    public function handle(Plan $plan): void
    {
        $startedAt = microtime(true);
        $stripe = Cashier::stripe();

        Log::channel('payments')->info('Stripe deletion flow started for plan.', [
            'plan_id' => $plan->id,
        ]);

        try {
            $pricesToDelete = [];

            if ($plan->stripe_product_id) {
                $pricesResponse = $stripe->prices->all([
                    'product' => $plan->stripe_product_id,
                    'limit' => 100,
                ]);

                foreach ($pricesResponse->data as $price) {
                    $pricesToDelete[] = $price;

                    $subscriptions = $stripe->subscriptions->all([
                        'price' => $price->id,
                        'status' => 'active',
                        'limit' => 1,
                    ]);

                    if (count($subscriptions->data) > 0) {
                        Log::channel('payments')->warning('Active subscription blocks deletion.', [
                            'plan_id' => $plan->id,
                            'stripe_price_id' => $price->id,
                        ]);
                        throw new Exception(__('ui.cannot_delete_plan'));
                    }
                }
            }

            $productDeleted = false;
            if ($plan->stripe_product_id) {
                try {
                    $result = $stripe->products->delete($plan->stripe_product_id);
                    $productDeleted = !empty($result->deleted);

                    if ($productDeleted) {
                        $plan->forceFill([
                            'stripe_product_id' => null,
                            'stripe_price_id' => null,
                            'active' => false,
                        ])->save();

                        Log::channel('payments')->info('Stripe product deleted.', [
                            'plan_id' => $plan->id,
                            'stripe_product_id' => $plan->stripe_product_id,
                        ]);
                    } else {
                        Log::channel('payments')->warning('Product not marked as deleted.', [
                            'plan_id' => $plan->id,
                            'result' => $result,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::channel('payments')->error('Product deletion failed even after price deletion.', [
                        'plan_id' => $plan->id,
                        'message' => $e->getMessage(),
                    ]);
                    throw new Exception(__('ui.prices_might_exist'));
                }
            }

            $plan->delete();

            Log::channel('payments')->info('Plan fully deleted.', [
                'plan_id' => $plan->id,
                'prices_deleted' => count($pricesToDelete),
                'product_deleted' => $productDeleted,
                'duration_ms' => (int)((microtime(true) - $startedAt) * 1000),
            ]);

        } catch (\Throwable $e) {
            Log::channel('payments')->error('Plan deletion failed.', [
                'plan_id' => $plan->id,
                'message' => $e->getMessage(),
                'duration_ms' => (int)((microtime(true) - $startedAt) * 1000),
            ]);
            throw $e;
        }
    }

    protected function archiveProduct($stripe, Plan $plan): void
    {
        try {
            $stripe->products->update($plan->stripe_product_id, ['active' => false]);
            Log::channel('payments')->info('Stripe product archived.', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $plan->stripe_product_id,
            ]);
            // Keep IDs for history; deactivate locally
            $plan->update(['active' => false]);
            Log::channel('payments')->info('Plan deactivated after archiving product.', [
                'plan_id' => $plan->id,
            ]);
        } catch (Exception $e) {
            Log::channel('payments')->error('Failed to archive Stripe product.', [
                'plan_id' => $plan->id,
                'stripe_product_id' => $plan->stripe_product_id,
                'message' => $e->getMessage(),
            ]);
            throw new Exception(__('ui.archive_plan_failed'));
        }
    }
}
