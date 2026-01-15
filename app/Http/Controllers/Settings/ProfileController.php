<?php

namespace App\Http\Controllers\Settings;

use App\Models\Plan;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\PostalCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use PeterColes\Countries\CountriesFacade;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Requests\Settings\BillingInfoUpdateRequest;
use App\Http\Requests\Settings\BillingAddressUpdateRequest;
use App\Http\Requests\Settings\ShippingAddressUpdateRequest;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the user's billing info settings page.
     */
    public function billingInfo(Request $request): Response
    {
        $user = $request->user();

        $user = $user->load([
            'billingInfo',
        ]);

        return Inertia::render('settings/BillingInfo', [
            'user' => $user,
        ]);
    }

    /**
     * Show the user's billing address settings page.
     */
    public function billingAddress(Request $request): Response
    {
        $user = $request->user();

        $user = $user->load([
            'billingAddress.postalCode',
        ]);

        return Inertia::render('settings/BillingAddress', [
            'user' => $user,
            'countries' => CountriesFacade::keyValue(locale: app()->getLocale()),
        ]);
    }

    /**
     * Show the user's shipping address settings page.
     */
    public function shippingAddress(Request $request): Response
    {
        $user = $request->user();

        $user = $user->load([
            'shippingAddress.postalCode',
        ]);

        return Inertia::render('settings/ShippingAddress', [
            'user' => $user,
        ]);
    }

    /**
    * Show the user's subscription info settings page.
    */
    public function subscriptionInfo(Request $request): Response
{
    $user = $request->user();
    $plans = Plan::active()->get();
    $stripeSubscription = $user?->subscription('default') ?? $user?->subscription('manual');
    $endsAt = $stripeSubscription?->ends_at?->format('Y-m-d H:i') ?? null;
    $activePlan = null;
    $isOnGracePeriod = $stripeSubscription?->onGracePeriod() ?? false;

    if (!$endsAt && $stripeSubscription?->stripe_price) {
        $activePlan = $plans
            ->firstWhere('stripe_price_id', $stripeSubscription->stripe_price) ?? null;

        // Remove active plan from the collection
        $plans = $plans->reject(function ($plan) use ($activePlan) {
            return $plan?->id === $activePlan?->id;
        });
    }

    $user->load('subscriptions');

    return Inertia::render('settings/Subscription', [
        'user' => $user,
        'plan' => $activePlan,
        'plans' => $plans->values(), // reindex collection
        'currentSubscription' => $stripeSubscription,
        'ends_at' => $endsAt,
        'is_on_grace_period' => $isOnGracePeriod,
    ]);
}


    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit')->with('success', __('ui.model_updated', ['model' => __('ui.profile')]));
    }

    /**
     * Update the user's billing information.
     */
    public function updateBillingInfo(BillingInfoUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();
        if($user->billingInfo){
            $user->billingInfo->update([
                'fiscal_type' => $data['type'],
                'fiscal_code' => $data['fiscal_code'],
                'sdi' => $data['sdi_code'],
                'vat_number' => $data['vat_number'],
                'business_name' => $data['business_name'],
            ]);
        }else {
            $user->billingInfo()->create([
                'fiscal_type' => $data['type'],
                'fiscal_code' => $data['fiscal_code'],
                'sdi' => $data['sdi_code'],
                'vat_number' => $data['vat_number'],
                'business_name' => $data['business_name'],
            ]);
        }

        return to_route('profile.billing.info')->with('success', __('ui.model_updated', ['model' => __('ui.billing_info')]));
    }

    /**
     * Update the user's billing address information.
     */
    public function updateBillingAddress(BillingAddressUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        $postalCode = PostalCode::where('code', $data['postal_code'])->firstOrFail();

        if($user->billingAddress){
            $user->billingAddress->update([
                'street' => $data['street'],
                'street_number' => $data['street_number'],
                'postal_code_id' => $postalCode->id,
                'city_id' => $data['city_id'],
                'province_id' => $data['province_id'],
                'region_id' => $data['region_id'],
                'state' => strtoupper($data['state']),
            ]);
        }else {
            $user->billingAddress()->create([
                'street' => $data['street'],
                'street_number' => $data['street_number'],
                'postal_code_id' => $postalCode->id,
                'city_id' => $data['city_id'],
                'province_id' => $data['province_id'],
                'region_id' => $data['region_id'],
                'state' => strtoupper($data['state']),
            ]);
        }

        return to_route('profile.billing.address')->with('success', __('ui.model_updated', ['model' => __('ui.billing_address')]));
    }

    /**
     * Update the user's shipping address information.
     */
    public function updateShippingAddress(ShippingAddressUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        $postalCode = $request?->user?->billingAddress?->postalCode;

        $shippingPostalCode = $request->same_as_billing
            ? $postalCode
            : PostalCode::where('code', $request->shipping_postal_code)->firstOrFail();

        $shippingAddressData = $request->same_as_billing
            ? [
                'name' => $user->first_name . ' ' . $user->last_name,
                'postal_code_id' => $user->postalCode?->id,
                'city_id' => $user->billingAddress?->city_id,
                'province_id' => $user->billingAddress?->province_id,
                'region_id' => $user->billingAddress?->region_id,
                'address' => $user->billingAddress?->street_number . ', ' . $user->billingAddress?->street,
                'phone_number' => $user?->mobile_number,
                'user_id' => $user->id,
                'same_as_billing' => $data['same_as_billing']
            ]
            : [
                'name' => $data['name'],
                'postal_code_id' => $shippingPostalCode->id,
                'city_id' => $data['shipping_city_id'],
                'province_id' => $data['shipping_province_id'],
                'region_id' => $data['shipping_region_id'],
                'address' => $data['address'],
                'phone_number' => $data['cellular'],
                'user_id' => $user->id,
                'same_as_billing' => $data['same_as_billing']
            ];

        if($user->shippingAddress){
            $user->shippingAddress->update($shippingAddressData);
        }else {
            $user->shippingAddress()->create($shippingAddressData);
        }

        return to_route('profile.shipping.address')->with('success', __('ui.model_updated', ['model' => __('ui.shipping_address')]));
    }

    /**
     * Update the user's plan.
     */
    public function updatePlan(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if (!$plan->stripe_price_id) {
            return back()->withErrors(['plan_id' => __('ui.plan_missing_price')]);
        }

        if (!$user->hasStripeId()) {
                $user->createAsStripeCustomer();
            }

        try {
            $currentSubscription = $user?->subscription('default') ?? null;

            if ($currentSubscription) {
                // If user has an active subscription, swap to the new plan
                if ($currentSubscription->stripe_status === 'active' || $currentSubscription->onGracePeriod()) {
                    $currentSubscription->swap($plan->stripe_price_id);
                    return redirect()->back()
                        ->with('success', __('ui.user_subscription_updated'));
                } 
            } else {
                    if($subscription = $user?->subscription('manual')){
                        $subscription->delete();

                        $quantity = $request->input('quantity', 1);
                        if (!is_numeric($quantity) || $quantity < 1) {
                            return back()->withErrors(['general' => 'An error occurred while updating the subscription: ' . $e->getMessage()]);
                        }

                        $checkoutUrl = $user->newSubscription('default', $plan->stripe_price_id)
                            ->quantity((int) $quantity)
                            ->checkout([
                                'success_url' => route('settings.subscription.index'),
                                'cancel_url' => route('billing.plans'),
                                'metadata' => [
                                    'plan_id' => $plan->id,
                                    'quantity' => (int) $quantity,
                                    'features' => $plan->features ? implode(',', $plan->features) : []
                                ],
                        ]);

                        return Inertia::location($checkoutUrl->url);
                    }

                    $user->newSubscription('default', $plan->stripe_price_id)
                        ->create();
                }
        } catch (\Exception $e) {
            Log::error('Failed to update user subscription: ' . $e->getMessage());
            return back()->withErrors(['general' => 'An error occurred while updating the subscription: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', __('ui.model_deleted', ['model' => __('ui.user')]));
    }


}
