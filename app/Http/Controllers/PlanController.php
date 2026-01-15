<?php

namespace App\Http\Controllers;

use App\Actions\DeleteStripeProductAndPriceAction;
use App\Enums\Plans;
use App\Models\Plan;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Plan::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|string|in:name,interval,currency,unit_amount,active,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'unit_amount';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name','slug','interval'];

        $query = Plan::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $query->orderBy('active', 'desc');

        if ($sortBy !== 'active') {
            $query->orderBy($sortBy, $sortOrder);
        }

        $plans = $query
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('plans/Index', [
            'plans' => $plans,
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
        Gate::authorize('create', Plan::class);

        return Inertia::render('plans/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Plan::class);

        try {
            $validated = $request->validate([
                'name' => ['required','string','max:255','unique:plans,name'],
                'slug' => ['required','string','max:255','unique:plans,slug'],
                'interval' => ['required', Rule::in(Plans::values())],
                'currency' => ['required','string','size:3'],
                'unit_amount' => ['required','min:0'],
                'stripe_product_id' => ['nullable','string','max:255'],
                'stripe_price_id' => ['nullable','string','max:255','unique:plans,stripe_price_id'],
                'features' => ['nullable','array'],
                'features.*' => ['string','max:255'],
                'active' => ['boolean'],
                'cadastral_units_number' => ['nullable','numeric'],
                'field_groups_number' => ['nullable','numeric'],
                'field_groups_max_area' => ['nullable','numeric'],
                'hide_plan' => ['bool', 'nullable']
            ]);

            $plan = Plan::create($validated);

            return redirect()->route('plans.show', $plan)
                ->with('success', __('ui.created_plan_success'));
        } catch (\Throwable $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        Gate::authorize('view', $plan);

        return Inertia::render('plans/Show', [
            'plan' => $plan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        Gate::authorize('update', $plan);

        return Inertia::render('plans/Edit', [
            'plan' => $plan,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        Gate::authorize('update', $plan);

        $validated = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('plans','name')->ignore($plan->id)],
            'slug' => ['required','string','max:255', Rule::unique('plans','slug')->ignore($plan->id)],
            'interval' => ['required', Rule::in(Plans::values())],
            'currency' => ['required','string','size:3'],
            'unit_amount' => ['required','min:0'],
            'stripe_product_id' => ['nullable','string','max:255'],
            'stripe_price_id' => ['nullable','string','max:255', Rule::unique('plans','stripe_price_id')->ignore($plan->id)],
            'features' => ['nullable','array'],
            'features.*' => ['string','max:255'],
            'active' => ['boolean'],
            'cadastral_units_number' => ['nullable','numeric'],
            'field_groups_number' => ['nullable','numeric'],
            'field_groups_max_area' => ['nullable','numeric'],
            'hide_plan' => ['bool', 'nullable']
        ]);

        $plan->update($validated);

        return redirect()->route('plans.index')
            ->with('success', __('ui.updated_plan_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        Gate::authorize('delete', $plan);

        try {
            DeleteStripeProductAndPriceAction::run($plan);
            return redirect()->route('plans.index')
                ->with('success', __('ui.deleted_plan_success'));
        } catch (Exception $e) {
            return redirect()->route('plans.index')
                ->with('error', $e->getMessage());
        }

    }
}
