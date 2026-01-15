<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Region;
use App\Helpers\PlanHelper;
use Illuminate\Http\Request;
use App\Models\CadastralUnit;
use Illuminate\Support\Facades\Gate;
use App\Services\CadastralUnitService;
use App\Http\Requests\CadastralUnit\CadastralUnitIndexRequest;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ParseCartographyJob;
use App\Models\City;

class CadastralUnitController extends Controller
{
    public function __construct(
        private CadastralUnitService $cadastralUnitService
    ) {}


    /**
     * Display a listing of the resource.
     */
    public function index(CadastralUnitIndexRequest $request)
    {
        Gate::authorize('viewAny', CadastralUnit::class);

        $validated = $request->validated();

        $cadastralUnits = $this->cadastralUnitService->getPaginatedUnits(
            auth()->id(),
            auth()->user()->isSuperAdmin(),
            $validated
        );

        $allCadastralUnitsForMap = $this->cadastralUnitService->getUnitsForMap(
            auth()->id(),
            auth()->user()->isSuperAdmin(),
        );

        return Inertia::render('cadastral-units/Index', [
            'cadastralUnits' => $cadastralUnits,
            'allCadastralUnitsForMap' => $allCadastralUnitsForMap,
            'sorting' => [
                'sortBy' => $validated['sort_by'] ?? null,
                'sortOrder' => $validated['sort_order'] ?? 'asc',
            ],
            'filtering' => [
                'search' => $validated['search'] ?? null,
                'searchableColumns' => array_merge(
                    ['city_name', 'province_name', 'region_name'],
                    CadastralUnitService::SEARCHABLE_COLUMNS
                ),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', CadastralUnit::class);

        $users = null;
        if (auth()->user()->isSuperAdmin()) {
            $users = User::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']);
        }

        return Inertia::render('cadastral-units/Create', [
            'regions' => Region::orderBy('name')->get(['id', 'name', 'code']),
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', CadastralUnit::class);

        $rules = [
            'city_id' => 'required|exists:cities,id',
            'section' => 'nullable|string|max:1',
            'sheet' => 'required|string|max:6',
            'parcel' => 'required|string|max:10',
            'sub' => 'nullable|string|max:4',
            'category' => 'nullable|string|max:3',
            'class' => 'nullable|string|max:2',
            'cadastral_area' => 'nullable|numeric',
            'dominical_income' => 'nullable|numeric',
            'agrarian_income' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['user_id'] = 'nullable|exists:users,id';
        }

        $validated = $request->validate($rules);

        $userId = Auth::user()->id;
        if (auth()->user()->isSuperAdmin() && !empty($validated['user_id'])) {
            $userId = $validated['user_id'];
        }

        $user = User::with('cadastralUnits')->whereId($userId)->first();
        if (PlanHelper::isCadastralUnitLimitReached($user)) {
            return redirect()->back()
                ->with('error', __('ui.maximum_cadastral_units_reached'));
        }

        $city = City::with('province.region')->find($validated['city_id']);
        $region = strtoupper($city->province->region->name);
        $province = strtoupper($city->province->code);
        $cityCode = $city->cadastral_code;
        $cityId = $city->id;
        $sheet = $validated['sheet'];
        $parcel = $validated['parcel'];

        ParseCartographyJob::dispatch($region, $province, $cityCode, $cityId, $sheet, $parcel, $userId);

        return redirect()->route('cadastral-units.index')
            ->with('success', __('ui.cadastral_unit_queued'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CadastralUnit $cadastralUnit)
    {
        Gate::authorize('update', $cadastralUnit);

        $cadastralUnit->load(['city.province.region', 'cadastralGroup']);

        $users = null;
        if (auth()->user()->isSuperAdmin()) {
            $users = User::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']);
        }

        return Inertia::render('cadastral-units/Edit', [
            'cadastralUnit' => $cadastralUnit,
            'regions' => Region::orderBy('name')->get(['id', 'name', 'code']),
            'isGrouped' => $cadastralUnit->isGrouped(),
            'cadastralGroup' => $cadastralUnit->cadastralGroup ? [
                'id' => $cadastralUnit->cadastralGroup->id,
                'name' => $cadastralUnit->cadastralGroup->name,
            ] : null,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cadastralUnit = CadastralUnit::findOrFail($id);

        Gate::authorize('update', $cadastralUnit);

        if ($cadastralUnit->isGrouped()) {
            return back()->withErrors(['general' => __('ui.cadastral_unit_locked_description')]);
        }

        $rules = [
            'city_id' => 'required|exists:cities,id',
            'section' => 'nullable|string|max:1',
            'sheet' => 'required|string|max:6',
            'parcel' => 'required|string|max:10',
            'sub' => 'nullable|string|max:4',
            'category' => 'nullable|string|max:3',
            'class' => 'nullable|string|max:2',
            'cadastral_area' => 'nullable|numeric',
            'dominical_income' => 'nullable|numeric',
            'agrarian_income' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['user_id'] = 'nullable|exists:users,id';
        }

        $validated = $request->validate($rules);

        $sheetChanged = $validated['sheet'] !== $cadastralUnit->sheet;
        $parcelChanged = $validated['parcel'] !== $cadastralUnit->parcel;

        $cadastralUnit->update($validated);

        if ($sheetChanged || $parcelChanged) {
            $city = City::with('province.region')->find($validated['city_id']);
            $region = strtoupper($city->province->region->name);
            $province = strtoupper($city->province->code);
            $cityCode = $city->cadastral_code;
            $cityId = $city->id;
            $sheet = $validated['sheet'];
            $parcel = $validated['parcel'];

            ParseCartographyJob::dispatch($region, $province, $cityCode, $cityId, $sheet, $parcel, $cadastralUnit->user_id, $cadastralUnit->id);

            return back()->with('success', __('ui.cadastral_unit_update_queued'));
        }

        return back()->with('success', __('ui.cadastral_unit_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CadastralUnit $cadastralUnit)
    {
        Gate::authorize('delete', $cadastralUnit);

        if ($cadastralUnit->isGrouped()) {
            return back()->withErrors(['general' => __('ui.cannot_delete_grouped_unit')]);
        }

        $cadastralUnit->delete();

        return redirect()->route('cadastral-units.index')
            ->with('success', __('ui.cadastral_unit_deleted'));
    }
}
