<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Company;
use App\Models\Cultivar;
use App\Models\CadastralUnit;
use App\Models\CadastralGroup;
use App\Models\PlantingScheme;
use App\Models\PlantDisease;
use App\Models\Irrigation;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CadastralGroupRepository
{
    /**
     * Get paginated cadastral groups with optional filters
     */
    public function getPaginated(
        int $userId,
        bool $isSuperAdmin,
        int $perPage = 10,
        ?string $sortBy = 'name',
        string $sortOrder = 'asc',
        ?string $search = null
    ): LengthAwarePaginator {
        $query = CadastralGroup::query()->with(['user:id,email,first_name,last_name', 'company'])->withCount('cadastralUnits')->visibleTo(User::find($userId));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%');
            });
        }

        return $query->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all groups for map display
     */
    public function getAllForMap(int $userId): Collection
    {
        $query = CadastralGroup::query()->visibleTo(User::find($userId))
            ->select('id', 'name', 'total_area', 'units_count', 'creation_method', 'boundary_geometry', 'centroid', 'color', 'user_id', 'company_id');

        return $query->get()->map(fn($group) => [
            'id' => $group->id,
            'name' => $group->name,
            'total_area' => $group->total_area,
            'units_count' => $group->units_count,
            'creation_method' => $group->creation_method,
            'color' => $group->color ?? '#3B82F6',
            'boundary_geometry_json' => $group->boundary_geometry_json,
            'centroid_json' => $group->centroid_json,
        ]);
    }

    /**
     * Get available cadastral units for a user
     */
    public function getAvailableUnits(int $userId, ?int $groupId = null, ?int $companyId = null): Collection
    {
        $query = CadastralUnit::query()
            ->whereNotNull('geometry')
            ->with(['city.province.region']);

        // If company is specified, get units from company users
        if ($companyId) {
            $company = Company::with('users')->find($companyId);
            $companyUserIds = $company->users->pluck('id')->toArray();
            $query->whereIn('user_id', $companyUserIds);
        } else {
            $query->where('user_id', $userId);
        }

        if ($groupId) {
            $query->where(function ($q) use ($groupId) {
                $q->whereNull('cadastral_group_id')
                    ->orWhere('cadastral_group_id', $groupId);
            });
        } else {
            $query->whereNull('cadastral_group_id');
        }

        return $query->get()->map(function ($unit) use ($groupId) {
            return [
                'id' => $unit->id,
                'sheet' => $unit->sheet,
                'parcel' => $unit->parcel,
                'cadastral_area' => $unit->cadastral_area,
                'selected' => $groupId && $unit->cadastral_group_id === $groupId,
                'city' => [
                    'name' => $unit->city->name,
                    'province' => $unit->city->province->name,
                    'province_code' => $unit->city->province->code ?? null,
                    'region' => $unit->city->region->name,
                ],
                'geometry_json' => $unit->geometry_json,
                'centroid_json' => $unit->centroid_json,
            ];
        });
    }

    /**
     * Get all cultivars with cultivation info
     */
    public function getAllCultivars(): Collection
    {
        return Cultivar::with('cultivation')
            ->orderBy('name')
            ->get()
            ->map(fn($cultivar) => [
                'id' => $cultivar->id,
                'name' => $cultivar->name,
                'description' => $cultivar->description,
                'cultivation_name' => $cultivar->cultivation->name ?? null,
            ]);
    }

    /**
     * Get all users (for admin)
     */
    public function getAllUsers(): Collection
    {
        return User::select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
            ]);
    }

    /**
     * Get all companies
     */
    public function getAllCompanies(): Collection
    {
        return Company::select('id', 'name', 'description')
            ->orderBy('name')
            ->get()
            ->map(fn($company) => [
                'id' => $company->id,
                'name' => $company->name,
                'description' => $company->description,
            ]);
    }

    /**
     * Get companies for a specific user
     */
    public function getUserCompanies(int $userId): Collection
    {
        $user = User::with('companies')->find($userId);

        return $user->companies->map(fn($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'description' => $company->description,
        ]);
    }

    /**
     * Get all planting schemes
     */
    public function getAllPlantingSchemes(): Collection
    {
        return PlantingScheme::orderBy('name')
            ->get()
            ->map(fn($scheme) => [
                'id' => $scheme->id,
                'name' => $scheme->name,
                'description' => $scheme->description,
                'distance' => $scheme->distance,
            ]);
    }

    /**
     * Get all plant diseases
     */
    public function getAllPlantDiseases(): Collection
    {
        return PlantDisease::orderBy('name')
            ->get()
            ->map(fn($disease) => [
                'id' => $disease->id,
                'name' => $disease->name,
                'description' => $disease->description,
            ]);
    }

    /**
     * Get all irrigations
     */
    public function getAllIrrigations(): Collection
    {
        return Irrigation::orderBy('type')
            ->get()
            ->map(fn($irrigation) => [
                'id' => $irrigation->id,
                'type' => $irrigation->type,
                'description' => $irrigation->description,
            ]);
    }

    /**
     * Format cadastral group for display view
     */
    public function formatForView(CadastralGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'total_area' => $group->total_area,
            'units_count' => $group->units_count,
            'color' => $group->color,
            'creation_method' => $group->creation_method,
            'boundary_geometry_json' => $group->boundary_geometry_json,
            'centroid_json' => $group->centroid_json,
            'latitude' => $group->centroid?->getY(),
            'longitude' => $group->centroid?->getX(),
            'created_at' => $group->created_at,
            'updated_at' => $group->updated_at,
            'user' => $group->user,
            'company' => $group->company ?? null,
            'cadastral_units' => $this->formatCadastralUnits($group->cadastralUnits),
            'cultivars' => $this->formatCultivars($group->cultivars),
            'planting_schemes' => $this->formatPlantingSchemes($group->plantingSchemes),
            'plant_diseases' => $this->formatPlantDiseases($group->plantDiseases),
            'irrigations' => $this->formatIrrigations($group->irrigations),
        ];
    }

    /**
     * Format cadastral group for edit view
     */
    public function formatForEdit(CadastralGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'total_area' => $group->total_area,
            'units_count' => $group->units_count,
            'color' => $group->color,
            'creation_method' => $group->creation_method,
            'original_geojson' => $group->original_geojson,
            'boundary_geometry_json' => $group->boundary_geometry_json,
            'centroid_json' => $group->centroid_json,
            'created_at' => $group->created_at,
            'updated_at' => $group->updated_at,
            'user_id' => $group->user_id,
            'company_id' => $group->company_id,
            'unit_ids' => $group->cadastralUnits->pluck('id')->toArray(),
            'cultivars' => $this->formatCultivars($group->cultivars),
            'planting_schemes' => $this->formatPlantingSchemes($group->plantingSchemes),
            'plant_diseases' => $this->formatPlantDiseases($group->plantDiseases),
            'irrigations' => $this->formatIrrigations($group->irrigations),
        ];
    }

    /**
     * Format cadastral units collection
     */
    protected function formatCadastralUnits(Collection $units): Collection
    {
        return $units->map(fn($unit) => [
            'id' => $unit->id,
            'sheet' => $unit->sheet,
            'parcel' => $unit->parcel,
            'cadastral_area' => $unit->cadastral_area,
            'city' => [
                'name' => $unit->city->name,
                'province' => $unit->city->province->name,
                'region' => $unit->city->region->name,
            ],
            'geometry_json' => $unit->geometry_json,
        ]);
    }

    /**
     * Format cultivars collection
     */
    protected function formatCultivars(Collection $cultivars): Collection
    {
        return $cultivars->map(fn($cultivar) => [
            'id' => $cultivar->id,
            'name' => $cultivar->name,
            'description' => $cultivar->description,
            'cultivation_name' => $cultivar->cultivation->name ?? null,
        ]);
    }

    /**
     * Format planting schemes collection
     */
    protected function formatPlantingSchemes(Collection $schemes): Collection
    {
        return $schemes->map(fn($scheme) => [
            'id' => $scheme->id,
            'name' => $scheme->name,
            'description' => $scheme->description,
            'distance' => $scheme->distance,
        ]);
    }

    /**
     * Format plant diseases collection
     */
    protected function formatPlantDiseases(Collection $diseases): Collection
    {
        return $diseases->map(fn($disease) => [
            'id' => $disease->id,
            'name' => $disease->name,
            'description' => $disease->description,
        ]);
    }

    /**
     * Format irrigations collection
     */
    protected function formatIrrigations(Collection $irrigations): Collection
    {
        return $irrigations->map(fn($irrigation) => [
            'id' => $irrigation->id,
            'type' => $irrigation->type,
            'description' => $irrigation->description,
        ]);
    }
}
