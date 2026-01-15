<?php

namespace App\Repositories;

use App\Models\CadastralUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CadastralUnitRepository
{
    private const SEARCHABLE_COLUMNS = ['sheet', 'parcel', 'cadastral_area'];

    public function getPaginated(int $userId, bool $isSuperAdmin, array $filters): LengthAwarePaginator
    {
        $query = $this->buildBaseQuery($userId, $isSuperAdmin);

        $this->applySearch($query, $filters['search'] ?? null);
        $this->applySorting($query, $filters['sort_by'] ?? null, $filters['sort_order'] ?? 'asc');

        return $query->paginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function getAllForMap(int $userId, bool $isSuperAdmin): Collection
    {
        if ($isSuperAdmin) {
            return CadastralUnit::query()
                ->with(['city.province', 'city.region'])
                ->select('id', 'sheet', 'parcel', 'cadastral_area', 'city_id', 'geometry', 'centroid')
                ->get();
        }

        return CadastralUnit::query()
            ->with(['city.province', 'city.region',])
            ->where('user_id', $userId)
            ->select('id', 'sheet', 'parcel', 'cadastral_area', 'city_id', 'geometry', 'centroid')
            ->get();
    }

    private function buildBaseQuery(int $userId, bool $isSuperAdmin): Builder
    {
        if ($isSuperAdmin) {
            return CadastralUnit::query()
                ->with(['city.province', 'city.region', 'user:id,email,first_name,last_name'])
                ->select('cadastral_units.*')
                ->join('cities', 'cadastral_units.city_id', '=', 'cities.id')
                ->join('provinces', 'cities.province_id', '=', 'provinces.id')
                ->join('regions', 'cities.region_id', '=', 'regions.id');
        }

        return CadastralUnit::query()
            ->with(['city.province', 'city.region', 'user:id,email,first_name,last_name'])
            ->where('user_id', $userId)
            ->select('cadastral_units.*')
            ->join('cities', 'cadastral_units.city_id', '=', 'cities.id')
            ->join('provinces', 'cities.province_id', '=', 'provinces.id')
            ->join('regions', 'cities.region_id', '=', 'regions.id');
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function ($q) use ($search) {
            // Search in cadastral unit columns
            foreach (self::SEARCHABLE_COLUMNS as $column) {
                $q->orWhere("cadastral_units.$column", 'like', '%' . $search . '%');
            }

            // Search in related tables
            $q->orWhere('cities.name', 'ilike', '%' . $search . '%')
                ->orWhere('provinces.name', 'ilike', '%' . $search . '%')
                ->orWhere('regions.name', 'ilike', '%' . $search . '%');
        });
    }

    private function applySorting(Builder $query, ?string $sortBy, string $sortOrder): void
    {
        if (!$sortBy) {
            $this->applyDefaultSorting($query);
            return;
        }

        match ($sortBy) {
            'city' => $this->applyCitySorting($query, $sortOrder),
            'province' => $this->applyProvinceSorting($query, $sortOrder),
            'region' => $this->applyRegionSorting($query, $sortOrder),
            'sheet' => $this->applySheetSorting($query, $sortOrder),
            default => $query->orderBy("cadastral_units.$sortBy", $sortOrder),
        };

        // Always add created_at as final sort
        $query->orderBy('cadastral_units.created_at', 'desc');
    }

    private function applyDefaultSorting(Builder $query): void
    {
        $query->orderBy('regions.name', 'asc')
            ->orderBy('provinces.name', 'asc')
            ->orderBy('cities.name', 'asc')
            ->orderBy('cadastral_units.sheet', 'asc')
            ->orderBy('cadastral_units.parcel', 'asc');
    }

    private function applyCitySorting(Builder $query, string $sortOrder): void
    {
        $query->orderBy('cities.name', $sortOrder)
            ->orderBy('cadastral_units.sheet', 'asc')
            ->orderBy('cadastral_units.parcel', 'asc');
    }

    private function applyProvinceSorting(Builder $query, string $sortOrder): void
    {
        $query->orderBy('provinces.name', $sortOrder)
            ->orderBy('cities.name', 'asc')
            ->orderBy('cadastral_units.sheet', 'asc')
            ->orderBy('cadastral_units.parcel', 'asc');
    }

    private function applyRegionSorting(Builder $query, string $sortOrder): void
    {
        $query->orderBy('regions.name', $sortOrder)
            ->orderBy('provinces.name', 'asc')
            ->orderBy('cities.name', 'asc')
            ->orderBy('cadastral_units.sheet', 'asc')
            ->orderBy('cadastral_units.parcel', 'asc');
    }

    private function applySheetSorting(Builder $query, string $sortOrder): void
    {
        $query->orderBy('cadastral_units.sheet', $sortOrder)
            ->orderBy('cadastral_units.parcel', 'asc');
    }
}
