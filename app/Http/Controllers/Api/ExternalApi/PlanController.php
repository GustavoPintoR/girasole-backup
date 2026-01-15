<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Helpers\PlanHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Plans
 *
 * API for users to subscribe to a plan
 *
 */
class PlanController extends Controller
{
    /**
     * Get all active plans.
     *
     * Returns a list of all active subscription plans available.
     * @header Content-Type application/json
     * @header Accept application/json
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Plans retrieved successfully",
     *   "plans": [
     *     {
     *       "id": 1,
     *       "name": "Basic Plan",
     *       "stripe_price_id": "price_123",
     *       "features": ["feature1", "feature2"],
     *       "active": true
     *     },
     *     {
     *       "id": 2,
     *       "name": "Pro Plan",
     *       "stripe_price_id": "price_456",
     *       "features": ["feature1", "feature2", "feature3"],
     *       "active": true
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $plans = Plan::active()->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.plans')]),
            'plans' => PlanResource::collection($plans),
        ]);
    }

    /**
     * Generate a Stripe checkout URL for subscribing to a plan.
     *
     * Creates a Stripe checkout session for the specified plan and returns the URL.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam plan_id integer required The ID of the plan to subscribe to. Example: 1
     * @response 200 {
     *   "message": "Checkout URL generated successfully",
     *   "checkout_url": "https://checkout.stripe.com/pay/cs_123"
     * }
     * @response 404 {
     *   "message": "Plan not found"
     * }
     * @response 409 {
     *   "message": "User is already subscribed"
     * }
     * @response 422 {
     *   "message": "Plan is missing Stripe price ID"
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *    "message": "The token has read permission only"
     * }
     */
    public function subscribe(Request $request, Plan $plan): JsonResponse
    {
        $user = $request->user();

        if(PlanHelper::checkIfTokenIsReadOnly($user)){
            return response()->json([
                'message' => __('ui.token_read_only'),
            ], 403);
        }

        if ($user->subscribed('default')) {
            return response()->json([
                'message' => __('ui.user_already_subscribed'),
            ], 409);
        }

        if (!$plan->stripe_price_id) {
            return response()->json([
                'message' => __('ui.stripe_price_id_not_found'),
            ], 422);
        }

        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        $checkout = $user->newSubscription('default', $plan->stripe_price_id)
            ->checkout([
                'success_url' => route('billing.success'),
                'cancel_url'  => route('billing.cancel'),
                'metadata'    => [
                    'plan_id' => $plan->id,
                    'features' => $plan->features ? implode(',', $plan->features) : []
                ],
            ]);

        return response()->json([
            'message' => __('ui.checkout_url_generated'),
            'checkout_url' => $checkout->url,
        ]);
    }
}
