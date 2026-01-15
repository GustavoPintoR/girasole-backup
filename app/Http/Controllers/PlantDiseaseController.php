<?php

namespace App\Http\Controllers;

use App\Models\Cultivation;
use App\Models\PlantDisease;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PlantDiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', PlantDisease::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,description,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number', 10);
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name', 'description'];

        $query = PlantDisease::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $plantDiseases = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('plant-diseases/Index', [
            'plantDiseases' => $plantDiseases,
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
    public function create(): Response
    {
        Gate::authorize('create', PlantDisease::class);

        $cultivations = Cultivation::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('plant-diseases/Create', [
            'cultivations' => $cultivations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', PlantDisease::class);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:plant_diseases,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cultivation_ids' => ['nullable', 'array'],
            'cultivation_ids.*' => ['integer', 'exists:cultivations,id'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $plantDisease = PlantDisease::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['cultivation_ids'])) {
            $plantDisease->cultivations()->sync($validated['cultivation_ids']);
        }

        return redirect()
            ->route('plant-diseases.index', $plantDisease)
            ->with('success', __('ui.model_created', ['model' => __('ui.plant_disease')]));
    }

    /**
     * Display the specified resource.
     */
    public function show(PlantDisease $plantDisease): Response
    {
        Gate::authorize('view', $plantDisease);

        $plantDisease->loadMissing('cultivations:id,name');

        return Inertia::render('plant-diseases/Show', [
            'plantDisease' => $plantDisease,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlantDisease $plantDisease): Response
    {
        Gate::authorize('update', $plantDisease);

        $plantDisease->loadMissing('cultivations:id,name');

        $cultivations = Cultivation::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('plant-diseases/Edit', [
            'plantDisease' => $plantDisease,
            'cultivations' => $cultivations,
            'selectedCultivationIds' => $plantDisease->cultivations->pluck('id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PlantDisease $plantDisease): RedirectResponse
    {
        Gate::authorize('update', $plantDisease);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cultivation_ids' => ['nullable', 'array'],
            'cultivation_ids.*' => ['integer', 'exists:cultivations,id'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $plantDisease->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $plantDisease->cultivations()->sync($validated['cultivation_ids'] ?? []);

        return redirect()
            ->route('plant-diseases.index')
            ->with('success', __('ui.model_updated', ['model' => __('ui.plant_disease')]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlantDisease $plantDisease): RedirectResponse
    {
        Gate::authorize('delete', $plantDisease);

        $plantDisease->delete();

        return redirect()
            ->route('plant-diseases.index')
            ->with('success', __('ui.model_deleted', ['model' => __('ui.plant_disease')]));
    }
}
