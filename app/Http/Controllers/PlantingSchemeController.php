<?php

namespace App\Http\Controllers;

use App\Enums\Pattern;
use App\Models\PlantingScheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PlantingSchemeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', PlantingScheme::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,description,row_spacing,plant_spacing,pattern,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name', 'description', 'row_spacing', 'plant_spacing', 'pattern'];

        $query = PlantingScheme::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $plantingSchemes = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('planting-schemes/Index', [
            'plantingSchemes' => $plantingSchemes,
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
        Gate::authorize('create', PlantingScheme::class);

        $patterns = array_map(fn ($pattern) => [
            'id' => $pattern->value,
            'name' => $pattern->value,
        ], Pattern::cases());

        return Inertia::render('planting-schemes/Create', compact('patterns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', PlantingScheme::class);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'row_spacing' => ['required', 'numeric', 'min:0'],
            'plant_spacing' => ['required', 'numeric', 'min:0'],
            'pattern' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        PlantingScheme::create($data);

        return redirect()
            ->route('planting-schemes.index')
            ->with('success', __('ui.model_created', ['model' => __('ui.planting_scheme')]));
    }

    /**
     * Display the specified resource.
     */
    public function show(PlantingScheme $plantingScheme): Response
    {
        Gate::authorize('view', $plantingScheme);

        //
        return Inertia::render('planting-schemes/Show', ['plantingScheme' => $plantingScheme]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlantingScheme $plantingScheme): Response
    {
        Gate::authorize('update', $plantingScheme);

        $patterns = array_map(fn ($pattern) => [
            'id' => $pattern->value,
            'name' => $pattern->value,
        ], Pattern::cases());

        return Inertia::render('planting-schemes/Edit', compact('plantingScheme', 'patterns'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PlantingScheme $plantingScheme): RedirectResponse
    {
        Gate::authorize('update', $plantingScheme);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'row_spacing' => ['required', 'numeric', 'min:0'],
            'plant_spacing' => ['required', 'numeric', 'min:0'],
            'pattern' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        $plantingScheme->update($data);

        return redirect()
            ->route('planting-schemes.index')
            ->with('success', __('ui.model_updated', ['model' => __('ui.planting_scheme')]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlantingScheme $plantingScheme): RedirectResponse
    {
        Gate::authorize('delete', $plantingScheme);

        $plantingScheme->delete();

        return redirect()->route('planting-schemes.index')
            ->with('success', __('ui.model_deleted', ['model' => __('ui.planting_scheme')]));

    }
}
