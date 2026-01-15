<?php

namespace App\Http\Controllers;

use App\Models\PostalCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class PostalCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', PostalCode::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:code,zone,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'code';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['code'];

        $query = PostalCode::query()->with('cities');

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $postalCodes = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('postal-codes/Index', [
            'postalCodes' => $postalCodes,
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
        Gate::authorize('create', PostalCode::class);

        return Inertia::render('postal-codes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', PostalCode::class);

        $validator = Validator::make($request->all(), [
            'postal_code' => 'required|string|size:5',
            'cities' => 'required|array|min:1',
            'cities.*.city_id' => 'required|exists:cities,id',
            'cities.*.zone' => 'nullable|string|max:255',
            'cities.*.notes' => 'nullable|string|max:1000',
        ]);

        $validated = $validator->validate();

        $postalCode = PostalCode::create([
            'code' => $validated['postal_code'],
        ]);

        $pivotData = collect($validated['cities'])->mapWithKeys(function ($city) {
            return [
                $city['city_id'] => [
                    'zone' => $city['zone'],
                    'notes' => $city['notes'],
                ],
            ];
        })->toArray();

        $postalCode->cities()->sync($pivotData);

        return redirect()->route('postal-codes.index')
            ->with('success', 'Postal code created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PostalCode $postalCode)
    {
        Gate::authorize('view', $postalCode);

        return Inertia::render('postal-codes/Show', [
            'postalCode' => $postalCode->load('cities.province.region'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PostalCode $postalCode)
    {
        Gate::authorize('update', $postalCode);

        return Inertia::render('postal-codes/Edit', [
            'postalCode' => $postalCode->load('cities.province.region'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PostalCode $postalCode)
    {
        Gate::authorize('update', $postalCode);

        $validator = Validator::make($request->all(), [
            'postal_code' => 'required|string|size:5',
            'cities' => 'required|array|min:1',
            'cities.*.city_id' => 'required|exists:cities,id',
            'cities.*.zone' => 'nullable|string|max:255',
            'cities.*.notes' => 'nullable|string|max:1000',
        ]);

        $validated = $validator->validate();

        $postalCode->update([
            'code' => $validated['postal_code'],
        ]);

        $pivotData = collect($validated['cities'])->mapWithKeys(function ($city) {
            return [
                $city['city_id'] => [
                    'zone' => $city['zone'] ?? null,
                    'notes' => $city['notes'] ?? null,
                ],
            ];
        })->toArray();

        $postalCode->cities()->sync($pivotData);

        $postalCode->cities()->sync($pivotData);

        return redirect()->route('postal-codes.index')
            ->with('success', 'Postal code updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PostalCode $postalCode)
    {
        Gate::authorize('delete', $postalCode);

        $postalCode->delete();

        return back()->with('success', 'Postal code deleted successfully.');
    }
}
