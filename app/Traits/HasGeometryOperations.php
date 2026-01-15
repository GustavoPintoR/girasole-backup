<?php

namespace App\Traits;

use App\Models\User;
use App\Helpers\PlanHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;

trait HasGeometryOperations
{
    /**
     * Calculate and update the merged geometry and total area for unit-based groups
     */
    public function updateGeometryFromUnits(): array
    {
        if ($this->creation_method !== 'units') {
            return [
                'success' => false,
                'message' => __('ui.field_not_created_from_units')
            ];
        }

        $units = $this->cadastralUnits()->whereNotNull('geometry')->get();

        if ($units->isEmpty()) {
            $this->boundary_geometry = null;
            $this->centroid = null;
            $this->total_area = 0;
            $this->save();
            return [
                'success' => false,
                'message' => __('ui.no_units_with_geometry_found')
            ];
        }

        if ($units->count() === 1) {
            $unit = $units->first();
            $this->boundary_geometry = ST::union([$unit->geometry]);
            $this->centroid = $unit->centroid;

            if ($unit->cadastral_area) {
                $this->total_area = $unit->cadastral_area;
                $calculatedArea = $unit->cadastral_area;
            } else {
                $result = DB::selectOne(
                    "SELECT ST_Area(ST_Transform(ST_GeomFromText(?, 4326), 3035)) as area",
                    [$unit->geometry]
                );
                $calculatedArea = $result->area;
                $this->total_area = ST::area(ST::transform($unit->geometry, 3035));
            }
        } else {
            $geometries = $units->map(fn($unit) => $unit->geometry)->toArray();
            $mergedGeometry = ST::union($geometries);

            $result = DB::selectOne(
                "SELECT ST_Area(ST_Transform(ST_Union(ARRAY[" .
                    implode(',', array_fill(0, count($geometries), 'ST_GeomFromText(?, 4326)')) .
                    "]), 3035)) as area",
                $geometries
            );
            $calculatedArea = $result->area;

            $this->boundary_geometry = $mergedGeometry;
            $this->centroid = ST::centroid($mergedGeometry);
            $this->total_area = ST::area(ST::transform($mergedGeometry, 3035));
        }

        $user = User::with('cadastralGroup')->whereId($this->user_id)->first();

        if ($response = $this->checkAndHandleCadastralAreaLimit($user, $calculatedArea, $this->id)) {
            return $response;
        }

        $this->units_count = $this->cadastralUnits()->count();
        $this->save();

        return [
            'success' => true,
        ];
    }

    /**
     * Update geometry from manual drawing or import
     */
    public function updateGeometryFromGeojson(string $geojson): array
    {
        try {
            $parser = app(GeojsonParser::class);
            $geometry = $parser->parse($geojson);
        } catch (\Throwable $th) {
            Log::info('Error parsing json: ' . $th->getMessage());
            return [
                'success' => false,
                'message' => __('ui.error_parsing_geojson')
            ];
        }


        try {
            $result = DB::selectOne(
                "SELECT ST_Area(ST_Transform(ST_GeomFromText(?, 4326), 3035)) as area",
                [$geometry]
            );
            $calculatedArea = $result->area;

            $this->boundary_geometry = ST::union([$geometry]);
            $this->centroid = ST::centroid($geometry);
            $this->total_area = ST::area(ST::transform($geometry, 3035));
            $this->original_geojson = $geojson;
            $this->units_count = 0;

            $user = User::with('cadastralGroup')->whereId($this->user_id)->first();

            if ($response = $this->checkAndHandleCadastralAreaLimit($user, $calculatedArea, $this->id)) {
                return $response;
            }

            $this->save();

            return [
                'success' => true,
                'message' => __('ui.geometry_updated_successfully')
            ];
        } catch (\Exception $e) {
            Log::info('Error parsing json: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => __('ui.error_parsing_geojson')
            ];
        }
    }

    /**
     * Check if a point is within this geometry
     */
    public function pointIsWithinGroup(float $latitude, float $longitude): bool
    {
        if (!$this->boundary_geometry) {
            return false;
        }

        $result = DB::selectOne(
            "SELECT ST_CoveredBy(
                ST_SetSRID(ST_MakePoint(?, ?), 4326),
                boundary_geometry
            ) as is_within
            FROM cadastral_groups
            WHERE id = ?",
            [$longitude, $latitude, $this->id]
        );

        return $result ? (bool) $result->is_within : false;
    }

    /**
     * Get the centroid coordinates of the group
     */
    public function getCentroidCoordinates(): array
    {
        return [
            'lng' => $this->centroid->getX(),
            'lat' => $this->centroid->getY()
        ];
    }

    /**
     * Check cadastral area plan limits and return a failure response array when exceeded.
     */
    protected function checkAndHandleCadastralAreaLimit(?User $user, float $calculatedArea, ?int $excludeGroupId = null): ?array
    {
        $planCheck = PlanHelper::isCadastralAreaLimitExceeded($user, $calculatedArea, $excludeGroupId);

        if ($planCheck['exceeded'] === false) {
            return null;
        }

        switch ($planCheck['reason'] ?? '') {
            case 'no_active_subscription':
            case 'no_active_plan':
                $message = __('ui.cadastral_area_limit_reached_no_subscription');
                break;
            case 'area_limit_exceeded':
                $message = __('ui.cadastral_area_limit_exceeded', [
                    'max' => isset($planCheck['max_area']) ? round($planCheck['max_area'] / 10000, 2) : 'N/A',
                    'current' => isset($planCheck['current_area']) ? round($planCheck['current_area'] / 10000, 2) : 'N/A',
                    'incoming' => isset($planCheck['incoming_area']) ? round($planCheck['incoming_area'] / 10000, 2) : 'N/A',
                ]);
                break;
            default:
                $message = __('ui.cadastral_area_limit_reached');
        }

        return [
            'success' => false,
            'message' => $message,
        ];
    }
}
