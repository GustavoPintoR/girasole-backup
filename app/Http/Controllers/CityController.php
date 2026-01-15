<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', City::class);

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

        $searchableColumns = ['name', 'cadastral_code'];

        $query = City::with(['province', 'region']);

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $cities = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('cities/Index', [
            'cities' => $cities,
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
        Gate::authorize('create', City::class);

        $regions = Region::orderBy('name')->get();
        $provinces = Province::with('region')->orderBy('name')->get();

        return Inertia::render('cities/Create', [
            'regions' => $regions,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', City::class);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:cities,name',
                'cadastral_code' => 'required|string|max:4|unique:cities,cadastral_code',
                'region_id' => 'required|exists:regions,id',
                'province_id' => 'required|exists:provinces,id',
            ]);

            $city = City::create($validated);

            return redirect()->route('cities.show', $city)
                ->with('success', __('ui.city_created'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(City $city)
    {
        Gate::authorize('view', $city);

        $city->load(['province', 'region']);

        return Inertia::render('cities/Show', [
            'city' => $city,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(City $city)
    {
        Gate::authorize('update', $city);

        $regions = Region::get(['id', 'name', 'code']);
        $provinces = Province::with('region')->get();

        return Inertia::render('cities/Edit', [
            'city' => $city,
            'regions' => $regions,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city)
    {
        Gate::authorize('update', $city);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name,'.$city->id,
            'cadastral_code' => 'required|string|max:4|unique:cities,cadastral_code,'.$city->id,
            'region_id' => 'required|exists:regions,id',
            'province_id' => 'required|exists:provinces,id',
        ]);

        $city->update($validated);

        return redirect()->route('cities.index')
            ->with('success', __('City updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city)
    {
        Gate::authorize('delete', $city);

        $city->delete();

        return redirect()->route('cities.index')
            ->with('success', __('City deleted successfully.'));

    }
}
