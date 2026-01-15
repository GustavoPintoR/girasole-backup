<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Region::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'name';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name'];

        $query = Region::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $regions = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('regions/Index', [
            'regions' => $regions,
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
        Gate::authorize('create', Region::class);

        return Inertia::render('regions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Region::class);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:regions,name',
                'code' => 'required|string|max:2|unique:regions,code',
            ]);

            $region = Region::create($validated);

            return redirect()->route('regions.show', $region)
                ->with('success', 'Region created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        Gate::authorize('view', $region);

        $region->loadCount(['provinces', 'cities']);

        // Provinces with cities count
        $provinces = $region->provinces()
            ->withCount('cities')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Cities directly related to region
        $cities = $region->cities()
            ->orderBy('name')
            ->get(['id', 'name', 'cadastral_code', 'province_id']);

        return Inertia::render('regions/Show', [
            'region' => $region,
            'provinces' => $provinces,
            'cities' => $cities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Region $region)
    {
        Gate::authorize('update', $region);

        return Inertia::render('regions/Edit', [
            'region' => $region,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Region $region)
    {
        Gate::authorize('update', $region);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:regions,code,'.$region->id,
        ]);

        $region->update($validated);

        return redirect()->route('regions.index')
            ->with('success', __('ui.updated_region_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Region $region)
    {
        Gate::authorize('delete', $region);

        $region->delete();

        return redirect()->route('regions.index')
            ->with('success', __('ui.deleted_region_success'));

    }
}
