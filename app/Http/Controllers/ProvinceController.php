<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Province::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,code,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'name';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name', 'code'];

        $query = Province::with('region');

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $provinces = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('provinces/Index', [
            'provinces' => $provinces,
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
        Gate::authorize('create', Province::class);

        $regions = Region::select('id', 'name', 'code')->get();

        return Inertia::render('provinces/Create', [
            'regions' => $regions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Province::class);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:provinces,name',
                'code' => 'required|string|max:2|unique:provinces,code',
                'region_id' => 'required|exists:regions,id',
            ]);

            $province = Province::create($validated);

            return redirect()->route('provinces.show', $province)
                ->with('success', __('ui.created_successfully', ['resource' => 'Province']));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Province $province)
    {
        Gate::authorize('view', $province);

        $province->load('region')->loadCount('cities');

        $cities = $province->cities()
            ->orderBy('name')
            ->get(['id', 'name', 'cadastral_code']);

        return Inertia::render('provinces/Show', [
            'province' => $province,
            'region' => $province->region,
            'cities' => $cities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Province $province)
    {
        Gate::authorize('update', $province);

        $regions = Region::select('id', 'name', 'code')->get();
        $cities = City::where('province_id', '!=', $province->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('provinces/Edit', [
            'province' => $province,
            'regions' => $regions,
            'cities' => $cities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Province $province)
    {
        Gate::authorize('update', $province);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:provinces,name,'.$province->id,
                'code' => 'required|string|max:2|unique:provinces,code,'.$province->id,
                'region_id' => 'required|exists:regions,id',
            ]);

            $province->update($validated);

            return redirect()->route('provinces.index')
                ->with('success', __('ui.updated_successfully', ['resource' => 'Province']));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Province $province)
    {
        Gate::authorize('delete', $province);

        $province->delete();

        return redirect()->route('provinces.index')
            ->with('success', __('ui.deleted_successfully', ['resource' => 'Province']));

    }

    public function available(Request $request, Province $province)
    {
        $search = (string) $request->query('search', '');

        $query = City::query()
            ->where('province_id', '!=', $province->id)
            ->when($search, fn ($q) => $q->where('name', 'like', '%'.str_replace('%', '\%', $search).'%'))
            ->orderBy('name')
            ->limit(100);

        return response()->json([
            'data' => $query->get(['id', 'name', 'cadastral_code', 'province_id']),
        ]);
    }

    public function attach(Request $request, Province $province)
    {

        $validated = $request->validate([
            'city_ids' => ['required', 'array', 'min:1'],
            'city_ids.*' => ['integer', Rule::exists('cities', 'id')],
        ]);

        DB::transaction(function () use ($validated, $province) {
            City::whereIn('id', $validated['city_ids'])->update([
                'province_id' => $province->id,
            ]);
        });

        return redirect()->route('provinces.show', $province->id)->with('success', 'Cities attached.');
    }
}
