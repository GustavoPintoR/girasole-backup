<?php

namespace App\Http\Controllers;

use App\Models\Cultivation;
use App\Models\SensorOperation;
use App\Models\SensorType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SensorTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', SensorType::class);

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

        $query = SensorType::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%' . $search . '%');
                }
            });
        }

        $sensorTypes = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('sensor-types/Index', [
            'sensorTypes' => $sensorTypes,
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
        Gate::authorize('create', SensorType::class);
        $operations = SensorOperation::query()
            ->orderBy('label')
            ->get(['id', 'label']);

        return Inertia::render('sensor-types/Create', [
            'operations' => $operations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', SensorType::class);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:sensor_types,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'operation_ids' => ['nullable', 'array'],
            'operation_ids.*' => ['integer', 'exists:sensor_operations,id'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $sensorType = SensorType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['operation_ids'])) {
            $sensorType->sensorOperations()->sync($validated['operation_ids']);
        }

        return redirect()->route('sensor-types.index')
            ->with('success', __('ui.model_created', ['model' => __('ui.sensor_type')]));
    }

    /**
     * Display the specified resource.
     */
    public function show(SensorType $sensorType): Response
    {
        Gate::authorize('view', $sensorType);

        $sensorType->loadMissing('sensorOperations:id,label');

        return Inertia::render('sensor-types/Show', [
            'sensorType' => $sensorType,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SensorType $sensorType): Response
    {
        Gate::authorize('update', $sensorType);

        $operations = SensorOperation::query()
            ->orderBy('label')
            ->get(['id', 'label']);

        return Inertia::render('sensor-types/Edit', [
            'sensorType' => $sensorType,
            'operations' => $operations,
            'selectedOperationsIds' => $sensorType->sensorOperations->pluck('id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SensorType $sensorType): RedirectResponse
    {
        Gate::authorize('update', $sensorType);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'operation_ids' => ['nullable', 'array'],
            'operation_ids.*' => ['integer', 'exists:sensor_operations,id'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $sensorType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['operation_ids'])) {
            $sensorType->sensorOperations()->sync($validated['operation_ids']);
        }

        return redirect()
            ->route('sensor-types.index')
            ->with('success', __('ui.model_updated', ['model' => __('ui.sensor_type')]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SensorType $sensorType): RedirectResponse
    {
        Gate::authorize('delete', $sensorType);

        $sensorType->delete();

        return redirect()->route('sensor-types.index')
            ->with('success', __('ui.model_deleted', ['model' => __('ui.sensor_type')]));
    }
}
