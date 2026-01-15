<?php

namespace App\Http\Controllers;

use App\Models\Cultivar;
use App\Models\Cultivation;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CultivarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cultivar::class);

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

        $searchableColumns = ['name', 'description'];

        $query = Cultivar::with('cultivation');

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $cultivars = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('cultivars/Index', [
            'cultivars' => $cultivars,
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
        Gate::authorize('create', Cultivar::class);

        $cultivations = Cultivation::orderBy('name')->get();
        $customFields = CustomField::forModel(Cultivar::class);

        return Inertia::render('cultivars/Create', [
            'cultivations' => $cultivations,
            'customFields' => $customFields,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Cultivar::class);

        try {
            $validated = $request->validate(array_merge([
                'name' => 'required|string|max:255|unique:cultivars,name',
                'description' => 'nullable|string|max:1000',
                'cultivation_id' => 'required|exists:cultivations,id',
            ], CustomField::getValidationRules(Cultivar::class)));

            $cultivar = Cultivar::create($validated);
            $cultivar->syncCustomFields($validated['custom_fields'] ?? []);

            return redirect()->route('cultivars.show', $cultivar)
                ->with('success', __('ui.cultivar_created'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cultivar $cultivar)
    {
        Gate::authorize('view', $cultivar);

        $cultivar->load('cultivation');
        $customFieldValues = $cultivar->getCustomFieldsArray();

        return Inertia::render('cultivars/Show', [
            'cultivar' => $cultivar,
            'customFieldValues' => $customFieldValues,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cultivar $cultivar)
    {
        Gate::authorize('update', $cultivar);

        $cultivar->load('cultivation');
        $cultivations = Cultivation::orderBy('name')->get();
        $customFields = CustomField::forModel(Cultivar::class);
        $customFieldValues = $cultivar->getCustomFieldValuesOnly();

        return Inertia::render('cultivars/Edit', [
            'cultivar' => $cultivar,
            'cultivations' => $cultivations,
            'customFields' => $customFields,
            'customFieldValues' => $customFieldValues,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cultivar $cultivar)
    {
        Gate::authorize('update', $cultivar);

        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255|unique:cultivars,name,'.$cultivar->id,
            'description' => 'nullable|string|max:1000',
            'cultivation_id' => 'required|exists:cultivations,id',
        ], CustomField::getValidationRules(Cultivar::class)));

        $cultivar->update($validated);
        $cultivar->syncCustomFields($validated['custom_fields'] ?? []);

        return redirect()->route('cultivars.index')
            ->with('success', __('Cultivar updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cultivar $cultivar)
    {
        Gate::authorize('delete', $cultivar);

        $cultivar->delete();

        return redirect()->route('cultivars.index')
            ->with('success', __('Cultivar deleted successfully.'));
    }
}
