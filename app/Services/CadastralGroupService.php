<?php

namespace App\Services;

use App\Models\CadastralGroup;
use App\Models\CadastralUnit;

class CadastralGroupService
{
    public function __construct(
        protected AdjacencyCheckerService $adjacencyChecker,
    ) {}

    /**
     * Add cadastral units to a group with validation
     */
    public function addUnitsToGroup(CadastralGroup $group, array $unitIds): array
    {
        if ($group->creation_method !== 'units') {
            return [
                'success' => false,
                'message' => __('ui.field_not_created_from_units')
            ];
        }

        $query = CadastralUnit::whereIn('id', $unitIds)
            ->whereNull('cadastral_group_id');

        // If group has a company, get units from company users
        if ($group->company_id) {
            $company = $group->company()->with('users')->first();
            $companyUserIds = $company->users->pluck('id')->toArray();
            $query->whereIn('user_id', $companyUserIds);
        } else {
            $query->where('user_id', $group->user_id);
        }

        $newUnits = $query->get();

        if ($newUnits->isEmpty()) {
            return [
                'success' => false,
                'message' => __('ui.units_not_found')
            ];
        }

        $existingUnits = $group->cadastralUnits;
        $allUnits = $existingUnits->merge($newUnits);

        if (!$this->adjacencyChecker->areUnitsAdjacent($allUnits)) {
            return [
                'success' => false,
                'message' => __('ui.units_must_be_adjacent')
            ];
        }

        CadastralUnit::whereIn('id', $unitIds)->update(['cadastral_group_id' => $group->id]);

        /** @var array */
        $status = $group->updateGeometryFromUnits();

        if ($status['success'] === false) {
            return $status;
        }

        return [
            'success' => true,
            'message' => __('ui.units_added_successfully')
        ];
    }

