<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Company;
use App\Helpers\PlanHelper;
use Illuminate\Http\Request;
use App\Models\CadastralGroup;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use App\Services\CadastralGroupService;
use App\Services\AdjacencyCheckerService;
use App\Repositories\CadastralGroupRepository;
use App\Http\Requests\CadastralGroup\StoreCadastralGroupRequest;
use App\Http\Requests\CadastralGroup\UpdateCadastralGroupRequest;

class CadastralGroupController extends Controller
{
    public function __construct(
        protected CadastralGroupService $groupService,
        protected AdjacencyCheckerService $adjacencyChecker,
        protected CadastralGroupRepository $repository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', CadastralGroup::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,total_area,units_count,created_at,creation_method',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $groups = $this->repository->getPaginated(
            userId: auth()->id(),
            isSuperAdmin: auth()->user()->isSuperAdmin(),
            perPage: $validated['per_page'] ?? 10,
            sortBy: $validated['sort_by'] ?? 'name',
            sortOrder: $validated['sort_order'] ?? 'asc',
            search: $validated['search'] ?? null
        );

        $allGroupsForMap = $this->repository->getAllForMap(auth()->id());

        return Inertia::render('cadastral-groups/Index', [
            'cadastralGroups' => $groups,
            'allGroupsForMap' => $allGroupsForMap,
            'sorting' => [
                'sortBy' => $validated['sort_by'] ?? 'name',
                'sortOrder' => $validated['sort_order'] ?? 'asc',
            ],
            'filtering' => [
                'search' => $validated['search'] ?? null,
                'searchableColumns' => ['name', 'description'],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        Gate::authorize('create', CadastralGroup::class);

        $isAdmin = auth()->user()->isSuperAdmin();
        $userCompanies = $this->repository->getUserCompanies(auth()->id());
        $ownedCompanyIds = Company::where('owner_id', auth()->id())->pluck('id')->toArray();

        return Inertia::render('cadastral-groups/Create', [
            'availableUnits' => $this->repository->getAvailableUnits(auth()->id()),
            'users' => $isAdmin ? $this->repository->getAllUsers() : null,
            'companies' => $isAdmin
                ? $this->repository->getAllCompanies()
                : $userCompanies,
            'cultivars' => $this->repository->getAllCultivars(),
            'plantingSchemes' => $this->repository->getAllPlantingSchemes(),
            'plantDiseases' => $this->repository->getAllPlantDiseases(),
            'irrigations' => $this->repository->getAllIrrigations(),
            'ownedCompanyIds' => $ownedCompanyIds,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCadastralGroupRequest $request): RedirectResponse
    {
        Gate::authorize('create', CadastralGroup::class);

        // check if user can assign the selected company
        $companyId = $request->input('company_id');
        if ($companyId) {
            $isCompanyOwner = Company::where('id', $companyId)
                ->where('owner_id', auth()->id())
                ->exists();

            if (!auth()->user()->isSuperAdmin() && !$isCompanyOwner) {
                return redirect()->back()->withErrors(['company_id' => __('ui.company_not_allowed')])->withInput();
            }
        }

        $user = User::with('cadastralGroup')->whereId(auth()->user()->id)->first();
        if (PlanHelper::isCadastralGroupsLimitReached($user)){
            return redirect()->back()
                ->with('error', __('ui.maximum_cadastral_groups_reached'));
        }

        $result = match ($request->input('creation_method')) {
            'units' => $this->groupService->createFromUnits(
                data: $request->validated(),
                unitIds: $request->input('unit_ids'),
                userId: $request->getUserId(),
                companyId: $request->input('company_id')
            ),
            'manual', 'import' => $this->groupService->createFromGeojson(
                data: $request->validated(),
                geojson: $request->input('geojson'),
                userId: $request->getUserId(),
                companyId: $request->input('company_id')
            ),
        };

        if (!$result['success']) {
            return back()->withErrors([
                'unit_ids' => $result['message'],
                'geojson' => $result['message']
            ]);
        }

        $cultivarIds = $request->getCultivarIds();
        if ($cultivarIds) {
            $this->groupService->syncCultivars($result['group'], $request->input('cultivars'));
        }

        $plantingSchemeIds = $request->getPlantingSchemeIds();
        if ($plantingSchemeIds) {
            $this->groupService->syncPlantingSchemes($result['group'], $request->input('planting_schemes'));
        }

        $plantDiseaseIds = $request->getPlantDiseaseIds();
        if ($plantDiseaseIds) {
            $this->groupService->syncPlantDiseases($result['group'], $request->input('plant_diseases'));
        }

        $irrigationIds = $request->getIrrigationIds();
        if ($irrigationIds) {
            $this->groupService->syncIrrigations($result['group'], $request->input('irrigations'));
        }

        return redirect()->route('cadastral-groups.show', $result['group']->id)
            ->with('success', __('ui.cadastral_group_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(CadastralGroup $cadastralGroup): Response
    {
        Gate::authorize('view', $cadastralGroup);

        $cadastralGroup->load([
            'cadastralUnits.city.province.region',
            'user',
            'company',
            'cultivars.cultivation',
            'plantingSchemes',
            'plantDiseases',
            'irrigations',
        ]);

        return Inertia::render('cadastral-groups/Show', [
            'cadastralGroup' => $this->repository->formatForView($cadastralGroup),
            'isAdmin' => auth()->user()->isSuperAdmin(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CadastralGroup $cadastralGroup): Response
    {
        Gate::authorize('update', $cadastralGroup);

        $cadastralGroup->load([
            'cadastralUnits.city.province.region',
            'cultivars.cultivation',
            'plantingSchemes',
            'plantDiseases',
            'irrigations',
        ]);

        $isAdmin = auth()->user()->isSuperAdmin();
        $userCompanies = $this->repository->getUserCompanies(auth()->id());
        $ownedCompanyIds = Company::where('owner_id', auth()->id())->pluck('id')->toArray();

        return Inertia::render('cadastral-groups/Edit', [
            'cadastralGroup' => $this->repository->formatForEdit($cadastralGroup),
            'availableUnits' => $this->repository->getAvailableUnits(
                $cadastralGroup->user_id,
                $cadastralGroup->id,
                $cadastralGroup->company_id
            ),
            'cultivars' => $this->repository->getAllCultivars(),
            'plantingSchemes' => $this->repository->getAllPlantingSchemes(),
            'plantDiseases' => $this->repository->getAllPlantDiseases(),
            'irrigations' => $this->repository->getAllIrrigations(),
            'users' => $isAdmin ? $this->repository->getAllUsers() : null,
            'companies' => $isAdmin
                ? $this->repository->getAllCompanies()
                : $userCompanies,
            'isAdmin' => $isAdmin,
            'ownedCompanyIds' => $ownedCompanyIds,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateCadastralGroupRequest $request,
        CadastralGroup $cadastralGroup
    ): RedirectResponse {
        Gate::authorize('update', $cadastralGroup);

        $targetUserId = $request->getUserId($cadastralGroup->user_id);
        $ownerChanged = $request->isOwnerChanging($cadastralGroup->user_id);
        $companyId = $request->input('company_id');

        // check if user can assign the selected company
        if ($companyId !== $cadastralGroup->company_id) {
            if ($companyId) {
                $isCompanyOwner = Company::where('id', $companyId)
                    ->where('owner_id', auth()->id())
                    ->exists();

                if (!auth()->user()->isSuperAdmin() && !$isCompanyOwner) {
                    return redirect()->back()->withErrors(['company_id' => __('ui.company_change_not_allowed')])->withInput();
                }
            } else {
                if (!auth()->user()->isSuperAdmin() && $cadastralGroup->company_id) {
                    if ($cadastralGroup->company->owner_id !== null && $cadastralGroup->company->owner_id !== auth()->id()) {
                        return redirect()->back()->withErrors(['company_id' => __('ui.company_change_not_allowed')])->withInput();
                    }
                }
            }
        }

        // Handle creation method changes
        if ($cadastralGroup->creation_method !== $request->input('creation_method')) {
            $this->handleCreationMethodChange($cadastralGroup);
        }

        // Update basic fields
        $cadastralGroup->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'color' => $request->input('color') ?? $cadastralGroup->color,
            'creation_method' => $request->input('creation_method'),
            'user_id' => $targetUserId,
            'company_id' => $companyId,
        ]);

        // Handle geometry updates
        if ($request->input('creation_method') === 'units') {
            if ($ownerChanged) {
                $cadastralGroup->cadastralUnits()->update(['cadastral_group_id' => null]);
            }

            $result = $this->groupService->updateUnits(
                $cadastralGroup,
                $request->input('unit_ids'),
                $targetUserId,
                $companyId
            );

            if (!$result['success']) {
                return back()->withErrors(['unit_ids' => $result['message']]);
            }
        } else {
            $result = $cadastralGroup->updateGeometryFromGeojson($request->input('geojson'));
            if (!$result['success']) {
                return back()->withErrors(['geojson' => $result['message']]);
            }
        }

        $this->groupService->syncCultivars(
            $cadastralGroup,
            $request->input('cultivars')
        );

        $this->groupService->syncPlantingSchemes(
            $cadastralGroup,
            $request->input('planting_schemes')
        );

        $this->groupService->syncPlantDiseases(
            $cadastralGroup,
            $request->input('plant_diseases')
        );

        $this->groupService->syncIrrigations(
            $cadastralGroup,
            $request->input('irrigations')
        );

        return redirect()->route('cadastral-groups.show', $cadastralGroup->id)
            ->with('success', __('ui.cadastral_group_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CadastralGroup $cadastralGroup): RedirectResponse
    {
        Gate::authorize('delete', $cadastralGroup);

        $cadastralGroup->cadastralUnits()->update(['cadastral_group_id' => null]);
        $cadastralGroup->delete();

        return redirect()->route('cadastral-groups.index')
            ->with('success', __('ui.cadastral_group_deleted_successfully'));
    }

    /**
     * Export group as FeatureCollection
     */
    public function exportFeatureCollection(CadastralGroup $cadastralGroup): \Illuminate\Http\Response
    {
        Gate::authorize('view', $cadastralGroup);

        $geojson = $cadastralGroup->exportAsFeatureCollection();
        $filename = sprintf(
            'cadastral_group_%d_%s.featurecollection.geojson',
            $cadastralGroup->id,
            date('Y-m-d')
        );

        return response($geojson)
            ->header('Content-Type', 'application/geo+json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export group as GeoJSON
     */
    public function exportGeojson(CadastralGroup $cadastralGroup): \Illuminate\Http\Response
    {
        Gate::authorize('view', $cadastralGroup);

        $geojson = $cadastralGroup->exportAsMultiPolygon();
        $filename = sprintf(
            'cadastral_group_%d_%s.geojson',
            $cadastralGroup->id,
            date('Y-m-d')
        );

        return response($geojson)
            ->header('Content-Type', 'application/geo+json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    protected function handleCreationMethodChange(CadastralGroup $cadastralGroup): void
    {
        if ($cadastralGroup->creation_method === 'units') {
            $cadastralGroup->cadastralUnits()->update(['cadastral_group_id' => null]);
        } else {
            $cadastralGroup->original_geojson = null;
        }
    }
}
