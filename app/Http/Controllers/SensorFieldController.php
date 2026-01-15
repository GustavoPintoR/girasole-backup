<?php

namespace App\Http\Controllers;

use App\Models\SensorField;
use Illuminate\Http\Request;;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SensorFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', SensorField::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,label,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number', 10);
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name', 'label'];

        $query = SensorField::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $sensorFields = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('sensor-fields/Index', [
            'sensorFields' => $sensorFields,
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
        Gate::authorize('create', SensorField::class);
        //
        return Inertia::render('sensor-fields/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', SensorField::class);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:sensor_fields,name'],
            'label' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        SensorField::create($validated);

        return redirect()->route('sensor-fields.index')
            ->with('success', __('ui.model_created', ['model' => __('ui.sensor_field')]));
    }

    /**
     * Display the specified resource.
     */
    public function show(SensorField $sensorField): Response
    {
        Gate::authorize('view', $sensorField);
        //
        return Inertia::render('sensor-fields/Show', ['sensorField' => $sensorField]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SensorField $sensorField): Response
    {
        Gate::authorize('update', $sensorField);
        //
        return Inertia::render('sensor-fields/Edit', ['sensorField' => $sensorField]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SensorField $sensorField): RedirectResponse
    {
        Gate::authorize('update', $sensorField);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $sensorField->update($validated);

        return redirect()->route('sensor-fields.index')
            ->with('success', __('ui.model_updated', ['model' => __('ui.sensor_field')]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SensorField $sensorField): RedirectResponse
    {
        Gate::authorize('delete', $sensorField);

        $sensorField->delete();

        return redirect()->route('sensor-fields.index')
            ->with('success', __('ui.model_deleted', ['model' => __('ui.sensor_field')]));
    }
}