    /**
     * Create a group from units
     */
    public function createFromUnits(array $data, array $unitIds, int $userId, ?int $companyId = null): array
    {
        $query = CadastralUnit::whereIn('id', $unitIds)
            ->whereNull('cadastral_group_id');

        // If company is specified, get units from company users
        if ($companyId) {
            $company = \App\Models\Company::with('users')->find($companyId);
            $companyUserIds = $company->users->pluck('id')->toArray();
            $query->whereIn('user_id', $companyUserIds);
        } else {
            $query->where('user_id', $userId);
        }

        $units = $query->get();

        if ($units->count() !== count($unitIds)) {
            return [
                'success' => false,
                'message' => __('ui.selected_units_not_available'),
                'group' => null
            ];
        }

        if (!$this->adjacencyChecker->areUnitsAdjacent($units)) {
            return [
                'success' => false,
                'message' => __('ui.matching_units_not_adjacent'),
                'group' => null
            ];
        }

        $group = CadastralGroup::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#3B82F6',
            'creation_method' => 'units',
        ]);

        $result = $this->addUnitsToGroup($group, $unitIds);

        if (!$result['success']) {
            $group->delete();
            return [
                'success' => false,
                'message' => $result['message'],
                'group' => null
            ];
        }

        return [
            'success' => true,
            'message' => __('ui.cadastral_group_created_successfully'),
            'group' => $group
        ];
    }

    /**
     * Create a group from GeoJSON
     */
    public function createFromGeojson(array $data, string $geojson, int $userId, ?int $companyId = null): array
    {
        $group = CadastralGroup::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#3B82F6',
            'creation_method' => $data['creation_method'],
        ]);

        $result = $group->updateGeometryFromGeojson($geojson);

        if (!$result['success']) {
            $group->delete();
            return [
                'success' => false,
                'message' => $result['message'],
                'group' => null
            ];
        }

        return [
            'success' => true,
            'message' => __('ui.cadastral_group_created_successfully'),
            'group' => $group
        ];
    }

    /**
     * Update units in a group
     */
    public function updateUnits(CadastralGroup $group, array $unitIds, int $userId, ?int $companyId = null): array
    {
        $oldGroupData = $group->toArray();
        $query = CadastralUnit::whereIn('id', $unitIds)
            ->where(function ($query) use ($group) {
                $query->whereNull('cadastral_group_id')
                    ->orWhere('cadastral_group_id', $group->id);
            });

        // If company is specified, get units from company users
        if ($companyId) {
            $company = \App\Models\Company::with('users')->find($companyId);
            $companyUserIds = $company->users->pluck('id')->toArray();
            $query->whereIn('user_id', $companyUserIds);
        } else {
            $query->where('user_id', $userId);
        }

        $units = $query->get();

        if ($units->count() !== count($unitIds)) {
            return [
                'success' => false,
                'message' => __('ui.selected_units_not_available')
            ];
        }

        if (!$this->adjacencyChecker->areUnitsAdjacent($units)) {
            return [
                'success' => false,
                'message' => __('ui.matching_units_not_adjacent')
            ];
        }

        $prevUnitIds = CadastralUnit::where('cadastral_group_id', $group->id)->pluck('id')->toArray();
        $newUnitIds = CadastralUnit::whereIn('id', $unitIds)->pluck('id')->toArray();

        CadastralUnit::where('cadastral_group_id', $group->id)
            ->whereNotIn('id', $unitIds)
            ->update(['cadastral_group_id' => null]);

        CadastralUnit::whereIn('id', $unitIds)
            ->update(['cadastral_group_id' => $group->id]);

        try {
            /** @var array */
            $status = $group->updateGeometryFromUnits();
        } catch (\Throwable $th) {
            $group->update($oldGroupData);

            CadastralUnit::whereIn('id', $prevUnitIds)
                ->update(['cadastral_group_id' => $group->id]);
            CadastralUnit::whereIn('id', $newUnitIds)
                ->whereNotIn('id', $prevUnitIds)
                ->where('cadastral_group_id', $group->id)
                ->update(['cadastral_group_id' => null]);

            return [
                'success' => false,
                'message' => __('ui.error_updating_group_geometry')
            ];
        }

        if ($status['success'] === false) {
            $group->update($oldGroupData);

            CadastralUnit::whereIn('id', $prevUnitIds)
                ->update(['cadastral_group_id' => $group->id]);
            CadastralUnit::whereIn('id', $newUnitIds)
                ->whereNotIn('id', $prevUnitIds)
                ->where('cadastral_group_id', $group->id)
                ->update(['cadastral_group_id' => null]);

            return $status;
        }

        return ['success' => true];
    }

    /**
     * Sync cultivars to a group
     */
    public function syncCultivars(CadastralGroup $group, ?array $cultivars): void
    {
        if ($cultivars) {
            $cultivarIds = array_column($cultivars, 'id');
            $group->cultivars()->sync($cultivarIds);
        } else {
            $group->cultivars()->detach();
        }
    }

    /**
     * Sync planting schemes to a group
     */
    public function syncPlantingSchemes(CadastralGroup $group, ?array $plantingSchemes): void
    {
        if ($plantingSchemes) {
            $schemeIds = array_column($plantingSchemes, 'id');
            $group->plantingSchemes()->sync($schemeIds);
        } else {
            $group->plantingSchemes()->detach();
        }
    }

    /**
     * Sync plant diseases for a group
     */
    public function syncPlantDiseases(CadastralGroup $group, ?array $plantDiseases): void
    {
        if (is_null($plantDiseases)) {
            return;
        }

        $ids = array_column($plantDiseases, 'id');
        $group->plantDiseases()->sync($ids);
    }

    /**
     * Sync irrigations for a group
     */
    public function syncIrrigations(CadastralGroup $group, ?array $irrigations): void
    {
        if (is_null($irrigations)) {
            return;
        }

        $ids = array_column($irrigations, 'id');
        $group->irrigations()->sync($ids);
    }
}
