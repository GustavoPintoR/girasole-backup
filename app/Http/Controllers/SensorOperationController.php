<?php

namespace App\Http\Controllers;

use App\Models\SensorOperation;
use Illuminate\Http\Request;;

use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SensorOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', SensorOperation::class);

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

        $query = SensorOperation::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $sensorOperations = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('sensor-operations/Index', [
            'sensorOperations' => $sensorOperations,
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
        Gate::authorize('create', SensorOperation::class);
        //
        return Inertia::render('sensor-operations/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', SensorOperation::class);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:sensor_operations,name'],
            'label' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        SensorOperation::create($validated);

        return redirect()
            ->route('sensor-operations.index')
            ->with('success', __('ui.model_created', ['model' => __('ui.sensor_operation')]));
    }

    /**
     * Display the specified resource.
     */
    public function show(SensorOperation $sensorOperation): Response
    {
        Gate::authorize('view', $sensorOperation);
        //
        return Inertia::render('sensor-operations/Show', ['sensorOperation' => $sensorOperation]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SensorOperation $sensorOperation): Response
    {
        Gate::authorize('update', $sensorOperation);
        //
        return Inertia::render('sensor-operations/Edit', ['sensorOperation' => $sensorOperation]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SensorOperation $sensorOperation): RedirectResponse
    {
        Gate::authorize('update', $sensorOperation);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated = $validator->validate();

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $sensorOperation->update($validated);

        return redirect()->route('sensor-operations.index')
            ->with('success', __('ui.model_updated', ['model' => __('ui.sensor_operation')]));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SensorOperation $sensorOperation): RedirectResponse
    {
        Gate::authorize('delete', $sensorOperation);

        $sensorOperation->delete();

        return redirect()->route('sensor-operations.index')
            ->with('success', __('ui.model_deleted', ['model' => __('ui.sensor_operation')]));
    }
}
