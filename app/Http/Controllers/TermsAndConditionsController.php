<?php

namespace App\Http\Controllers;

use App\Models\TermsAndConditions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TermsAndConditionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', TermsAndConditions::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:version,is_active,active_at,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['version'];

        $query = TermsAndConditions::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $termsAndConditions = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('is_active', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('termsandconditions/Index', [
            'termsAndConditions' => $termsAndConditions,
            'sorting' => compact('sortBy', 'sortOrder'),
            'filtering' => [
                'search' => $search,
                'searchableColumns' => $searchableColumns,
            ],
        ]);
    }

    public function create()
    {
        Gate::authorize('create', TermsAndConditions::class);

        return Inertia::render('termsandconditions/Create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', TermsAndConditions::class);

        $validated = $request->validate([
            'version' => 'required|string|max:255|unique:terms_and_conditions',
            'description' => 'required|string',
            'summary' => 'required|string|max:1000',
            'is_active' => 'boolean',
            'active_at' => 'nullable|date',
        ]);

        if ($validated['is_active'] ?? false) {
            TermsAndConditions::where('is_active', true)->update(['is_active' => false]);
            $validated['active_at'] = $validated['active_at'] ?? now();
        }

        TermsAndConditions::create($validated);

        return redirect()->route('terms-and-conditions.index')
            ->with('success', 'Terms and conditions created successfully.');
    }

    public function show(TermsAndConditions $termsAndCondition)
    {
        Gate::authorize('view', $termsAndCondition);

        return Inertia::render('termsandconditions/Show', [
            'terms' => $termsAndCondition,
        ]);
    }

    public function edit(TermsAndConditions $termsAndCondition)
    {
        Gate::authorize('update', $termsAndCondition);

        return Inertia::render('termsandconditions/Edit', [
            'terms' => $termsAndCondition,
        ]);
    }

    public function update(Request $request, TermsAndConditions $termsAndCondition)
    {
        Gate::authorize('update', $termsAndCondition);

        $validated = $request->validate([
            'version' => 'required|string|max:255|unique:terms_and_conditions,version,'.$termsAndCondition->id,
            'description' => 'required|string',
            'summary' => 'required|string|max:1000',
            'is_active' => 'boolean',
            'active_at' => 'nullable|date',
        ]);

        // If setting as active, deactivate all others
        if (($validated['is_active'] ?? false) && ! $termsAndCondition->is_active) {
            TermsAndConditions::where('is_active', true)->update(['is_active' => false]);
            $validated['active_at'] = $validated['active_at'] ?? now();

        } elseif ($termsAndCondition->is_active) {
            unset($validated['is_active']);
        }

        $termsAndCondition->update($validated);

        return redirect()->route('terms-and-conditions.index')
            ->with('success', 'Terms and conditions updated successfully.');
    }

    public function destroy(TermsAndConditions $termsAndCondition)
    {
        Gate::authorize('delete', $termsAndCondition);

        if ($termsAndCondition->is_active) {
            return back()->with('error', 'Cannot delete active terms and conditions.');
        }

        $termsAndCondition->delete();

        return back()->with('success', 'Terms and conditions deleted successfully.');
    }

    public function activate(Request $request)
    {
        $termsAndCondition = TermsAndConditions::findOrFail($request->id);
        
        // Deactivate all others
        TermsAndConditions::where('is_active', true)->update(['is_active' => false]);

        $termsAndCondition->update([
            'is_active' => true,
            'active_at' => now(),
        ]);

        return back()->with('success', 'Terms and conditions activated successfully.');
    }

    public function accept()
    {
        $termsAndConditions = TermsAndConditions::where('is_active', true)->first();

        if (! $termsAndConditions) {
            return to_route('dashboard');
        }

        return Inertia::render('termsandconditions/Accept', compact('termsAndConditions'));
    }

    public function confirmAccept(Request $request)
    {
        $termsAccepted = TermsAndConditions::findOrFail($request->id);
        $activeTerms = TermsAndConditions::where('is_active', true)->firstOrFail();

        if ($termsAccepted->id !== $activeTerms->id) {
            return back()->with('error', 'The terms and conditions you have accepted do not match the latest terms and conditions.');
        }

        $user = $request->user();
        $user->update([
            'accepted_at' => now(),
            'terms_and_conditions_id' => $termsAccepted->id,
        ]);

        return to_route('dashboard');
    }
}
