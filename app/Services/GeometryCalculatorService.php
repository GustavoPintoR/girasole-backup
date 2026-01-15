<?php

namespace App\Services;

use App\Models\CadastralUnit;
use Illuminate\Support\Facades\DB;

class GeometryCalculatorService
{
    /**
     * Calculate the total area for given unit IDs
     */
    public function calculateAreaForUnits(array $unitIds): array
    {
        $units = CadastralUnit::whereIn('id', $unitIds)
            ->whereNotNull('geometry')
            ->get();

        if ($units->isEmpty()) {
            return [
                'total_area' => 0,
                'unit_count' => 0,
                'message' => 'No units with geometry found'
            ];
        }

        try {
            $totalArea = $units->count() === 1
                ? $this->getSingleUnitArea($units->first())
                : $this->calculateUnionArea($units->pluck('id')->toArray());

            return [
                'total_area' => $totalArea,
                'unit_count' => $units->count(),
                'message' => 'Area calculated successfully'
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Error calculating area: ' . $e->getMessage());
        }
    }

    /**
     * Get the area of a single unit
     */
    protected function getSingleUnitArea(CadastralUnit $unit): float
    {
        return $unit->cadastral_area ?? 0;
    }

    /**
     * Calculate the area of the union of multiple units
     */
    protected function calculateUnionArea(array $unitIds): float
    {
        $placeholders = implode(',', array_fill(0, count($unitIds), '?'));

        $result = DB::selectOne(
            "SELECT ST_Area(
                ST_Transform(
                    ST_Union(geometry),
                    3035
                )
             ) as total_area
             FROM cadastral_units
             WHERE id IN ($placeholders)",
            $unitIds
        );

        return $result ? round($result->total_area, 2) : 0;
    }
}
