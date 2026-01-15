<?php

namespace App\Http\Controllers;

use App\Actions\FetchSensors;
use App\Services\InfluxDBService;
use App\Helpers\PlanHelper;
use App\Models\CadastralGroup;
use App\Models\Sensor;
use App\Models\SensorField;
use App\Models\SensorType;
use App\Models\User;
use App\Models\Company;
use App\Rules\PointWithinCadastralGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class SensorController extends Controller
{
    public function __construct(protected InfluxDBService $influxDBService) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Sensor::class);

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

        $searchableColumns = ['sensors.name', 'sensors.firmware', 'sensors.serial_number'];

        $query = Sensor::query()->with(['owner', 'sensorType', 'cadastralGroup', 'company']);

        if (!$request->user()->isSuperAdmin()) {
            $query->visibleTo($request->user());
        }

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%' . $search . '%');
                }
            });
        }

        switch ($sortBy) {
            case 'type':
                $query->join('sensor_types', 'sensors.sensor_type_id', '=', 'sensor_types.id')
                    ->orderBy('sensor_types.name', $sortOrder);
                break;

            default:
                $query->orderBy("sensors.$sortBy", $sortOrder);
                break;
        }

        $query->orderBy('sensors.created_at', 'desc');

        $sensors = $query
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('sensors/Index', [
            'sensors' => $sensors,
            'sorting' => compact('sortBy', 'sortOrder'),
            'filtering' => [
                'search' => $search,
                'searchableColumns' => $searchableColumns,
            ],
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Sensor::class);

        $users = User::orderBy('first_name')->get();

        $types = SensorType::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $isAdmin = auth()->user()->isSuperAdmin();
        $userCompanies = null;
        if (!$isAdmin) {
            $userCompanies = Company::whereHas('users', function ($q) {
                $q->where('id', auth()->id());
            })->select('id', 'name', 'description')->orderBy('name')->get();
        }

        $ownedCompanyIds = Company::where('owner_id', auth()->id())->pluck('id')->toArray();

        return Inertia::render('sensors/Create', [
            'users' => $users,
            'types' => $types,
            'companies' => $isAdmin ? Company::select('id', 'name', 'description')->orderBy('name')->get() : $userCompanies,
            'ownedCompanyIds' => $ownedCompanyIds,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Sensor::class);

        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'sensor_type_id' => 'required|exists:sensor_types,id',
            'cadastral_group_id' => 'nullable|exists:cadastral_groups,id',
            'firmware' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'iccid' => 'required|string|max:100',
            'transmission_module_identification' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'urn' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'owner_id' => 'required|exists:users,id',
        ]);

        $statusMessage = __('ui.sensor_created');

        if ($validated['cadastral_group_id']) {
            $cadastralGroup = CadastralGroup::find($validated['cadastral_group_id']);
            if ($validated['latitude'] && $validated['longitude'] && !$cadastralGroup->pointIsWithinGroup($validated['latitude'], $validated['longitude'])) {
                $validated['longitude'] = $cadastralGroup->centroid->getX();
                $validated['latitude'] = $cadastralGroup->centroid->getY();
                $statusMessage = __('ui.sensor_created_but_not_in_cadastral_group');
            }
        } else {
            // $validated['longitude'] = null;
            // $validated['latitude'] = null; // keeping if set, valid or not
            $statusMessage = __('ui.sensor_created_without_cadastral_group');
        }

        $user = User::whereId($validated['owner_id'])->first();
        if(!PlanHelper::checkSubscription($user)){
            return redirect()->back()
                ->with('error', __('ui.sensor_user_not_subscribed'));
        }

        if (PlanHelper::isSensorLimitReached($user)){
            return redirect()->back()
                ->with('error', __('ui.maximum_sensors_reached'));
        }

        $companyId = $validated['company_id'] ?? null;
        if ($companyId) {
            $isCompanyOwner = Company::where('id', $companyId)
                ->where('owner_id', auth()->id())
                ->exists();

            if (!auth()->user()->isSuperAdmin() && !$isCompanyOwner) {
                return redirect()->back()->withErrors(['company_id' => __('ui.company_selected_not_allowed')])->withInput();
            }
        }

        // Ensure the selected company (if any) is valid for the chosen owner
        if ($companyId && isset($validated['owner_id'])) {
            $ownerId = $validated['owner_id'];
            $company = Company::with('users')->find($companyId);
            if (!$company) {
                return redirect()->back()->withErrors(['company_id' => __('ui.selected_company_not_found')])->withInput();
            }

            // Check if owner_id is null (orphaned company) or if owner/member matches
            $isOwner = $company->owner_id !== null && $company->owner_id === $ownerId;
            $isMember = $company->users->pluck('id')->contains($ownerId);

            if (!$isOwner && !$isMember) {
                return redirect()->back()->withErrors(['company_id' => __('ui.selected_company_user_not_associated')])->withInput();
            }
        }

        $sensor = Sensor::create($validated);

        return redirect()->route('sensors.show', $sensor)
            ->with('success', $statusMessage);
    }

    public function show(Request $request, Sensor $sensor)
    {
        Gate::authorize('view', $sensor);

        $sensor->load(['owner', 'sensorType', 'cadastralGroup', 'company']);

        $time = $request->input('time', '-7d');
        $op = $request->input('op', '');
        $field = $request->input('field', '');

        // Defaults
        if (!$op && $sensor->urn) {
            $ops = $this->influxDBService->getDistinctOps($sensor->urn, $time);
            if (!empty($ops)) {
                $sensorOps = $sensor->sensorType?->sensorOperations
                    ->whereIn('label', $ops)
                    ->sortBy('name')
                    ->values();

                if ($sensorOps->isNotEmpty()) {
                    $op = $sensorOps->first()->label;
                }
            }
        }

        if (!$field) {
            $field = 'CH';
        }

        $data = ['chartData' => [], 'categories' => ['value']];
        $errors = [];

        if ($sensor->urn && $op && $field) {
            try {
                $data = $this->influxDBService->getChartDataByFilters($sensor->urn, $op, (int) $time, $field, $sensor->firmware);
            } catch (\Exception $e) {
                $errors['chart'] = 'Failed to fetch chart data';
            }
        }

        $sensorField = SensorField::where('label', $field)->first();
        $selectedField = $sensorField ? $sensorField->label : $field;

        return Inertia::render('sensors/Show', [
            'sensor' => $sensor,
            'chartData' => $data['chartData'],
            'categories' => $data['categories'],
            'selectedTime' => $time,
            'selectedOp' => $op,
            'selectedField' => $selectedField,
            'errors' => $errors,
        ]);
    }

    public function edit(Sensor $sensor)
    {
        Gate::authorize('update', $sensor);

        $users = User::orderBy('first_name')->get();
        $sensor->load(['owner', 'sensorType', 'cadastralGroup', 'company']);
        $types = SensorType::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $isAdmin = auth()->user()->isSuperAdmin();
        $userCompanies = null;
        if (!$isAdmin) {
            $userCompanies = Company::whereHas('users', function ($q) {
                $q->where('id', auth()->id());
            })->select('id', 'name', 'description')->orderBy('name')->get();
        }

        $ownedCompanyIds = Company::where('owner_id', auth()->id())->pluck('id')->toArray();

        return Inertia::render('sensors/Edit', [
            'sensor' => $sensor,
            'users' => $users,
            'types' => $types,
            'companies' => $isAdmin ? Company::select('id', 'name', 'description')->orderBy('name')->get() : $userCompanies,
            'ownedCompanyIds' => $ownedCompanyIds,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function update(Request $request, Sensor $sensor)
    {
        Gate::authorize('update', $sensor);

        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'sensor_type_id' => 'required|exists:sensor_types,id',
            'cadastral_group_id' => 'nullable|exists:cadastral_groups,id',
            'firmware' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'iccid' => 'required|string|max:100',
            'transmission_module_identification' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'urn' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'owner_id' => 'required|exists:users,id',
        ]);

        $statusMessage = __('ui.sensor_updated');

        if ($validated['cadastral_group_id']) {
            $cadastralGroup = CadastralGroup::find($validated['cadastral_group_id']);
            if ($validated['latitude'] && $validated['longitude'] && !$cadastralGroup->pointIsWithinGroup($validated['latitude'], $validated['longitude'])) {
                $validated['longitude'] = $cadastralGroup->centroid->getX();
                $validated['latitude'] = $cadastralGroup->centroid->getY();
                $statusMessage = __('ui.sensor_updated_but_not_in_cadastral_group');
            }
        } else {
            // $validated['longitude'] = null;
            // $validated['latitude'] = null; // keeping if set, valid or not
            $statusMessage = __('ui.sensor_updated_without_cadastral_group');
        }

        $user = User::whereId($validated['owner_id'])->first();
        if(!PlanHelper::checkSubscription($user)){
            return redirect()->back()
                ->with('error', __('ui.sensor_user_not_subscribed'));
        }

        if (PlanHelper::isSensorLimitReached($user)){
            return redirect()->back()
                ->with('error', __('ui.maximum_sensors_reached'));
        }

        $companyId = $validated['company_id'] ?? null;
        if ($companyId) {
            $isCompanyOwner = Company::where('id', $companyId)
                ->where('owner_id', auth()->id())
                ->exists();

            if (!auth()->user()->isSuperAdmin() && !$isCompanyOwner) {
                return redirect()->back()->withErrors(['company_id' => 'You are not allowed to assign this company.'])->withInput();
            }
        }

        // Ensure the selected company (if any) is valid for the chosen owner
        if ($companyId && isset($validated['owner_id'])) {
            $ownerId = $validated['owner_id'];
            $company = Company::with('users')->find($companyId);
            if (!$company) {
                return redirect()->back()->withErrors(['company_id' => 'Selected company not found.'])->withInput();
            }

            // Check if owner_id is null (orphaned company) or if owner/member matches
            $isOwner = $company->owner_id !== null && $company->owner_id === $ownerId;
            $isMember = $company->users->pluck('id')->contains($ownerId);

            if (!$isOwner && !$isMember) {
                return redirect()->back()->withErrors(['company_id' => 'Selected company is not associated with the selected owner.'])->withInput();
            }
        }

        $sensor->update($validated);

        return redirect()->route('sensors.show', $sensor)
            ->with('success', $statusMessage);
    }

    public function destroy(Sensor $sensor)
    {
        Gate::authorize('delete', $sensor);

        $sensor->delete();

        return redirect()->route('sensors.index')
            ->with('success', __('ui.sensor_deleted'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sync(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'range' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        FetchSensors::run($validated['range']);

        return redirect()->route('sensors.index')
            ->with('success', __('ui.sensor_sync_in_progress'));
    }
}
