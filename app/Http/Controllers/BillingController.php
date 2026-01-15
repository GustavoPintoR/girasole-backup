<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Notifications\BroadcastMessageNotification;
use Inertia\Inertia;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function getPlans()
    {
        $plans = Plan::active()->visible()->get();

        if(Plan::active()->count() == 0){
            return redirect()->route('dashboard');
        }

        $hasActiveSub = auth()->user()?->subscription('default')?->onGracePeriod() ?? false;

        $activePlanId = $hasActiveSub ? auth()->user()?->subscription('default')?->stripe_price : null;

        return Inertia::render('billing/Subscribing', [
            'plans' => $plans,
            'hasActiveSub' => $hasActiveSub,
            'activePlanId' => $activePlanId,
        ]);
    }

    public function subscribe(Request $request, int $planId)
    {
        $user = $request->user();

        // Prevent duplicate subscription
        if ($user->subscribed('default')) {
            return new Response(__('ui.already_subscribed'), 409);
        }

        $plan = Plan::find($planId);
        if (!$plan) {
            return new Response(__('ui.plan_not_found'), 404);
        }

        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        if (!$plan->stripe_price_id) {
            return new Response(__('ui.plan_missing_price'), 422);
        }

        $quantity = $request->input('quantity', 1);
        if (!is_numeric($quantity) || $quantity < 1) {
            return new Response(__('ui.invalid_quantity'), 422);
        }

        try{
            $checkoutUrl = $user->newSubscription('default', $plan->stripe_price_id)
                ->quantity((int) $quantity)
                ->checkout([
                    'success_url' => route('billing.success'),
                    'cancel_url' => route('billing.plans'),
                    'metadata' => [
                        'plan_id' => $plan->id,
                        'quantity' => (int) $quantity,
                        'features' => $plan->features ? implode(',', $plan->features) : []
                    ],
                ]);

            // Redirect user to Stripe Checkout
            return Inertia::location($checkoutUrl->url);

        }catch (\Exception $e){
            return back()->with('error', $e->getMessage());
        }
    }


    public function success(Request $request)
    {
        return redirect()->route('dashboard')->with('success', 'Subscription successful! You can now access features.');
    }

    public function cancel(Request $request)
    {
        $user = $request->user();

        if (! $user->subscribed('default')) {
            return new Response(__('ui.no_active_subscription'), 404);
        }

        $subscription = $user->subscription('default');
        $subscription->cancel();

        $endsAt = $subscription->ends_at?->format('Y-m-d') ?? null;

        $user->notify(new BroadcastMessageNotification([
            'title' => trans('ui.goodbye_title'),
            'description' => $endsAt
                ? trans('ui.goodbye_message_with_access', [
                    'name' => $user->first_name,
                    'date' => $endsAt
                ])
                : trans('ui.goodbye_message', ['name' => $user->first_name]),
        ], true));

        return redirect()->route('settings.subscription.index')->with('success', __('ui.subscription_cancelled'));
    }
}
