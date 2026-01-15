<?php

namespace App\Http\Controllers;

use App\Models\Irrigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class IrrigationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Irrigation::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'type';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['type'];

        $query = Irrigation::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $irrigations = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('irrigations/Index', [
            'irrigations' => $irrigations,
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
        Gate::authorize('create', Irrigation::class);

        return Inertia::render('irrigations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Irrigation::class);

        try {
            $validated = $request->validate([
                'type' => 'required|string|max:255|unique:irrigations,type',
                'description' => 'nullable|string|max:1000',
            ]);

            $irrigation = Irrigation::create($validated);

            return redirect()->route('irrigations.show', $irrigation)
                ->with('success', __('ui.irrigation_created'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Irrigation $irrigation)
    {
        Gate::authorize('view', $irrigation);

        return Inertia::render('irrigations/Show', [
            'irrigation' => $irrigation,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Irrigation $irrigation)
    {
        Gate::authorize('update', $irrigation);

        return Inertia::render('irrigations/Edit', [
            'irrigation' => $irrigation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Irrigation $irrigation)
    {
        Gate::authorize('update', $irrigation);

        $validated = $request->validate([
            'type' => 'required|string|max:255|unique:irrigations,type,'.$irrigation->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $irrigation->update($validated);

        return redirect()->route('irrigations.index')
            ->with('success', __('Irrigation updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Irrigation $irrigation)
    {
        Gate::authorize('delete', $irrigation);

        $irrigation->delete();

        return redirect()->route('irrigations.index')
            ->with('success', __('Irrigation deleted successfully.'));
    }
}
