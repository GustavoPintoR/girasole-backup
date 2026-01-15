<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Inertia\Inertia;
use App\Enums\UserRole;
use App\Helpers\SubscriptionHelper;
use App\Models\PostalCode;
use App\Models\BillingInfo;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\BillingAddress;
use App\Models\ShippingAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use PeterColes\Countries\CountriesFacade;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\PermissionRegistrar;
use App\Models\TermsAndConditions;
use App\Rules\UserBelongsToCompany;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:first_name,last_name,email,mobile_number,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['first_name', 'last_name', 'email', 'mobile_number'];

        $query = User::withTrashed();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%' . $search . '%');
                }
            });
        }

        $users = $query
            ->with(['roles', 'subscriptions', 'termsAndConditions'])
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('first_name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('users/Index', [
            'users' => $users,
            'sorting' => compact('sortBy', 'sortOrder'),
            'filtering' => [
                'search' => $search,
                'searchableColumns' => $searchableColumns,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', User::class);

        $countries = CountriesFacade::keyValue(locale: app()->getLocale());
        $roles = array_map(fn($role) => [
            'id' => $role->value,
            'name' => $role->value,
        ], UserRole::cases());

        return Inertia::render('users/Create', compact('countries', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Password::defaults()],
            'type' => ['required', 'string', 'max:50', 'in:business,sole_business'],
            'business_name' => ['nullable', 'string', 'max:255', 'required_if:type,business'],
            'fiscal_code' => ['required', 'string', 'max:16'],
            'vat_number' => ['nullable', 'string', 'max:11', 'required_if:type,business'],
            'mobile_phone' => ['required', 'string', 'max:20'],
            'sdi_code' => ['nullable', 'string', 'max:7', 'required_if:type,business'],
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10', 'exists:postal_codes,code'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'state' => ['required', 'string', 'max:2'],
            'active' => ['required', 'boolean'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'same_as_billing' => ['required', 'boolean'],
            'shipping_region_id' => ['required_if:same_as_billing,false', 'integer', 'exists:regions,id'],
            'shipping_province_id' => ['required_if:same_as_billing,false', 'integer', 'exists:provinces,id'],
            'shipping_postal_code' => ['required_if:same_as_billing,false', 'string', 'max:10', 'exists:postal_codes,code'],
            'shipping_city_id' => ['required_if:same_as_billing,false', 'integer', 'exists:cities,id'],
            'name' => ['required_if:same_as_billing,false', 'string', 'max:255'],
            'address' => ['required_if:same_as_billing,false', 'string', 'max:255'],
            'cellular' => ['required_if:same_as_billing,false', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $postalCode = PostalCode::where('code', $request->postal_code)->firstOrFail();
            $shippingPostalCode = $request->same_as_billing
                ? $postalCode
                : PostalCode::where('code', $request->shipping_postal_code)->firstOrFail();

            $user = DB::transaction(function () use ($request, $postalCode, $shippingPostalCode) {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'mobile_number' => preg_replace('/\D/', '', $request->mobile_phone), // Only digits
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'active' => $request->active,
                ]);

                $user->assignRole($request->role);

                $companyName = $request->type === 'business'
                    ? $request->business_name
                    : $request->first_name . ' ' . $request->last_name;

                $company = Company::create([
                    'name' => $companyName,
                    'owner_id' => $user->id,
                ]);

                $user->update(['main_company_id' => $company->id]);

                $company->users()->sync([$user->id]);

                BillingAddress::create([
                    'street' => $request->street,
                    'street_number' => $request->street_number,
                    'postal_code_id' => $postalCode->id,
                    'city_id' => $request->city_id,
                    'province_id' => $request->province_id,
                    'region_id' => $request->region_id,
                    'state' => strtoupper($request->state),
                    'company_id' => $company->id,
                ]);

                BillingInfo::create([
                    'fiscal_type' => $request->type,
                    'fiscal_code' => $request->fiscal_code,
                    'sdi' => $request->sdi_code,
                    'vat_number' => $request->vat_number,
                    'business_name' => $request->business_name,
                    'company_id' => $company->id,
                ]);

                $shippingAddressData = $request->same_as_billing
                    ? [
                        'name' => $request->first_name . ' ' . $request->last_name,
                        'postal_code_id' => $postalCode->id,
                        'city_id' => $request->city_id,
                        'province_id' => $request->province_id,
                        'region_id' => $request->region_id,
                        'address' => $request->street_number . ', ' . $request->street,
                        'phone_number' => preg_replace('/\D/', '', $request->mobile_phone), // Only digits
                        'company_id' => $company->id,
                        'same_as_billing' => $request->same_as_billing
                    ]
                    : [
                        'name' => $request->name,
                        'postal_code_id' => $shippingPostalCode->id,
                        'city_id' => $request->shipping_city_id,
                        'province_id' => $request->shipping_province_id,
                        'region_id' => $request->shipping_region_id,
                        'address' => $request->address,
                        'phone_number' => preg_replace('/\D/', '', $request->cellular), // Only digits
                        'company_id' => $company->id,
                        'same_as_billing' => $request->same_as_billing
                    ];

                ShippingAddress::create($shippingAddressData);

                return $user;
            });

            event(new Registered($user));

            return redirect(route('users.index'))
                ->with('success', 'User created successfully.');
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['postal_code' => 'Invalid postal code'])->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage());
            return back()->withErrors(['general' => 'An error occurred while creating the user: ' . $e])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        Gate::authorize('view', $user);

        $user = $user->load([
            'termsAndConditions',
            'mainCompany.billingInfo',
            'companyOwner',
            'companies' => function ($query) {
                $query->select('companies.id', 'companies.name', 'companies.owner_id');
            },
        ]);

        $userRole = Arr::get($user->getRoleNames()->toArray(), '0');

        return Inertia::render('users/Show', compact('user', 'userRole'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        $user = $user->load([
            'companies',
        ]);

        $roles = array_map(fn($role) => [
            'id' => $role->value,
            'name' => $role->value,
        ], UserRole::cases());

        $userRole = Arr::get($user->getRoleNames()->toArray(), '0');

        $latestTerms = TermsAndConditions::where('is_active', true)->first();

        return Inertia::render('users/Edit', compact('user', 'roles', 'userRole', 'latestTerms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'mobile_phone' => ['required', 'string', 'max:20'],
            'active' => ['required', 'boolean'],
            'role' => ['required'],
            'accepted_terms' => ['boolean'],
            'main_company_id' => [
                'nullable',
                'integer',
                new UserBelongsToCompany($user),
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $termsData = [];
        if ($request->has('accepted_terms')) {
            $latestTerms = TermsAndConditions::where('is_active', true)->first();
            if ($request->accepted_terms && $latestTerms) {
                $termsData['terms_and_conditions_id'] = $latestTerms->id;
                $termsData['accepted_at'] = now();
            } else {
                $termsData['terms_and_conditions_id'] = null;
                $termsData['accepted_at'] = null;
            }
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile_number' => preg_replace('/\D/', '', $request->mobile_phone), // Only digits
            'email' => $request->email,
            'active' => $request->active,
            'main_company_id' => $request->main_company_id,
            ...($request->filled('password') ? ['password' => Hash::make($request->password)] : []),
            ...$termsData,
        ]);

        $user->syncRoles($request->role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect(route('users.index'))
            ->with('success', 'User updated successfully.');
    }

    /**
     * Trash the specified resource.
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return back()->with('success', 'User trashed successfully.');
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);

        Gate::authorize('restore', $user);

        $user->restore();

        return back()->with('success', 'User restored successfully.');
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete(int $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);

        Gate::authorize('forceDelete', $user);

        // delete user data first
        $user->billingInfo?->delete();
        $user->billingAddress()->delete();
        $user->shippingAddress()->delete();

        $user->forceDelete();

        return back()->with('success', 'User permanently deleted.');
    }

    /**
     * Display the user's current plan.
     */
    public function viewPlan(User $user)
    {
        Gate::authorize('view', $user);

        $currentSubscription = $user->subscription('default') ?? $user->subscription('manual');

        $currentPlan = null;
        if ($currentSubscription && $currentSubscription->stripe_price) {
            $currentPlan = Plan::where('stripe_price_id', $currentSubscription->stripe_price)->first();
        }

        return Inertia::render('users/plan/Show', [
            'user' => $user,
            'currentSubscription' => $currentSubscription ? [
                'id' => $currentSubscription->id,
                'stripe_id' => $currentSubscription->stripe_id,
                'stripe_price' => $currentSubscription->stripe_price,
                'stripe_status' => $currentSubscription->stripe_status,
                'quantity' => $currentSubscription->quantity,
                'trial_ends_at' => $currentSubscription->trial_ends_at,
                'ends_at' => $currentSubscription->ends_at,
                'created_at' => $currentSubscription->created_at,
                'updated_at' => $currentSubscription->updated_at,
                'on_trial' => $currentSubscription->onTrial(),
                'on_grace_period' => $currentSubscription->onGracePeriod(),
                'canceled' => $currentSubscription->canceled(),
                'active' => $currentSubscription->active(),
                'type' => $currentSubscription->type,
            ] : null,
            'currentPlan' => $currentPlan,
        ]);
    }

    /**
     * Show the form for managing user's plan.
     */
    public function managePlan(User $user)
    {
        Gate::authorize('update', $user);

        $plans = Plan::active()->get();
        $currentSubscription = $user->subscription('default') ?? $user->subscription('manual');

        $currentPlan = null;
        if ($currentSubscription && $currentSubscription->stripe_price) {
            $currentPlan = Plan::where('stripe_price_id', $currentSubscription->stripe_price)->first();
        }

        $hasActiveSub = (bool) $currentSubscription;
        $activePlanId = $currentPlan?->id;

        $plans = $plans->reject(fn ($plan) => $plan->id === $activePlanId);
        $plans = $plans->values()->all();

        return Inertia::render('users/plan/Edit', [
            'user' => $user,
            'plans' => $plans,
            'currentSubscription' => $currentSubscription ? [
                'stripe_price' => $currentSubscription->stripe_price,
                'stripe_status' => $currentSubscription->stripe_status,
                'ends_at' => $currentSubscription->ends_at,
                'on_grace_period' => $currentSubscription->onGracePeriod(),
                'type' => $currentSubscription->type,
            ] : null,
            'currentPlan' => $currentPlan,
            'hasActiveSub' => $hasActiveSub,
            'activePlanId' => $activePlanId,
        ]);
    }

    /**
     * Update the user's plan.
     */
    public function updatePlan(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if (!$plan->stripe_price_id) {
            return back()->withErrors(['plan_id' => __('ui.plan_missing_price')]);
        }

        try {

            $quantity = $request->input('quantity', 1);
            $endsAt = $request->input('ends_at', null);
            if (!is_numeric($quantity) || $quantity < 1) {
                return back()->withErrors(['general' => 'An error occurred while updating the subscription: ' . __('ui.invalid_quantity')]);
            }

            // Ensure user has a Stripe customer ID
            if (!$user->hasStripeId()) {
                $user->createAsStripeCustomer();
            }

            $currentSubscription = $user->subscription('default');
            $hasManualSubscription = $user->subscription('manual');

            if ($currentSubscription || $hasManualSubscription) {
                if($hasManualSubscription){
                    $hasManualSubscription->delete();
                }

                // If user has an active recorrent subscription, swap to the new plan
                if ($currentSubscription && ($currentSubscription->stripe_status === 'active' || $currentSubscription->onGracePeriod())) {
                    $currentSubscription->update([
                        'quantity' => $quantity,
                        'ends_at' => $endsAt,
                    ]);

                    $currentSubscription->swap($plan->stripe_price_id);

                    return redirect()->route('users.view-plan', $user)
                    ->with('success', __('ui.user_subscription_updated'));
                } else {
                    // If not, just create a new manual subscription
                    if($currentSubscription){
                        $currentSubscription->delete();
                    }

                    SubscriptionHelper::newSubscription($user, $plan, $quantity, $endsAt);
                }
            }

            if(is_null($currentSubscription) || is_null($hasManualSubscription)){
                SubscriptionHelper::newSubscription($user, $plan, $quantity, $endsAt);
            }

            return redirect()->route('users.view-plan', $user)
                ->with('success', __('ui.user_subscription_created'));
        } catch (\Exception $e) {
            Log::channel('subscriptions')->error('Failed to update user subscription: ' . $e->getMessage());
            return back()->withErrors(['general' => 'An error occurred while updating the subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the user's custom plan expire date.
     */
    public function setCustomPlanDetails(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if (!$plan->stripe_price_id) {
            return back()->withErrors(['plan_id' => __('ui.plan_missing_price')]);
        }

        try {
            $endsAt = $request->input('ends_at', null);
            $quantity = $request->input('quantity', null);

            $subscription = $user->subscriptions()->first();

            $toUpdate['ends_at'] = $endsAt;

            if (!is_null($quantity)) {
                $toUpdate['quantity'] = $quantity;
            }

            if (!empty($toUpdate)) {
                $subscription->update($toUpdate);
            }

            return redirect()->route('users.manage-plan', $user)
                ->with('success', __('ui.user_subscription_updated'));
        } catch (\Exception $e) {
            Log::channel('subscriptions')->error('Failed to update user subscription: ' . $e->getMessage());
            return back()->withErrors(['general' => 'An error occurred while updating the subscription: ' . $e->getMessage()]);
        }
    }

   public function getSubscription(Request $request, User $user)
    {
        $subscription = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        if (!$subscription) {
            return null;
        }

        // Translate the subscription type
        $typeLabel = match ($subscription->type) {
            'default' => trans('ui.automatic'),
            'manual'  => trans('ui.manual'),
            default   => '—',
        };

        // Format ends_at in Italian locale and 24h time
        $formattedEndsAt = Carbon::parse($subscription->ends_at)
            ->locale('it')
            ->translatedFormat('Y-m-d');

        // Return as a single formatted string
        return "{$typeLabel} ({$formattedEndsAt})";
    }
}
