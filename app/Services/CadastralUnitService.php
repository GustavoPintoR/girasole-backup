<?php

namespace App\Services;

use App\Repositories\CadastralUnitRepository;
use App\Transformers\CadastralUnitMapTransformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CadastralUnitService
{
    public const SEARCHABLE_COLUMNS = ['sheet', 'parcel', 'cadastral_area'];

    public function __construct(
        private CadastralUnitRepository $repository,
        private CadastralUnitMapTransformer $mapTransformer
    ) {}

    public function getPaginatedUnits(int $userId, bool $isSuperAdmin, array $filters): LengthAwarePaginator
    {
        return $this->repository->getPaginated($userId, $isSuperAdmin, $filters);
    }

    public function getUnitsForMap(int $userId, $isSuperAdmin): Collection
    {
        $units = $this->repository->getAllForMap($userId, $isSuperAdmin);
        return $this->mapTransformer->transformCollection($units);
    }
}
