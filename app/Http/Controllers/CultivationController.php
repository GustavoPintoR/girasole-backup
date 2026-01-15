<?php

namespace App\Http\Controllers;

use App\Models\Cultivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CultivationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cultivation::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'name';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name'];

        $query = Cultivation::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $cultivations = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('cultivations/Index', [
            'cultivations' => $cultivations,
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
        Gate::authorize('create', Cultivation::class);

        return Inertia::render('cultivations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Cultivation::class);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:cultivations,name',
                'description' => 'nullable|string|max:1000',
            ]);

            $cultivation = Cultivation::create($validated);

            return redirect()->route('cultivations.show', $cultivation)
                ->with('success', __('ui.cultivation_created'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cultivation $cultivation)
    {
        Gate::authorize('view', $cultivation);

        return Inertia::render('cultivations/Show', [
            'cultivation' => $cultivation,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cultivation $cultivation)
    {
        Gate::authorize('update', $cultivation);

        return Inertia::render('cultivations/Edit', [
            'cultivation' => $cultivation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cultivation $cultivation)
    {
        Gate::authorize('update', $cultivation);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cultivations,name,'.$cultivation->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $cultivation->update($validated);

        return redirect()->route('cultivations.index')
            ->with('success', __('Cultivation updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cultivation $cultivation)
    {
        Gate::authorize('delete', $cultivation);

        $cultivation->delete();

        return redirect()->route('cultivations.index')
            ->with('success', __('Cultivation deleted successfully.'));
    }
}
